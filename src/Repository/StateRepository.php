<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;

class StateRepository extends EntityRepository
{
    public function findByStateAndCountryAbbreviation($state, $country)
    {
        $qb = $this->createQueryBuilder('s')
            ->where('s.abbreviation = :state')
            ->join('s.country', 'c')
            ->andWhere('c.abbreviation = :country')
            ->setParameters(array('country' => $country, 'state' => $state));
        try {
            return $qb->getQuery()->getSingleResult();
        } catch (NoResultException $e) {
        }
    }
}
