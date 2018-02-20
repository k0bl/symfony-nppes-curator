<?php
namespace App\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="provider_specialties"),
 * @ORM\HasLifecycleCallbacks
 */
class Specialty
{
    /**
     *@ORM\Column(type="integer")
     *@ORM\Id
     *@ORM\GeneratedValue(strategy="AUTO")
     */
    public $id;

    /**
     * @ORM\ManyToOne(targetEntity="Provider", inversedBy="specialties")
     * @ORM\JoinColumn(name="provider_id", referencedColumnName="id", onDelete="SET NULL")
     */
    public $provider;

    /**
     * @ORM\ManyToOne(targetEntity="Taxonomy")
     * @ORM\JoinColumn(name="taxonomy_id", referencedColumnName="id", onDelete="SET NULL")
     */
    public $taxonomy;


    /**
     * @ORM\Column(name="license")
     */
    public $license;

    /**
     * @ORM\ManyToOne(targetEntity="State")
     * @ORM\JoinColumn(name="state_id", referencedColumnName="id", onDelete="SET NULL")
     */
    public $state;

    /**
     * @ORM\Column(name="isprimary")
     */
    public $primary;

    /**
     * @ORM\Column(name="code")
     */
    public $code;


}