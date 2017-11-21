<?php

namespace AppBundle\Repository;

use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Tools\Pagination\Paginator;


/**
 * TeamPlayerRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class TeamPlayerRepository extends ModelRepository
{

    /**
     * @param array $criteria
     * @param array $orderBy
     * @param int $limit
     * @param int $offset
     * @return array
     * @throws \Exception
     */
    public function findByLike(array $criteria = [], array $orderBy = [], $limit = 0, $offset = -1)
    {
        $q = $this->createQueryBuilder('m')
            ->innerJoin('m.player', 'p')
            ->innerJoin('m.team', 't');
        $where = '';

        // criteria
        foreach($criteria as $property => $value){
            if($where) {
                $where .= " AND ";
            }

            if($property == 'search') {
                $where .= $this->getSearchWhere($value['fields']);
                $q->setParameter('search', '%' . $value['text'] . '%');
            } else{
                if($property == 'player') {
                    $where .= '(p.id = :' . $property . ')';
                } elseif($property == 'team'){
                    $where .= '(t.id = :' . $property . ')';
                } else {
                    $where .= '(m.' . $property . ' = :' . $property . ')';
                }
                $q->setParameter($property, (is_bool($value) ? ($value ? 1 : 0) : $value));
            }
        }

        if($where){
            $q->where($where);
        }

        // order by
        foreach($orderBy as $field => $direction){
            if($field == 'createdAt'){
                $q->addOrderBy('m.' . $field, $direction);
            } else {
                $q->addOrderBy($field, $direction);
            }
        }
        $q->addOrderBy( 'm.id', $direction);

        // limit
        if($limit > 0){
            $q->setMaxResults($limit);
        }

        // offset
        if($offset >= 0){
            $q->setFirstResult($offset);
        }

        try {
            //die($q->getQuery()->getDQL());
            $models = $q->getQuery()->getResult();
            if($offset >= 0){
                $p = new Paginator($q);
                return [$models, $p->count()];
            }
        } catch (NoResultException $e) {
            throw new \Exception(sprintf('Unable to filter table by like'), null, 0, $e);
        }

        return $models;
    }

    /**
     * @param $fields
     * @return string
     */
    protected function getSearchWhere($fields){
        $where = '(';
        $count = 0;
        foreach ($fields as $field){
            if($count++ > 0){
                $where .= ' OR ';
            }
            $where .= $field . ' LIKE :search';
        }
        $where .= ')';

        return $where;
    }
}
