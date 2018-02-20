<?php
namespace App\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="provider_cities"),
 * @ORM\HasLifecycleCallbacks
 */
class City
{
    /**
     * @ORM\ManyToOne(targetEntity="State", inversedBy="cities")
     * @ORM\JoinColumn(name="state_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $state;

    /**
     * @ORM\ManyToOne(targetEntity="Country", inversedBy="cities")
     * @ORM\JoinColumn(name="country_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $country;

    /**
     * @ORM\OneToMany(targetEntity="Address", mappedBy="city")
     */
    protected $addresses;

    /**
     * @ORM\Column()
     */
    protected $name;

    /**
     * @ORM\Column()
     */
    protected $nameCanonical;

    /**
     * @ORM\Column(nullable=true)
     */
    protected $county;

    /**
     * @ORM\Column(nullable=true, length=2701)
     */
    protected $zipCodes;

    /**
     * @ORM\Column(nullable=true)
     */
    protected $areaCode;

    /**
     * @ORM\Column(nullable=true, type="decimal", precision=8, scale=5)
     */
    protected $latitude;

    /**
     * @ORM\Column(nullable=true, type="decimal", precision=8, scale=5)
     */
    protected $longitude;

    /**
     * @ORM\Column(nullable=true, type="integer")
     */
    protected $population;

    /**
     * @ORM\Column(nullable=true, type="integer")
     */
    protected $households;

    /**
     * @ORM\Column(nullable=true, type="integer")
     */
    protected $medianIncome;

    /**
     * @ORM\Column(nullable=true, type="bigint")
     */
    protected $landArea;

    /**
     * @ORM\Column(nullable=true, type="bigint")
     */
    protected $waterArea;

    /**
     * @ORM\Column(nullable=true)
     */
    protected $timeZone;

}