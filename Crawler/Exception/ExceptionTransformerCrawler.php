<?php
namespace Sfynx\CrawlerBundle\Crawler\Exception;

use Symfony\Component\HttpFoundation\Response;

/**
 * Exception class for XmlCrawler class
 *
 * @category   Sfynx\MediaBundle
 * @package    Crawler
 * @subpackage Exception
 *
 * @author     Etienne de Longeaux <etienne.delongeaux@gmail.com>
 * @copyright  2015 PI-GROUPE
 * @license    http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version    2.3
 * @link       http://opensource.org/licenses/gpl-license.php
 * @since      2015-02-16
 */
class ExceptionTransformerCrawler extends \Exception
{
    /**
     * @var array Array of data to describe errors
     */
    protected $data;

    /**
     * @param string $message
     * @param array $data
     * @param Exception|null $previous
     */
    public function __construct($message = '', array $data = [], Exception $previous = null)
    {
        parent::__construct($message, Response::HTTP_INTERNAL_SERVER_ERROR, $previous);
        $this->data = $data;
    }
}
