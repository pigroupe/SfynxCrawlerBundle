<?php
namespace Sfynx\CrawlerBundle\Crawler\Transformer;

use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;

use Sfynx\CrawlerBundle\Crawler\Exception\ExceptionTransformerCrawler;
use Sfynx\CrawlerBundle\Crawler\Transformer\JsonToCsv\Parser;
use Sfynx\CrawlerBundle\Crawler\Transformer\JsonToCsv\Analyzer;
use Psr\Log\NullLogger;
use Sfynx\CrawlerBundle\Crawler\Transformer\JsonToCsv\Structure;

/**
 * Class Doctrine2OtherTransformer
 * @category   Sfynx\CrawlerBundle\Layers
 * @package    Crawler
 * @subpackage Transformer
 */
class Doctrine2OtherTransformer
{
    /** @var string */
    const EXPORT_FORMAT_JSON = 'json';
    /** @var string */
    const EXPORT_FORMAT_XML = 'xml';
    /** @var string */
    const EXPORT_FORMAT_CSV = 'csv';

    /** @var Serializer */
    protected $serializer;
    /** @var \StdClass */
    protected $result;
    /** @var  string */
    protected $content;
    /** @var  string */
    protected $contentType;

    /**
     * List of concrete $responseException that can be built using this factory.
     * @var string[]
     */
    protected static $exportMethode = [
        self::EXPORT_FORMAT_JSON => 'exportToJson',
        self::EXPORT_FORMAT_XML => 'exportToXml',
        self::EXPORT_FORMAT_CSV => 'exportToCsv',
    ];

    protected static $exportcontentType = [
        self::EXPORT_FORMAT_JSON => 'application/json',
        self::EXPORT_FORMAT_XML => 'application/xml',
        self::EXPORT_FORMAT_CSV => 'text/csv',
    ];

    public function __construct()
    {
        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
        $normalizer = new ObjectNormalizer($classMetadataFactory, new CamelCaseToSnakeCaseNameConverter());
        $normalizer->setCircularReferenceLimit(1);
        $normalizer->setIgnoredAttributes(['__initializer__', '__cloner__', '__isInitialized__']);
        $normalizer->setCircularReferenceHandler(function ($object) {
            // @todo A cleaner solution need.
            try {
                $return = $object->getId();
            } catch (\Error $exception) {
                $return = null;
            }
            $return = null;
            return $return;
        });
        $normalizers = [$normalizer];
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $this->serializer = new Serializer($normalizers, $encoders);
        $this->result = new \StdClass();
    }

    /**
     * @param $dataObject
     * @param $format
     * @return Doctrine2OtherTransformer
     */
    public static function export($dataObject, $format): Doctrine2OtherTransformer
    {
        try {
            $method = self::$exportMethode["$format"];
            $contentType = self::$exportcontentType["$format"];
        } catch (\Exception $e) {
            throw new ExceptionTransformerCrawler('The extension export does nit existed !');
        }
        $NewInstance = new self();

        return $NewInstance
            ->setContent($NewInstance->$method($dataObject, $format))
            ->setContentType($contentType)
            ;
    }

    /**
     * @param $dataObject
     * @param $format
     * @return \Symfony\Component\Serializer\Encoder\scalar
     */
    public function exportToJson($dataObject, $format)
    {
        return $this->serializer->serialize($dataObject, 'json');
    }

    /**
     * @param $dataObject
     * @return \Symfony\Component\Serializer\Encoder\scalar
     */
    public function exportToXml($dataObject)
    {
        return $this->serializer->serialize($dataObject, 'xml');
    }

    /**
     * @param $dataObject
     * @return string
     */
    public function exportToCsv($dataObject)
    {
        $jsonTransform = $this->exportToJson($dataObject, 'json');
        $analyser = new Analyzer(new NullLogger(), new Structure());
        $parser = new Parser($analyser);
        $parser->process(json_decode($jsonTransform, true), 'metadata');

        return $parser->getCsvFiles(); // array of CsvFile objects;
    }

    /**
     * getContent
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * setContent
     *
     * @param string $content
     * @return Doctrine2OtherTransformer
     */
    public function setContent($content): Doctrine2OtherTransformer
    {
        $this->content = $content;
        return $this;
    }

    /**
     * getContentType
     *
     * @return string
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * setContentType
     *
     * @param string $contentType
     * @return Doctrine2OtherTransformer
     */
    public function setContentType($contentType): Doctrine2OtherTransformer
    {
        $this->contentType = $contentType;
        return $this;
    }
}
