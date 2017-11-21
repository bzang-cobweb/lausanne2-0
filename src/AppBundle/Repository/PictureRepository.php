<?php

namespace AppBundle\Repository;

use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Tools\Pagination\Paginator;


/**
 * PictureRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class PictureRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * @param array $criteria
     * @return mixed
     */
    public function uncover(array $criteria){
        $q = $this->createQueryBuilder('p');
        $where = '';
        foreach ($criteria as $property => $value){
            if($where != ''){
                $where .= ' AND ';
            }
            if($property == 'id'){
                $where .= '(p.' . $property . ' <> :' . $property . ')';
            } else {
                $where .= '(p.' . $property . ' = :' . $property . ')';
            }
            $q->setParameter($property, $value);
        }

        if($where == ''){
            return 0;
        }

        return $q
            ->update()
            ->set('p.cover', 0)
            ->where($where)
            ->getQuery()
            ->getSingleScalarResult();
    }


    /**
     * @param int $teamId
     * @param string $season
     * @return array
     * @throws NoResultException
     */
    public function findByTeamSeason($teamId, $season)  {
        $period = $this->period($season);

        $q = $this->createQueryBuilder('p')
            ->innerJoin('AppBundle\Entity\Team', 't', 'WITH',
                '(t = p.team) AND (t.id = :id) AND (p.createdAt >= :startedAt AND p.createdAt <= :endedAt)')
            ->setParameter('id', $teamId)
            ->setParameter('startedAt', $period['startedAt'])
            ->setParameter('endedAt', $period['endedAt'])
            ->groupBy('p.id')
            ->addOrderBy('p.createdAt', 'DESC')
            ->addOrderBy('p.id', 'DESC');

        try {
            $pictures = $q->getQuery()->getResult();
        } catch (NoResultException $e) {
            throw new NoResultException(sprintf('Unable to find pictures by team and season'), null, 0, $e);
        }

        return $pictures;
    }

    /**
     * @param int $playerId
     * @param string $season
     * @return array
     * @throws NoResultException
     */
    public function findByPlayerSeason($playerId, $season)  {
        $period = $this->period($season);

        $q = $this->createQueryBuilder('p')
            ->innerJoin('AppBundle\Entity\Player', 't', 'WITH',
                '(t = p.player) AND (t.id = :id) AND (p.createdAt >= :startedAt AND p.createdAt <= :endedAt)')
            ->setParameter('id', $playerId)
            ->setParameter('startedAt', $period['startedAt'])
            ->setParameter('endedAt', $period['endedAt'])
            ->groupBy('p.id')
            ->addOrderBy('p.createdAt', 'DESC')
            ->addOrderBy('p.id', 'DESC');

        try {
            $pictures = $q->getQuery()->getResult();
        } catch (NoResultException $e) {
            throw new NoResultException(sprintf('Unable to find pictures by player and season'), null, 0, $e);
        }

        return $pictures;
    }

    /**
     * @param string $season
     * @return array
     */
    protected function period($season){
        $years = explode('-', $season);
        return [
            'startedAt' => $years[0] . '-06-01 00:00:00',
            'endedAt' => $years[1] . '-05-31 00:00:00',
            'now' => date('Y-m-d 00:00:00')
        ];
    }
}
