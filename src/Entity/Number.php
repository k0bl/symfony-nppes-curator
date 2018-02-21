<?php
namespace App\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="provider_numbers")
 */
class Number
{
    /**
     *@ORM\Column(type="integer")
     *@ORM\Id
     *@ORM\GeneratedValue(strategy="AUTO")
     */
    public $id;

     /**
     * @ORM\ManyToOne(targetEntity="Provider", inversedBy="numbers")
     * @ORM\JoinColumn(name="provider_id", referencedColumnName="id", onDelete="SET NULL")
     */
    public $provider;

    /**
     * @ORM\Column()
     */
    public $type;
    
    /**
     * @ORM\Column()
     */
    public $location;

    /**
     * @ORM\Column()
     */
    public $number;

}