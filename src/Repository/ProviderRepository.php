<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;

class ProviderRepository extends EntityRepository
{
    protected function getSearchQueryBuilder(
        $selectAlias,
        array $criteria = array(),
        $orderBy = null,
        $limit = null,
        $offset = null
    ) {
        $qb = parent::getSearchQueryBuilder($selectAlias, $criteria, $orderBy, $limit, $offset);
        $qb->resetDQLPart('orderBy');
        $this->addProviderCriteria($qb, $selectAlias);
        return $qb;
    }
    protected function getCountQueryBuilder(
        $selectAlias,
        array $criteria = array()
    ) {
        $qb = parent::getCountQueryBuilder($selectAlias, $criteria);
        $qb->select('count(DISTINCT e.id)')
            ->resetDQLPart('orderBy');
        $this->addProviderCriteria($qb, $selectAlias);
        return $qb;
    }

    protected function addProviderCriteria(&$qb, $selectAlias)
    {
        // $qb->leftJoin("$selectAlias.addresses", 'a');
        // $qb->leftJoin("$selectAlias.specialties", 's');
        // $qb->leftJoin("s.taxonomy", 't');
    }

    protected function processCriteria(&$qb, array $criteria = array(), $selectAlias = 'e')
    {
        if (isset($criteria['name'])) {
            $terms = array_slice(explode(' ', $criteria['name']), 0, 4);
            $params = array();
            $ors = array();

            foreach ($terms as $k => $term) {
                $or = $qb->expr()->orX();
                $name = "term$k";
                $params[$name] = $term;
                $name = ":$name";
                $or->add($qb->expr()->like("$selectAlias.firstName", $name));
                $or->add($qb->expr()->like("$selectAlias.lastName", $name));
                $or->add($qb->expr()->like("$selectAlias.middleName", $name));
                $or->add($qb->expr()->like("$selectAlias.providerName", $name));
                $or->add($qb->expr()->like("$selectAlias.organizationName", $name));
                $ors[] = $or;
            }

            $qb->andWhere($qb->expr()->andX()->addMultiple($ors));
            foreach ($params as $name => $param) {
                $qb->setParameter($name, "%$param%");
            }
            unset($criteria['name']);
        }

        if (isset($criteria['city']) || isset($criteria['state'])) {
            $qb->leftJoin("$selectAlias.addresses", 'a');
        }

        if (isset($criteria['city'])) {
            $qb->leftJoin('a.city', 'ac');
            $qb->andWhere($qb->expr()->like('ac.name', ':city'))
                ->setParameter('city', $criteria['city'] . '%');
            unset($criteria['city']);
        }

        if (isset($criteria['state'])) {
            $qb->leftJoin('a.state', 'ast');
            $qb->andWhere('ast.abbreviation = :state')
                ->setParameter('state', $criteria['state']);
            unset($criteria['state']);
        }
        parent::processCriteria($qb, $criteria, $selectAlias);
    }

    public function idSearch(
        array $criteria = array(),
        $orderBy = null,
        $limit = null,
        $offset = null
    ) {
        $q = $this->buildIdSearchQuery($criteria, $orderBy, $limit, $offset);
        return array_map(
            function ($i) {
                return $i['id'];
            },
            $q->getScalarResult()
        );
    }

    protected function buildIdSearchQuery(
        array $criteria = array(),
        $orderBy = null,
        $limit = null,
        $offset = null
    ) {
        $selectAlias = 'e';
        $qb = $this->getSearchQueryBuilder(
            $selectAlias,
            $criteria,
            $orderBy,
            $limit,
            $offset
        );
        $qb->select('e.id')->distinct();
        return $qb->getQuery();
    }

    public function fetchAll(array $ids)
    {
        $selectAlias = 'e';
        $qb = $this->createFetchAllQueryBuilder($selectAlias);
        $qb->where("$selectAlias.id IN (?1)")
            ->setParameter(1, $ids);
        return $qb->getQuery()->getResult();
    }

    public function createFetchAllQueryBuilder($selectAlias = 'e')
    {
        $qb = $this->createQueryBuilder($selectAlias);
        $qb
            ->select(array($selectAlias, 'a', 'ast', 'ac', 's', 'sst', 't', 'c'))
            ->leftJoin("$selectAlias.claims", 'c')
            ->leftJoin("$selectAlias.addresses", 'a')
            ->leftJoin('a.city', 'ac')
            ->leftJoin('ac.state', 'ast')
            ->leftJoin("$selectAlias.specialties", 's')
            ->leftJoin('s.state', 'sst')
            ->leftJoin('s.taxonomy', 't');
        return $qb;
    }
}
