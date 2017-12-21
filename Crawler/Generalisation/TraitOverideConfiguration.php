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

trait TraitOverideConfiguration {

    /**
     * This method overide default configuration parameters with parameters passed in $options from constructor
     *
     * @param array $options parameters passed in $options from constructor
     */
    protected function overideConfiguration($options)
    {
        if (null !== $options) {
            array_map([$this, 'saveKeyConfiguration'], array_keys($options), array_values($options));
        }
    }

    /**
     *
     * @param integer $optionKey
     * @param string $optionValue
     */
    protected function saveKeyConfiguration($optionKey, $optionValue)
    {
        if (array_key_exists($optionKey, $this->configuration)
            && (is_bool($optionValue) || trim($optionValue) != "")
        ) {
            $this->configuration[$optionKey] = $optionValue;
        }
    }
}