<?php
/**
 * This class is used to validate an imported xml file from an XmlCrawler object
 * with an XmlSchema file
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

namespace Sfynx\CrawlerBundle\Crawler;

use Sfynx\CrawlerBundle\Crawler\Generalisation\TraitOverideConfiguration;

/**
 * This class is used to validate an imported xml file from an XmlCrawler object
 * with an XmlSchema file
 *
 * @author  Etienne de Longeaux <etienne.delongeaux@gmail.com>
 * @package Crawler
 */
class XmlCrawlerValidator
{
    use TraitOverideConfiguration;

    /** @var \DOMDocument */
    public $currentNode;

    protected $configuration;
    protected $xsd = null;
    protected $errors = [];
    protected $typeXsd = 'source';
    protected $typeXml = 'source';

    const schemaValidateFunction = [
        'file' => 'schemaValidate',
        'source' => 'schemaValidateSource'
    ];

    const loadFunction = [
        'file' => 'load',
        'source' => 'loadXml'
    ];

    /**
     * Class constructor
     *
     * @param string $xsd path|source to the xsd
     * @param array  $options an array of parameters overload the default configuration
     */
    public function __construct($xsd, $options = [])
    {
        $this->xsd = $xsd;
        if (file_exists($this->xsd)) {
            $this->typeXsd = 'file';
        }
        $this->setDefaultConfiguration();
        $this->overideConfiguration($options);
    }

    /**
     * This method return configuration's parameters
     *
     * @return array configuration's parameters used by object after instantiation
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * This function test the validity of the xml file|source
     *
     * @param string $xml path|source to the xml to validate
     */
    public function xmlIsValid($xml)
    {
        $this->currentNode = new \DOMDocument();

        if (file_exists($xml)) {
            $this->typeXml = 'file';
        }
        if ($xml instanceof \DOMDocument) {
            $xml = $xml->saveXML();
        }
        $loadFunction = self::loadFunction[$this->typeXml];
        $this->currentNode->$loadFunction($xml);

        libxml_use_internal_errors(true);
        $schemaValidateFunction = self::schemaValidateFunction[$this->typeXsd];
        if (!$this->currentNode->$schemaValidateFunction($this->xsd)) {
            $this->errors = XmlCrawlerHelper::formatLibXmlErrors(libxml_get_errors());
            libxml_clear_errors();
            return false;
        }
        return true;
    }

    /**
     * This method return errors generated during processing of the xml source validation
     *
     * @return array errors generated during processing of the xml source validation
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
            'xsdLocalBaseName' => 'xsdImported',
            'archiveError' => false,
            'archiveTimestamp' => 'date', //should be date or datetime
            'archiveNumber' => 7
        ];
    }
}
