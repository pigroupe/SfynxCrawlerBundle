<?php
namespace Sfynx\CrawlerBundle\Tests\Crawler\Crawler;

use Sfynx\CrawlerBundle\Crawler\Generalisation\AbstractXmlCrawler;

class ExCrawler extends AbstractXmlCrawler
{

    /**
     * this method overdide parent method to create a valid distant path, with a valid token
     *
     * @return \SimpleXml source Xml as SimpleXml Object
     */
    public function getSimpleXml()
    {
        if ($this->distantXml !== null) {
            $this->prepareDistantUrl();
        }

        return parent::getSimpleXml();
    }

    /**
     * this method parse xml to set data in array, ready to be set on an Object.
     *
     * @return array datas ready to be set
     */
    public function getDataInArray()
    {
        $dataInArray = [];
        $xml = $this->getSimpleXml();
        if (!$xml) {
            return false;
        }
        foreach ($xml->coupon as $coupon) {
            $objectData = [];
            $objectData['id'] = (integer) $coupon->id;
            $objectData['amount'] = (float) $coupon->amount;
            $objectData['img_url'] = (string) $coupon->img_url;
            $objectData['text'] = (string) $coupon->text;
            $objectData['brand'] = (string) $coupon->brand;
            $objectData['type'] = (string) $coupon->type;
            $objectData['status'] = (string) $coupon->status;
            $dataInArray[] = $objectData;
        }

        return $dataInArray;
    }

    /**
     * this method return the value of attribute distantXml
     * It's only used on test
     *
     * @return string distant xml url
     */
    public function getDistantUrl()
    {
        return $this->distantXml;
    }

    /**
     * this function set default configuration parameters
     */
    protected function setDefaultConfiguration()
    {
        parent::setDefaultConfiguration();
        $this->configuration['secretKey'] = '';
    }

    /**
     * this method add a valid token to distant url
     *
     */
    protected function prepareDistantUrl()
    {
        $valideToken = hash('sha512', $this->configuration['secretKey'] . date('Y-m-d'));
        $this->distantXml .= '/' . $valideToken;
    }
}
