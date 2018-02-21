<?php
namespace App\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="provider_countries")
 */
class Country
{
    /**
     *@ORM\Column(type="integer")
     *@ORM\Id
     *@ORM\GeneratedValue(strategy="AUTO")
     */
    public $id;
    
    /**
     * @ORM\OneToMany(targetEntity="City", mappedBy="country")
     */
    public $cities;

    /**
     * @ORM\OneToMany(targetEntity="State", mappedBy="country")
     */
    public $states;

    /**
     * @ORM\OneToMany(targetEntity="Address", mappedBy="country")
     */
    public $addresses;

    /**
     * @ORM\Column()
     */
    public $commonName;

    /**
     * @ORM\Column()
     */
    public $officialName;

    /**
     * @ORM\Column()
     */
    public $abbreviation;

    /**
     * @ORM\Column()
     */
    public $cca3;
    /**
     * @ORM\Column()
     */
    public $ccn3;

}