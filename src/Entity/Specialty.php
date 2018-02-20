<?php
namespace App\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="provider_specialties"),
 * @ORM\HasLifecycleCallbacks
 */
class Specialty
{
    /**
     * @ORM\ManyToOne(targetEntity="Provider", inversedBy="specialties")
     * @ORM\JoinColumn(name="provider_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $provider;

    /**
     * @ORM\ManyToOne(targetEntity="Taxonomy")
     * @ORM\JoinColumn(name="taxonomy_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $taxonomy;


    /**
     * @ORM\Column(name="license")
     */
    protected $license;

    /**
     * @ORM\ManyToOne(targetEntity="State")
     * @ORM\JoinColumn(name="state_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $state;

    /**
     * @ORM\Column(name="isprimary")
     */
    protected $primary;

    /**
     * @ORM\Column(name="code")
     */
    protected $code;


}