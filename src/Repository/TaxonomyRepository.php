<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;

class TaxonomyRepository extends EntityRepository
{
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
                $or->add($qb->expr()->like("$selectAlias.grouping", $name));
                $or->add($qb->expr()->like("$selectAlias.classification", $name));
                $or->add($qb->expr()->like("$selectAlias.specialization", $name));
                $or->add($qb->expr()->like("$selectAlias.definition", $name));
                $ors[] = $or;
            }

            $qb->andWhere($qb->expr()->andX()->addMultiple($ors));
            foreach ($params as $name => $param) {
                $qb->setParameter($name, "%$param%");
            }
            unset($criteria['name']);
        }
        parent::processCriteria($qb, $criteria, $selectAlias);
    }
}
