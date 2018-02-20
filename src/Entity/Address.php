<?php
namespace App\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table("provider_addresses")
 */
class Address
{
    /**
     * @ORM\ManyToOne(targetEntity="Provider", inversedBy="addresses")
     * @ORM\JoinColumn(name="provider_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $provider;

    /**
     * @ORM\ManyToOne(targetEntity="City", inversedBy="addresses")
     * @ORM\JoinColumn(name="city_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $city;

    /**
     * @ORM\ManyToOne(targetEntity="State", inversedBy="addresses")
     * @ORM\JoinColumn(name="state_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $state;

    /**
     * @ORM\ManyToOne(targetEntity="Country", inversedBy="addresses")
     * @ORM\JoinColumn(name="country_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $country;

    /**
     * @ORM\Column(name="type", type="string")
     */
    protected $type;

    /**
     * @ORM\Column(name="first_line", type="string")
     */
    protected $firstLine;

    /**
     * @ORM\Column(name="second_line", type="string")
     */
    protected $secondLine;

    /**
     * @ORM\Column(name="zip", type="string")
     */
    protected $zip;
}