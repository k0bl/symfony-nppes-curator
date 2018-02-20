<?php
namespace App\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="provider_states"),
 * @ORM\HasLifecycleCallbacks
 */
class State
{
    /**
     * @ORM\OneToMany(targetEntity="City", mappedBy="state")
     */
    protected $cities;

    /**
     * @ORM\ManyToOne(targetEntity="Country", inversedBy="states")
     * @ORM\JoinColumn(name="country_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $country;

    /**
     * @ORM\OneToMany(targetEntity="Address", mappedBy="state")
     */
    protected $addresses;

    /**
     * @ORM\Column()
     */
    protected $name;

    /**
     * @ORM\Column()
     */
    protected $abbreviation;

}