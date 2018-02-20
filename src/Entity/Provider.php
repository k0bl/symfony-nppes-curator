<?php
namespace App\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 *@ORM\Entity
 *@ORM\Table(name="provider")
 */
class Provider
{
	/**
	 *@ORM\Column(type="integer")
	 *@ORM\Id
	 *@ORM\GeneratedValue(strategy="AUTO")
	 */
	public $id;

    /**
     * @ORM\Column(name="npi", type="string", nullable=true)
     */
    public $npi;
    
    /**
     * @ORM\Column(name="replacement_npi", type="string", nullable=true)
     */
    public $replacementNpi;

    /**
     * @ORM\Column(name="entity_type", type="integer", nullable=true)
     */
    public $entityType;

    /**
     * @ORM\Column(name="provider_name",  type="string", nullable=true)
     */
    public $providerName;

    /**
     * @ORM\Column(name="gender", type="string", nullable=true)
     */
    public $gender;

    /**
     * @ORM\Column(name="last_name", type="string", nullable=true)
     */
    public $lastName;

    /**
     * @ORM\Column(name="first_name", type="string", nullable=true)
     */
    public $firstName;

    /**
     * @ORM\Column(name="middle_name", type="string", nullable=true)
     */
    public $middleName;

    /**
     * @ORM\Column(name="name_prefix", type="string", nullable=true)
     */
    public $namePrefix;

    /**
     * @ORM\Column(name="name_suffix", type="string", nullable=true)
     */
    public $nameSuffix;

    /**
     * @ORM\Column(name="name_credential", type="string", nullable=true)
     */
    public $nameCredential;

    /**
     * @ORM\Column(name="organization_name", type="string", nullable=true)
     */
    public $organizationName;

    /**
     * @ORM\Column(name="other_organization_name", type="string", nullable=true)
     */
    public $otherOrganizationName;
    
    /**
     * @ORM\OneToMany(targetEntity="Address", mappedBy="provider")
     */
    public $addresses;

    /**
     * @ORM\OneToMany(targetEntity="Number", mappedBy="provider")
     */
    public $numbers;    
    
    /**
     * @ORM\OneToMany(targetEntity="Specialty", mappedBy="provider")
     */
    public $specialties;

}