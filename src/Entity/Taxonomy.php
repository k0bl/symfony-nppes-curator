<?php
namespace App\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TaxonomyRepository")
 * @ORM\Table(name="provider_taxonomies"),
 */
class Taxonomy
{
    /**
     *@ORM\Column(type="integer")
     *@ORM\Id
     *@ORM\GeneratedValue(strategy="AUTO")
     */
    public $id;

    /**
     * @ORM\Column()
     */
    public $code;

    /**
     * @ORM\Column()
     */
    public $grouping;

    /**
     * @ORM\Column()
     */
    public $classification;

    /**
     * @ORM\Column()
     */
    public $specialization;

    /**
     * @ORM\Column()
     */
    public $definition;
}