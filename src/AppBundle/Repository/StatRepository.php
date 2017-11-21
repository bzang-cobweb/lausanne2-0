<?php

namespace AppBundle\Repository;

use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Tools\Pagination\Paginator;


/**
 * Class StatRepository
 * @package AppBundle\Repository
 */
abstract class StatRepository extends ModelRepository
{
    public function total()  {
        try {
            return $this->getEntityManager()->createQuery(
                '(SELECT COUNT(c) FROM AppBundle\Entity\Championship as c WHERE c.deleted = 0) AS championship,
                (SELECT COUNT(t) FROM AppBundle\Entity\Team as t WHERE t.deleted = 0) AS team
            ')->getResult();
        } catch (NoResultException $e) {}

        return [];
    }
}
