<?php

namespace App\Command;

use App\Entity\City;
use App\Entity\State;
use App\Entity\Country;
use App\Entity\Provider;
use App\Entity\Address;
use App\Entity\Number;
use App\Entity\Taxonomy;
use App\Entity\Specialty;

use App\Exception\ProviderCreateException;
use App\Exception\ProviderDeactivateException;
use App\Exception\ProviderImportException;
use App\Exception\ProviderSkipException;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use App\Model\NppesInterface as Nppes;
use App\Util\SystemLoggingTrait;
use Psr\Log\LoggerInterface;

use Doctrine\Commmon\Collections\ArrayCollection;


class NppesImportCommand extends ContainerAwareCommand
{
    use SystemLoggingTrait;
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
        parent::__construct();
    }
    private $itemCount = 0;
    private $iteration = 0;

    private $addressmanager = null;
    private $providermanager = null;
    private $taxonomymanager = null;

    /**
     * Indexes of taxonomy codes from import data
     *
     * Associative array whose keys will match the keys of the fields
     * with taxonomy codes. Values are ignored.
     * array (47=>null, 51=>null, etc)
     *
     * @var array
     */
    private $taxonomyCodeFields = array();
    /**
     * @var array
     */
    private $blacklistedTaxonomyCodes = array();

    protected function configure()
    {
        $this
            ->setName('nppes:import')
            ->setDescription('Import NPPES datafile.')
            ->addArgument(
                'file',
                InputArgument::REQUIRED,
                'input file'
            )
            ->addOption(
                'dry-run',
                null,
                InputOption::VALUE_NONE,
                'Perform a dry run, do not change anything'
            );
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
        // $this->logger = $this->get('logger');

        $this->dryRun = $input->getOption('dry-run');
        if ($this->dryRun) {
            $this->logger->warning('Performing a dry run');
        }

        $range = range(47, 106, 4);
        $this->taxonomyCodeFields = array_combine($range, array_fill(0, count($range), null));

        $file = $this->getContainer()->getParameter('kernel.root_dir') . '/Resources/taxonomy_exclusion_list.txt';
        $this->blacklistedTaxonomyCodes = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $col = new ArrayCollection();
        $output->writeln($col->count());

        $this->io->title('Import NPPES Datafile');
        $file = $input->getArgument('file');
        if (!file_exists($file) || !is_readable($file)) {
            $this->io->error("The file '$file' does not exist or is not readable.");

            return 1;
        }
        $fp = @fopen($file, 'r');
        if (!$fp) {
            $this->io->error("Unable to open '$file' for reading.");

            return 1;
        }
        $line = fgets($fp); // chomp the first line
        $line = fgetcsv($fp);

        if (!$line) {
            throw new \RuntimeException("There are no records in the file '$file'?");
        }

        do {
            ++$this->iteration;

            // NPI Exist in db?
                // Yes:
                    // only NPI and deactivation date?
                        // Yes: Deactivate our record
                        // No: Update our record
                // No
                    // only NPI and deactivation date?
                        // Yes: process next line
                        // No: Create our record

            // Temporary work load.
            try {
                $this->doIterate($line, $output);
            } catch (ProviderImportException $e) {
                switch (get_class($e)) {
                    case ProviderCreateException::class:
                        // Or
                    case ProviderDeactivateException::class:
                        // Or
                    case ProviderUpdateException::class:
                        $this->logger->notice($e->getMessage());
                        break;
                    default:
                        $this->logger->info($e->getMessage());
                        // code...
                        break;
                }
                $this->reportIteration(
                    $e->getIndicator(),
                    $output
                );
            } catch (\Exception $e) {
                $this->logger->warning($e->getMessage());
                $this->reportIteration(
                    'E',
                    $output
                );
            }

            $this->processFlush($output);

            $line = fgetcsv($fp);
        } while ($line);
        $this->reportFinished($output);
        $this->flush();

        fclose($fp);
    }

    protected function doIterate($line, $output)
    {
        $iterResult = null;

        if ($line[Nppes::NPI_DEACTIVATION_DATE] || $line[Nppes::NPI_REACTIVATION_DATE]) {
            throw new ProviderDeactivateException($line[Nppes::NPI].': Record has been deactivated', 'D');
        }

        $this->throwExceptionForBlacklistedTaxonomyCodes($line, $line[Nppes::NPI].': ');
        $em = $this->getEM();

        if ($em->getRepository(Provider::class)->findByNpi($line[Nppes::NPI])) {
            throw new ProviderSkipException($line[Nppes::NPI]. ': Provider already exists in database', '.');
        }
        $provider = $this->createProviderAttributes($line);
        $mailingPhoneNumber = $this->createMailingPhoneNumber($line);
        $mailingFaxNumber = $this->createMailingFaxNumber($line);
        $practicePhoneNumber = $this->createPracticePhoneNumber($line);
        $practiceFaxNumber = $this->createPracticeFaxNumber($line);

        $provider->numbers->add($mailingPhoneNumber);
        $provider->numbers->add($mailingFaxNumber);
        $provider->numbers->add($practicePhoneNumber);
        $provider->numbers->add($practiceFaxNumber);
        
        $address = $this->createAddress($line);
        $practiceAddress = $this->createPracticeAddress($line);

        $provider->addresses->add($address);
        $provider->addresses->add($practiceAddress);

        $this->processSpecialties($line, $provider);

        $this->persist($address);
        $this->persist($practiceAddress);
        $this->persist($provider);

        throw new ProviderCreateException($line[Nppes::NPI] . ": $providerName has been added to the database", 'C');
    }
    
    protected function createProviderAttributes($line)
    {
        $providerName = $this->calculateProviderName($line);
        $provider = new Provider();
        $provider->providerName = $providerName;
        $provider->npi = $line[Nppes::NPI];
        $provider->entityType = $line[Nppes::ENTITY_TYPE_CODE];
        $provider->replacementNpi = $line[Nppes::REPLACEMENT_NPI];
        $provider->gender = $line[Nppes::PROVIDER_GENDER_CODE];
        $provider->firstName = $this->ucname($line[Nppes::PROVIDER_FIRST_NAME]);
        $provider->middleName = $this->ucname($line[Nppes::PROVIDER_MIDDLE_NAME]);
        $provider->lastName = $this->ucname($line[Nppes::PROVIDER_LAST_NAME]);
        $provider->namePrefix = $this->ucname($line[Nppes::PROVIDER_NAME_PREFIX_TEXT]);
        $provider->nameSuffix = $this->ucname($line[Nppes::PROVIDER_NAME_SUFFIX_TEXT]);
        $provider->nameCredential = $line[Nppes::PROVIDER_CREDENTIAL_TEXT];
        $provider->organizationName = $this->ucwords($line[Nppes::PROVIDER_ORGANIZATION_NAME]);
        $provider->otherOrganizationName = $this->ucwords($line[Nppes::PROVIDER_OTHER_ORGANIZATION_NAME]);
        return $provider;
    }    
    
    protected function createAddress($line) {

        $address = new Address();
        $address->type = 'MAILING';
        $address->firstLine = $this->ucwords($line[Nppes::PROVIDER_FIRST_LINE_BUSINESS_MAILING_ADDRESS]);
        $address->secondLine = $this->ucwords($line[Nppes::PROVIDER_SECOND_LINE_BUSINESS_MAILING_ADDRESS]);
        $address->city = $this->findOrCreateCity(
            $this->ucwords($line[Nppes::PROVIDER_BUSINESS_MAILING_ADDRESS_CITY_NAME]),
            $line[Nppes::PROVIDER_BUSINESS_MAILING_ADDRESS_STATE_NAME]
        );
        $address->state = $this->findState($line[Nppes::PROVIDER_BUSINESS_MAILING_ADDRESS_STATE_NAME]);
        $address->country = $line[Nppes::PROVIDER_BUSINESS_MAILING_ADDRESS_COUNTRY_CODE];
        $address->zip = $line[Nppes::PROVIDER_BUSINESS_MAILING_ADDRESS_POSTAL_CODE];
        return $address;
    }    
    
    protected function createPracticeAddress($line) {
        
        $practiceAddress = new Address();
        $practiceAddress->type = 'PRACTICE';
        $practiceAddress->firstLine = $this->ucwords($line[Nppes::PROVIDER_FIRST_LINE_BUSINESS_PRACTICE_LOCATION_ADDRESS]);
        $practiceAddress->secondLine = $this->ucwords($line[Nppes::PROVIDER_SECOND_LINE_BUSINESS_PRACTICE_LOCATION_ADDRESS]);
        $practiceAddress->city = $this->findOrCreateCity(
            $this->ucwords($line[Nppes::PROVIDER_BUSINESS_PRACTICE_LOCATION_ADDRESS_CITY_NAME]),
            $line[Nppes::PROVIDER_BUSINESS_PRACTICE_LOCATION_ADDRESS_STATE_NAME]
        );
        $practiceAddress->state = $this->findState($line[Nppes::PROVIDER_BUSINESS_PRACTICE_LOCATION_ADDRESS_STATE_NAME]);
        $practiceAddress->country = $line[Nppes::PROVIDER_BUSINESS_PRACTICE_LOCATION_ADDRESS_COUNTRY_CODE];
        $practiceAddress->zip = $line[Nppes::PROVIDER_BUSINESS_PRACTICE_LOCATION_ADDRESS_POSTAL_CODE];

        return $practiceAddress;
    }
    
    protected function createMailingPhoneNumber($line) {

        $mailingPhoneNumber = new Number();
        $mailingPhoneNumber->number = $line[Nppes::PROVIDER_BUSINESS_MAILING_ADDRESS_TELEPHONE_NUMBER];
        $mailingPhoneNumber->type = "PHONE";
        $mailingPhoneNumber->location = "MAILING";
        return $mailingPhoneNumber;
    }

    protected function createMailingFaxNumber($line) {
        $mailingFaxNumber = new Number();
        $mailingFaxNumber->number = $line[Nppes::PROVIDER_BUSINESS_MAILING_ADDRESS_FAX_NUMBER];
        $mailingFaxNumber->type = "FAX";
        $mailingFaxNumber->location = "MAILING";
        return $mailingFaxNumber;
    }
    
    protected function createPracticePhoneNumber($line) {
        
        $practicePhoneNumber = new Number();
        $practicePhoneNumber->number = $line[Nppes::PROVIDER_BUSINESS_PRACTICE_LOCATION_ADDRESS_TELEPHONE_NUMBER];
        $practicePhoneNumber->type = "PHONE";
        $practicePhoneNumber->location = "PRACTICE";
        return $practicePhoneNumber;
    }    

    protected function createPracticeFaxNumber($line) {
        $practiceFaxNumber = new Number();
        $practiceFaxNumber->number = $line[Nppes::PROVIDER_BUSINESS_MAILING_ADDRESS_FAX_NUMBER];
        $practiceFaxNumber->type = "FAX";
        $practiceFaxNumber->location = "PRACTICE";
        return $practiceFaxNumber;
    }

    protected function calculateProviderName($line)
    {
        switch ($line[Nppes::ENTITY_TYPE_CODE]) {
            case 2:
                if ($line[Nppes::PROVIDER_OTHER_ORGANIZATION_NAME]) {
                    return $this->ucwords($line[Nppes::PROVIDER_OTHER_ORGANIZATION_NAME]);
                } elseif ($line[Nppes::PROVIDER_ORGANIZATION_NAME]) {
                    return $this->ucwords($line[Nppes::PROVIDER_ORGANIZATION_NAME]);
                } else {
                    return 'No Provider Name';
                }
                break;
            case 1:
                return trim(
                    preg_replace(
                        '/  +/', // spaces followed by 1 or more spaces
                        ' ', // are replaced with single spaces
                        sprintf(
                            '%s %s %s %s %s %s',
                            $this->ucname($line[Nppes::PROVIDER_NAME_PREFIX_TEXT]),
                            $this->ucname($line[Nppes::PROVIDER_FIRST_NAME]),
                            $this->ucname($line[Nppes::PROVIDER_MIDDLE_NAME]),
                            $this->ucname($line[Nppes::PROVIDER_LAST_NAME]),
                            $this->ucname($line[Nppes::PROVIDER_NAME_SUFFIX_TEXT]),
                            $line[Nppes::PROVIDER_CREDENTIAL_TEXT]
                        )
                    )
                );
                break;
            default:
                throw new \RuntimeException("Unknown entity type: " . $line[Nppes::ENTITY_TYPE_CODE]);
                break;
        }
    }

    protected function processSpecialties($line, $provider)
    {
        $em = $this->getEM();
        foreach (range(47, 106, 4) as $lineCount) {
            if ($line[$lineCount]) {
                $specialty = new Specialty();
                $specialty->code = $line[$lineCount];
                //find taxonomy, relate to specialty
                $taxonomy = $em->getRepository(Taxonomy::class)->findByCode($line[$lineCount]);

                // $taxonomy = $this->taxonomymanager->findByCode($line[$lineCount]);

                if (!$taxonomy) {
                    $this->logger->warning('Taxonomy Code not found '.$line[$lineCount]);
                    break;
                }
                $specialty->taxonomy = $taxonomy;
                ++$lineCount;
                $specialty->license = $line[$lineCount];
                ++$lineCount;
                $specialty->state = $this->findState($line[$lineCount]);
                ++$lineCount;
                $specialty->primary = $line[$lineCount];
                ++$lineCount;
                
                $provider->specialties->add($specialty);
                $this->persist($specialty);
            } else {
                break;
            }
        }
    }

    protected function processFlush($output)
    {
        if (0 === $this->iteration % 1000) {
            $out = $this->iteration . ": Flushing...\n";
            $output->write($out);
            $this->flush();
        }
    }

    protected function reportIteration($status, OutputInterface $output)
    {
        if ($output->getVerbosity() !== OutputInterface::VERBOSITY_NORMAL) {
            return;
        }

        if (0 === $this->iteration % 50) {
            $status = "$status\n";
        }
        if (1 === $this->iteration % 10) {
            $status = " $status";
        }
        $output->write($status);
    }

    protected function reportFinished(OutputInterface $output)
    {
        if ($output->getVerbosity() !== OutputInterface::VERBOSITY_NORMAL) {
            return;
        }
        $output->write("\n");
    }

    protected function flush()
    {
        if (!$this->dryRun) {
            $this->get('doctrine.orm.entity_manager')->flush();
        }
    }
    
    protected function persist($entity)
    {
        $this->getEM()->persist($entity);
    }

    protected function get($service)
    {
        return $this->getContainer()->get($service);
    }

    protected function ucname(
        $string,
        $delimiters = array(' ', '-', '.', "'", "O'", 'Mc'),
        $exceptions = array('de', 'da', 'dos', 'das', 'do', 'I', 'II', 'III', 'IV', 'V', 'VI')
    ) {
        /*
         * Exceptions in lower case are words you don't want converted
         * Exceptions all in upper case are any words you don't want converted to title case
         *   but should be converted to upper case, e.g.:
         *   king henry viii or king henry Viii should be King Henry VIII
         */
        $string = mb_convert_case($string, MB_CASE_TITLE, 'UTF-8');
        foreach ($delimiters as $dlnr => $delimiter) {
            $words = explode($delimiter, $string);
            $newwords = array();
            foreach ($words as $wordnr => $word) {
                if (in_array(mb_strtoupper($word, 'UTF-8'), $exceptions)) {
                    // check exceptions list for any words that should be in upper case
                    $word = mb_strtoupper($word, 'UTF-8');
                } elseif (in_array(mb_strtolower($word, 'UTF-8'), $exceptions)) {
                    // check exceptions list for any words that should be in upper case
                    $word = mb_strtolower($word, 'UTF-8');
                } elseif (!in_array($word, $exceptions)) {
                    // convert to uppercase (non-utf8 only)
                    $word = ucfirst($word);
                }
                array_push($newwords, $word);
            }
            $string = implode($delimiter, $newwords);
        }//foreach
        return $string;
    }

    protected function ucwords($string)
    {
        return ucwords(strtolower($string));
    }

    public function findState($abbr)
    {
        $em = $this->getEM();
        return $em->getRepository(State::class)->findOneByAbbreviation($abbr);
    }

    protected function findOrCreateCity($cityStr, $stateStr)
    {
        $em = $this->getEM();
        $city = $em->getRepository(City::class)->findByNameAndStateAbbreviation($cityStr, $stateStr);
        if (!$city) {
            $state = $this->findState($stateStr);
            $city = new City();
            $city->name = $cityStr;
            $city->state = $state;
            $em->persist($city);
            $em->flush($city);
        }
        return $city;
    }

    protected function getEM()
    {
        return $this->get('doctrine.orm.entity_manager');
    }

    protected function throwExceptionForBlacklistedTaxonomyCodes($line, $logLine)
    {
        $taxonomyValues = array_filter(
            array_intersect_key(
                $line,
                $this->taxonomyCodeFields
            )
        );

        $matches = array_intersect(
            $taxonomyValues,
            $this->blacklistedTaxonomyCodes
        );

        if (!empty($matches)) {
            throw new ProviderSkipException(
                $logLine . 'Provider has a blacklisted taxonomy code: ' . implode(', ', $matches),
                'B'
            );
        }
    }
}
