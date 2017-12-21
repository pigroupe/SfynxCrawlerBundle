<?php
/**
 * This class is used to work with iterator node from xml
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

/**
 * This class is used to validate an imported xml file from an XmlCrawler object
 * with an XmlSchema file
 *
 * @author  Etienne de Longeaux <etienne.delongeaux@gmail.com>
 * @package Crawler
 */
class XmlCrawlerIterator implements \Iterator, \Countable
{
    /**
     * @var XmlCrawlerValidator
     */
    protected $validator = null;

    /**
     * @var string
     */
    protected $xpath;

    /**
     * @var array
     */
    protected $namespaces;

    /**
     * @var string
     */
    protected $localXml = null;

    /**
     * @param XmlCrawlerValidator $validator
     * @param string|\DOMDocument $xml XML to to iterate over
     * @param string $xpath XPath defining which nodes to iterate over
     * @param array $namespaces Associative array used to define namespaces for XPath
     */
    public function __construct(XmlCrawlerValidator $validator, $xml, $xpath = null, $namespaces = [])
    {
        if ($xml instanceof \DOMDocument) {
            $this->localXml = $xml->saveXML();
        } else {
            $this->localXml = $xml;
        }
        $this->xpath = $xpath;
        $this->namespaces = $namespaces;
        $this->validator = $validator;
        $this->validator->xmlIsValid($this->localXml);
    }

    /**
     * @return integer
     */
    public function count()
    {
        $xpath = new \DOMXPath($this->validator->currentNode);
        foreach ($this->namespaces as $prefix => $namespace) {
            $xpath->registerNamespace($prefix, $namespace);
        }
        $nodes = $xpath->query(rtrim($this->xpath, '[1]'));
        return $nodes->length;
    }

    /**
     * @inheritdoc
     */
    public function current()
    {
        $currentNode = $this->getCurrentNode();
        return $currentNode ? $this->validator->currentNode->saveXML($currentNode) : null;
    }

    /**
     * @inheritdoc
     */
    public function next()
    {
        $currentNode = $this->getCurrentNode();
        $currentNode->parentNode->removeChild($currentNode);
    }

    /**
     * @inheritdoc
     */
    public function key()
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function valid()
    {
        return (boolean) $this->getCurrentNode();
    }

    /**
     * @inheritdoc
     */
    public function rewind()
    {
    }

    /**
     * @return \DOMNode|null
     */
    public function getCurrentNode()
    {
        $xpath = new \DOMXPath($this->validator->currentNode);
        foreach ($this->namespaces as $prefix => $namespace) {
            $xpath->registerNamespace($prefix, $namespace);
        }
        $nodes = $xpath->query($this->getXPath());
        return $nodes->length ? $nodes->item(0) : null;
    }

    /**
     * @return string
     */
    public function getXPath()
    {
        $xpath  = $this->xpath;
        $suffix = '[1]';
        if (strlen($xpath) - strlen($suffix) !== strrpos($xpath, $suffix)) {
            $xpath .= $suffix;
        }
        return $xpath;
    }

    /**
     * @param \DOMXPath $xpath
     * @return string
     */
    public function setXPath($xpath)
    {
        $this->xpath = $xpath;
        return $this;
    }

    /**
     * @param array $namespaces
     * @return $this
     */
    public function setNamespaces(array $namespaces)
    {
        $this->namespaces = $namespaces;
        return $this;
    }

    /**
     * @param string $prefix
     * @param string $namespace
     * @return $this
     */
    public function addNamespace($prefix, $namespace)
    {
        $this->namespaces[$prefix] = $namespace;
        return $this;
    }
}
