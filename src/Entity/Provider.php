<?php
namespace App\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 *@ORM\Entity
 *@ORM\Table(name="provider")
 */
class Provider
{
	/**
	 *@ORM\Column(type="integer")
	 *@ORM\Id
	 *@ORM\GeneratedValue(stategy="auto")
	 */
	public $id;

    /**
     * @ORM\Column(name="npi", type="string", nullable=true)
     */
    protected $npi;
    
    /**
     * @ORM\Column(name="replacement_npi", type="string", nullable=true)
     */
    protected $replacementNpi;

    /**
     * @ORM\Column(name="entity_type", type="integer", nullable=true)
     */
    protected $entityType;

    /**
     * @ORM\Column(name="provider_name",  type="string", nullable=true)
     */
    protected $providerName;

    /**
     * @ORM\Column(name="gender", type="string", nullable=true)
     */
    protected $gender;

    /**
     * @ORM\Column(name="last_name", type="string", nullable=true)
     */
    protected $lastName;

    /**
     * @ORM\Column(name="first_name", type="string", nullable=true)
     */
    protected $firstName;

    /**
     * @ORM\Column(name="middle_name", type="string", nullable=true)
     */
    protected $middleName;

    /**
     * @ORM\Column(name="name_prefix", type="string", nullable=true)
     */
    protected $namePrefix;

    /**
     * @ORM\Column(name="name_suffix", type="string", nullable=true)
     */
    protected $nameSuffix;

    /**
     * @ORM\Column(name="name_credential", type="string", nullable=true)
     */
    protected $nameCredential;

    /**
     * @ORM\Column(name="organization_name", type="string", nullable=true)
     */
    protected $organizationName;

    /**
     * @ORM\Column(name="other_organization_name", type="string", nullable=true)
     */
    protected $otherOrganizationName;
    
    /**
     * @ORM\OneToMany(targetEntity="Address", mappedBy="provider")
     */
    protected $addresses;

    /**
     * @ORM\OneToMany(targetEntity="PhoneNumber", mappedBy="provider")
     */
    protected $phoneNumbers;    

    /**
     * @ORM\OneToMany(targetEntity="FaxNumber", mappedBy="provider")
     */
    protected $faxNumbers;
    
    /**
     * @ORM\OneToMany(targetEntity="Specialty", mappedBy="provider")
     */
    protected $specialties;

}