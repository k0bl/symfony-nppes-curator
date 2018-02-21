<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;

class AddressRepository extends EntityRepository
{
    protected function processCriteria(&$qb, array $criteria = array(), $selectAlias = 'e')
    {
        if (isset($criteria['city'])) {
            $qb->andWhere($qb->expr()->like("$selectAlias.city", ':city'))
                ->setParameter('city', $criteria['city'] . '%');
            unset($criteria['city']);
        }

        if (isset($criteria['state'])) {
            $qb->andWhere("$selectAlias.state = :state")
                ->setParameter('state', $criteria['state']);
            unset($criteria['state']);
        }
        parent::processCriteria($qb, $criteria, $selectAlias);
    }

    public function citySearch($criteria = array())
    {
        $qb = $this->createQueryBuilder('a')
            ->distinct()
            ->select(array('a.city', 'a.state'))
            ->orderBy('a.city', 'ASC')
            ->addOrderby('a.state', 'ASC')
            ->setMaxResults(10);
        $this->processCriteria($qb, $criteria, 'a');
        return $qb->getQuery()->getResult();
    }
}
