<?php
namespace App\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AddressRepository")
 * @ORM\Table("provider_addresses")
 */
class Address
{
    /**
     *@ORM\Column(type="integer")
     *@ORM\Id
     *@ORM\GeneratedValue(strategy="AUTO")
     */
    public $id;

    /**
     * @ORM\ManyToOne(targetEntity="Provider", inversedBy="addresses")
     * @ORM\JoinColumn(name="provider_id", referencedColumnName="id", onDelete="SET NULL")
     */
    public $provider;

    /**
     * @ORM\ManyToOne(targetEntity="City", inversedBy="addresses")
     * @ORM\JoinColumn(name="city_id", referencedColumnName="id", onDelete="SET NULL")
     */
    public $city;

    /**
     * @ORM\ManyToOne(targetEntity="State", inversedBy="addresses")
     * @ORM\JoinColumn(name="state_id", referencedColumnName="id", onDelete="SET NULL")
     */
    public $state;

    /**
     * @ORM\ManyToOne(targetEntity="Country", inversedBy="addresses")
     * @ORM\JoinColumn(name="country_id", referencedColumnName="id", onDelete="SET NULL")
     */
    public $country;

    /**
     * @ORM\Column(name="type", type="string")
     */
    public $type;

    /**
     * @ORM\Column(name="first_line", type="string")
     */
    public $firstLine;

    /**
     * @ORM\Column(name="second_line", type="string")
     */
    public $secondLine;

    /**
     * @ORM\Column(name="zip", type="string")
     */
    public $zip;
}