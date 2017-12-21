<?php
/**
 * @category   Xml
 * @package    Crawler
 * @author     Etienne de Longeaux <etienne.delongeaux@gmail.com>
 * @copyright  2015 PI-GROUPE
 * @license    http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link       http://opensource.org/licenses/gpl-license.php
 */
namespace Sfynx\CrawlerBundle\Crawler\Generalisation;

use Sfynx\CrawlerBundle\Crawler\XmlCrawlerValidator;
use Sfynx\CrawlerBundle\Crawler\XmlCrawlerIterator;

/**
 * Interface XmlCrawlerInterface
 */
interface XmlCrawlerInterface
{
    /**
     * this method set a XmlCrawlerValidator object that will be used ti validate local xml file
     * before return it. This is an option
     *
     * @param XmlCrawlerValidator $validator
     */
    public function setValidator(XmlCrawlerValidator $validator);

    /**
     * @param XmlCrawlerIterator $iterator
     */
    public function setIterator(XmlCrawlerIterator $iterator);

    /**
     * this method return source Xml as SimpleXml Object if it's possible, false if not.
     * It should be false if source xml is distant and unattainable, or xml is incorrectly formatted,
     * or not validate by xsd file (via an XmlCrawlerValidator)
     *
     * @return  \SimpleXMLElement source Xml as SimpleXml Object
     */
    public function getSimpleXml();

    /**
     * this method must return an array of arrays, ready to be set on a specific propel object
     *
     * @param array $data
     * @return array array of arrays, ready to be set on a specific propel object
     * @abstract
     */
    public function getDataInArray(array $data = null);

    /**
     * this method parse xml to set data in array, ready to be set on an Object.
     *
     * @param array $data
     * @return array datas ready to be set
     */
    public function getDataInObject(array $data = null);

    /**
     * This method return configuration's parameters
     *
     * @return array configuration's parameters used by object after instantiation
     */
    public function getConfiguration();

    /**
     * This method return errors generated during processing of the xml source
     *
     * @return array errors generated during processing of the xml source
     */
    public function getErrors();
}
