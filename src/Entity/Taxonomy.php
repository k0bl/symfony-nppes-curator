<?php
namespace App\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="provider_taxonomies"),
 * @ORM\HasLifecycleCallbacks
 */
class City
{
    /**
     * @ORM\Column(name="code", type="string")
     */
    protected $code;

    /**
     * @ORM\Column(name="grouping", type="string")
     */
    protected $grouping;

    /**
     * @ORM\Column(name="classification", type="string")
     */
    protected $classification;

    /**
     * @ORM\Column(name="specialization", type="string")
     */
    protected $specialization;

    /**
     * @ORM\Column(name="definition", type="string")
     */
    protected $definition;
}