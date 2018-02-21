<?php
namespace App\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\StateRepository")
 * @ORM\Table(name="provider_states"),
 */
class State
{
    /**
     *@ORM\Column(type="integer")
     *@ORM\Id
     *@ORM\GeneratedValue(strategy="AUTO")
     */
    public $id;

    /**
     * @ORM\OneToMany(targetEntity="City", mappedBy="state")
     */
    public $cities;

    /**
     * @ORM\ManyToOne(targetEntity="Country", inversedBy="states")
     * @ORM\JoinColumn(name="country_id", referencedColumnName="id", onDelete="SET NULL")
     */
    public $country;

    /**
     * @ORM\OneToMany(targetEntity="Address", mappedBy="state")
     */
    public $addresses;

    /**
     * @ORM\Column()
     */
    public $name;

    /**
     * @ORM\Column()
     */
    public $abbreviation;

}