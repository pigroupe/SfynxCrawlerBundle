<?php
/**
 * This class is used to transform an xml to array or an xml to array
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

use Sfynx\CrawlerBundle\Crawler\Transformer\XML2DataTransformer;
use Sfynx\CrawlerBundle\Crawler\Transformer\Data2XMLTransformer;

/**
 * @author  Etienne de Longeaux <etienne.delongeaux@gmail.com>
 * @package Crawler
 */
class XmlCrawlerTransformer
{
    /**
     * @var XML2DataTransformer
     */
    private static $xml2data = null;

    /**
     * @var Data2XMLTransformer
     */
    private static $data2xml = null;

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->setXML2DataTransformer(new XML2DataTransformer());
        $this->setData2XMLTransformer(new Data2XMLTransformer());
    }

    /**
     * {@inheritdoc}
     */
    public function setXML2DataTransformer(XML2DataTransformer $transformer)
    {
        self::$xml2data = $transformer;
    }

    /**
     * {@inheritdoc}
     */
    public function setData2XMLTransformer(Data2XMLTransformer $transformer)
    {
        self::$data2xml = $transformer;
    }

    /**
     * Convert an XML to Array
     *
     * @param \DOMDocument|string $input_xml
     * @param string $version
     * @param string $encoding
     * @param boolean $format_output
     *
     * @return array
     */
    public function xml2dataBuilder($input_xml, $version = '1.0', $encoding = 'UTF-8', $format_output = true)
    {
        self::$xml2data->init($version, $encoding, $format_output);
        return self::$xml2data->build($input_xml);
    }

    /**
     * Convert an XML to Array
     *
     * @param string $node_name - name of the root node to be converted
     * @param array $arr - aray to be converterd
     * @param string $version
     * @param string $encoding
     * @param boolean $format_output
     *
     * @return \DomDocument
     */
    public function data2XmlBuilder($node_name, $arr = [], $version = '1.0', $encoding = 'UTF-8', $format_output)
    {
        self::$data2xml->init($version, $encoding, $format_output);
        return self::$data2xml->build($node_name, $arr);
    }

    /**
     *
     * Turns a simplexml object into json.
     *
     * <code>
     * $xml_object=simplexml_load_string($contentXmlFile);
     * $contentFile =Xmlobject2json($xml_object);
     * </code>
     *
     * @param \SimpleXMLElement $object
     * @return string A json content format
     */
    public function Xmlobject2json($object) {
        return @json_encode($object);
    }

    /**
     * Turns a simplexml object into array.
     *
     * @param \SimpleXMLElement $object
     * @return array
     */
    public function Xmlobject2array($object) {
        return @json_decode(@json_encode($object),1);
    }
}
