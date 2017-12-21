<?php
/**
 * This abstract class is used to return a valid SimpleXml object
 * from an xml file that could be local or distant.
 * It's possible to add an XmlCrawlerValidator to validated the source xml with an Xsd file
 * It's also possible to generate archive of imported xml files with adding an XmlCrawlerArchiver object
 *
 * @category   Xml
 * @package    Crawler
 * @author     Etienne de Longeaux <etienne.delongeaux@gmail.com>
 * @copyright  2015 PI-GROUPE
 * @license    http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version    2.3
 * @link       http://opensource.org/licenses/gpl-license.php
 * @since      2015-02-16
 */
namespace Sfynx\CrawlerBundle\Crawler\Generalisation;

use Sfynx\CrawlerBundle\Crawler\XmlCrawlerValidator;
use Sfynx\CrawlerBundle\Crawler\XmlCrawlerIterator;
use Sfynx\CrawlerBundle\Crawler\XmlCrawlerTransformer;
use Sfynx\CrawlerBundle\Crawler\XmlCrawlerHelper;
use Sfynx\CrawlerBundle\Crawler\Exception\ExceptionXmlCrawler;

/**
 * This generic class is used to return a valid SimpleXml object
 * from an xml file that could be local or distant.
 * It's possible to add an XmlCrawlerValidator to validated the source xml with an Xsd file
 * It's also possible to generate archive of imported xml files with adding an XmlCrawlerArchiver object
 *
 * @author  Etienne de Longeaux <etienne.delongeaux@gmail.com>
 * @package Crawler
 */
abstract class AbstractXmlCrawler implements XmlCrawlerInterface
{
    use TraitOverideConfiguration;

    /**
     * @var XmlCrawlerValidator
     */
    protected $validator = null;

    /**
     * @var XmlCrawlerIterator
     */
    protected $iterator = null;

    /**
     * @var XmlCrawlerTransformer
     */
    protected $transformer = null;

    /**
     * @var \SimpleXMLElement
     */
    protected $simpleXml = null;
    protected $typeXml = 'file';

    protected $configuration;
    protected $localXml = null;
    protected $distantXml = null;
    protected $archiver = null;
    protected $errors = [];
    protected $isDestroyFile;

    const simplexmlLoadFunction = [
        'file' => 'simplexml_load_file',
        'source' => 'simplexml_load_string'
    ];

    /**
     * Class constructor
     *
     * @param string $xml path|source to the xml
     * @param array  $options an array of parameters overload the default configuration
     * @param boolean $isDestroyFile
     */
    public function __construct($xml, $options = [], $isDestroyFile = false)
    {
        $this->isDestroyFile = $isDestroyFile;

        if (!file_exists($xml)) {
            $this->typeXml = 'source';
            $this->localXml = $xml;
        } elseif (!XmlCrawlerHelper::pathIsLocal($xml)) {
            $this->validWorkingFolder();
            $this->validCurl();
            $this->distantXml = $xml;
        } else {
            $this->localXml = $xml;
        }
        $this->setDefaultConfiguration();
        $this->overideConfiguration($options);
        $this->setTransformer(new XmlCrawlerTransformer());
    }

    /**
     * this method delete importedfile if source xml was distant
     * if we want de keep imported distant file, we have to set an XmlCrawlerArchiver
     *
     */
    public function __destruct()
    {
        if ($this->isDestroyFile
            && ($this->typeXml == 'file')
            && $this->distantXml !== null
            && file_exists($this->localXml)
        ) {
            unlink($this->localXml);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setValidator(XmlCrawlerValidator $validator)
    {
        $this->validator = $validator;
    }


    /**
     * {@inheritdoc}
     */
    public function setIterator(XmlCrawlerIterator $iterator)
    {
        $this->iterator = $iterator;
    }

    /**
     * {@inheritdoc}
     */
    public function setTransformer(XmlCrawlerTransformer $transformer)
    {
        $this->transformer = $transformer;
    }

    /**
     * {@inheritdoc}
     *
     * @link https://www.w3schools.com/xml/schema_complex_any.asp
     * @link https://msdn.microsoft.com/en-us/library/ms256043(v=vs.110).aspx
     */
    public function getSimpleXml()
    {
        if ($this->localXml === null && $this->distantXml !== null) {
            $this->getDistantXmlWithCurl();
        }
        if ($this->localXml === null) {
            $this->errors['localXml'] = 'localXml is null';

            return false;
        }
        if ($this->validator !== null
            && !$this->validator->xmlIsValid($this->localXml)
        ) {
            $this->errors['xmlNotValide'] = $this->validator->getErrors();

            return false;
        }
        libxml_use_internal_errors(true);

        $simplexmlLoadFunction = self::simplexmlLoadFunction[$this->typeXml];
        if (!$this->simpleXml = call_user_func($simplexmlLoadFunction, $this->localXml)) {
            $this->errors['badFormat'] = XmlCrawlerHelper::formatLibXmlErrors(libxml_get_errors());
            libxml_clear_errors();

            return false;
        }
        return $this->simpleXml;
    }

    /**
     * {@inheritdoc}
     */
    abstract public function getDataInArray(array $data = null);

    /**
     * Array structure of the Xml with default values
     *
     * @param array $data
     * @return array
     */
    abstract protected function defaultParams(array $data = null);

    /**
     * {@inheritdoc}
     */
    public function getDataInObject(array $data = null)
    {
        if (null === $data) {
            return json_decode(json_encode($this->getDataInArray()), FALSE);
        }
        return json_decode(json_encode($data), FALSE);
    }

    /**
     * {@inheritdoc}
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * {@inheritdoc}
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * this function set default configuration parameters
     */
    protected function setDefaultConfiguration()
    {
        $this->configuration = [
            'createFolder' => false,
            'workingFolder' => null,
            'xmlLocalBaseName' => 'xmlImported',
            'verifyCertificates' => true
        ];
    }

    /**
     *
     * @throws ExceptionXmlCrawler
     */
    protected function validCurl()
    {
        if (!in_array('curl', get_loaded_extensions())) {
            throw new ExceptionXmlCrawler('Curl must be loaded before use distant Xml');
        }
    }

    /**
     * @throws ExceptionXmlCrawler
     * @return void
     */
    protected function validWorkingFolder()
    {
        if (!file_exists($this->configuration['workingFolder'])
            && $this->configuration['createFolder']
        ) {
            mkdir($this->configuration['workingFolder'], 0777);
        }
        if (!is_writable($this->configuration['workingFolder'])) {
            throw new ExceptionXmlCrawler('WorkingFolder ('.$this->configuration['workingFolder'].') must be writable');
        }
    }

    /**
     * @return bool
     */
    protected function getDistantXmlWithCurl()
    {
        set_time_limit(0);
        $destination = $this->configuration['workingFolder'] . '/' . $this->configuration['xmlLocalBaseName'] . '.xml';
        $importedXml = fopen($destination, 'w+');
        $curlSession = curl_init($this->distantXml);
        curl_setopt($curlSession, CURLOPT_TIMEOUT, 50);
        curl_setopt($curlSession, CURLOPT_FILE, $importedXml);
        curl_setopt($curlSession, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curlSession, CURLOPT_HEADER, false);
        curl_setopt($curlSession, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlSession, CURLOPT_SSL_VERIFYPEER, $this->configuration['verifyCertificates']);
        $data = curl_exec($curlSession);
        $nbCurlErrors = curl_errno($curlSession);
        $CurlErrors = curl_error($curlSession);
        $http_status = curl_getinfo($curlSession, CURLINFO_HTTP_CODE);
        curl_close($curlSession);
        fwrite($importedXml, $data);
        fclose($importedXml);
        if ($http_status == 200
            && !$nbCurlErrors
            && file_exists($destination)
            && filesize($destination)) {
            $this->localXml = $destination;

            return true;
        } elseif ($nbCurlErrors) {
            $this->errors['getDistantXml'] = $CurlErrors;
        } elseif ($http_status != 200) {
            $this->errors['getDistantXml'] = 'Http response code : ' . $http_status;
        } else {
             $this->errors['getDistantXml'] = 'Error while writing local Xml';
        }

        return false;
    }
}
