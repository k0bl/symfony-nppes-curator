<?php
namespace App\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="provider_cities"),
 * @ORM\HasLifecycleCallbacks
 */
class City
{
    /**
     *@ORM\Column(type="integer")
     *@ORM\Id
     *@ORM\GeneratedValue(strategy="AUTO")
     */
    public $id;

    /**
     * @ORM\ManyToOne(targetEntity="State", inversedBy="cities")
     * @ORM\JoinColumn(name="state_id", referencedColumnName="id", onDelete="SET NULL")
     */
    public $state;

    /**
     * @ORM\ManyToOne(targetEntity="Country", inversedBy="cities")
     * @ORM\JoinColumn(name="country_id", referencedColumnName="id", onDelete="SET NULL")
     */
    public $country;

    /**
     * @ORM\OneToMany(targetEntity="Address", mappedBy="city")
     */
    public $addresses;

    /**
     * @ORM\Column()
     */
    public $name;

    /**
     * @ORM\Column()
     */
    public $nameCanonical;

    /**
     * @ORM\Column(nullable=true)
     */
    public $county;

    /**
     * @ORM\Column(nullable=true, length=2701)
     */
    public $zipCodes;

    /**
     * @ORM\Column(nullable=true)
     */
    public $areaCode;

    /**
     * @ORM\Column(nullable=true, type="decimal", precision=8, scale=5)
     */
    public $latitude;

    /**
     * @ORM\Column(nullable=true, type="decimal", precision=8, scale=5)
     */
    public $longitude;

    /**
     * @ORM\Column(nullable=true, type="integer")
     */
    public $population;

    /**
     * @ORM\Column(nullable=true, type="integer")
     */
    public $households;

    /**
     * @ORM\Column(nullable=true, type="integer")
     */
    public $medianIncome;

    /**
     * @ORM\Column(nullable=true, type="bigint")
     */
    public $landArea;

    /**
     * @ORM\Column(nullable=true, type="bigint")
     */
    public $waterArea;

    /**
     * @ORM\Column(nullable=true)
     */
    public $timeZone;

}