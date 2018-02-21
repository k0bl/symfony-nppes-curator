<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;

class CityRepository extends EntityRepository
{
    public function findByNameStateAndCountry($city, $state, $country)
    {
        // This query works because of how mysql sorts values with NULL
        // at the top (or bottom with DESC) of the results. Using a left join
        // for state resulted in only the correct record having a value in
        // the state abbreviation field. Sorting allowed the correct answer
        // appear at top yet allowing records that don't have a state to still
        // be found.
        $qb = $this->createQueryBuilder('c')
            ->join('c.country', 'o', 'WITH', 'o.abbreviation = :country')
            ->leftJoin('c.state', 's', 'WITH', 's.abbreviation = :state')
            ->where('c.nameCanonical = :city')
            ->orderBy('s.abbreviation', 'DESC')
            ->setMaxResults(1)
            ->setParameters(array(
                'city' => preg_replace(
                    '/[^a-z0-9]/',
                    '',
                    strtolower($city)
                ),
                'state' => $state,
                'country' => $country
            ));
        try {
            return $qb->getQuery()->getSingleResult();
        } catch (NoResultException $e) {
        }
    }

    protected function processCriteria(&$qb, array $criteria = array(), $selectAlias = 'e')
    {
        if (isset($criteria['name'])) {
            $qb->andWhere($qb->expr()->like("$selectAlias.name", ':city'))
                ->setParameter('city', $criteria['name'] . '%');
            unset($criteria['name']);
        }

        if (isset($criteria['state'])) {
            $qb->leftJoin("$selectAlias.state", 's');
            $qb->andWhere('s.abbreviation = :state')
                ->setParameter('state', $criteria['state']);
            unset($criteria['state']);
        }
        parent::processCriteria($qb, $criteria, $selectAlias);
    }
}
