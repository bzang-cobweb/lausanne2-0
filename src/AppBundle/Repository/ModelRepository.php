<?php

namespace AppBundle\Repository;

use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Tools\Pagination\Paginator;


/**
 * Class ModelRepository
 * @package AppBundle\Repository
 */
abstract class ModelRepository extends \Doctrine\ORM\EntityRepository
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
        $q = $this->createQueryBuilder('m');
        $where = '';

        // criteria
        foreach($criteria as $property => $value){
            if($where) {
                $where .= " AND ";
            }

            if($property == 'search'){
                $where .= $this->getSearchWhere($value['fields']);
                $q->setParameter('search', '%' . $value['text'] . '%');
            } else {
                $where .= '(m.' . $property . ' = :' . $property . ')';
                $q->setParameter($property, (is_bool($value) ? ($value ? 1 : 0) : $value));
            }
        }

        if($where){
            $q->where($where);
        }

        // order by
        foreach($orderBy as $field => $direction){
            $q->addOrderBy( 'm.' . $field, $direction);
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
     * @param array $criteria
     * @return mixed
     */
    public function count(array $criteria = []){
        $q = $this->createQueryBuilder('m');
        $q->select('count(m.id)');
        foreach($criteria as $field => $value){
            $q->andWhere('m.' . $field . ' = :' . $field);
            $q->setParameter($field, $value);
        }

        return $q->getQuery()->getSingleScalarResult();
    }

    /**
     * @param $id
     * @return mixed
     */
    public function delete($id){
        return $this->createQueryBuilder('m')
            ->update()
            ->set('m.deleted', 1)
            ->set('m.updatedAt', ':updatedAt')
            ->setParameter('updatedAt', new \DateTime(), \Doctrine\DBAL\Types\Type::DATETIME)
            ->where('m.id IN (' . implode(',', (is_array($id) ? $id : [$id])) . ')')
            ->getQuery()
            ->getSingleScalarResult();
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
            $where .= 'm.' . $field . ' LIKE :search';
        }
        $where .= ')';

        return $where;
    }

    /**
     * @param string $season
     * @return array
     */
    protected function period($season){
        $years = explode('-', $season);
        return [
            'createdAt' => $years[0] . '-06-01 00:00:00',
            'updatedAt' => $years[1] . '-05-31 00:00:00',
            'now' => date('Y-m-d 00:00:00')
        ];
    }
}
