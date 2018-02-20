<?php
namespace App\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="provider_countries"),
 * @ORM\HasLifecycleCallbacks
 */
class Country
{
    /**
     * @ORM\OneToMany(targetEntity="City", mappedBy="country")
     */
    protected $cities;

    /**
     * @ORM\OneToMany(targetEntity="State", mappedBy="country")
     */
    protected $states;

    /**
     * @ORM\OneToMany(targetEntity="Address", mappedBy="country")
     */
    protected $addresses;

    /**
     * @ORM\Column()
     */
    protected $commonName;

    /**
     * @ORM\Column()
     */
    protected $officialName;

    /**
     * @ORM\Column()
     */
    protected $abbreviation;

    /**
     * @ORM\Column()
     */
    protected $cca3;
    /**
     * @ORM\Column()
     */
    protected $ccn3;

}