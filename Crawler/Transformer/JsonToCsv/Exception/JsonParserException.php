<?php
namespace Sfynx\CrawlerBundle\Crawler\Transformer\JsonToCsv\Exception;

class JsonParserException extends \Exception
{
    protected $data = [];

    public function __construct($message = "", array $data = [], $code = 0, $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->setData($data);
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }
    /**
     * @param array $data
     * @return $this
     */
    public function setData(array $data)
    {
        $this->data = $data;
        return $this;
    }
}
