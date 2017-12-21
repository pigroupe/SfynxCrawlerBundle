<?php
namespace Sfynx\CrawlerBundle\Crawler\Transformer\JsonToCsv;

use Symfony\Component\Filesystem\Filesystem;

class Temp
{
    /**
     * @var String
     */
    protected $prefix;
    /**
     * @var \SplFileInfo[]
     */
    protected $files = [];
    /**
     *
     * If temp folder needs to be deterministic, you can use ID as the last part of folder name
     *
     * @var string
     */
    protected $id = "";

    public function __construct($prefix = '')
    {
        $this->prefix = $prefix;
        $this->id = uniqid("run-", true);
        $this->filesystem = new Filesystem();
    }

    public function initRunFolder()
    {
        clearstatcache();
        if (!file_exists($this->getTmpPath()) && !is_dir($this->getTmpPath())) {
            $this->filesystem->mkdir($this->getTmpPath(), 0777, true);
        }
    }
    /**
     * Get path to temp directory
     *
     * @return string
     */
    protected function getTmpPath()
    {
        $tmpDir = sys_get_temp_dir();
        if (!empty($this->prefix)) {
            $tmpDir .= "/" . $this->prefix;
        }
        $tmpDir .= "/" . $this->id;
        return $tmpDir;
    }

    /**
     * Returns path to temp folder for current request
     *
     * @return string
     */
    public function getTmpFolder()
    {
        return $this->getTmpPath();
    }

    /**
     * Create empty file in TMP directory
     *
     * @param string $suffix filename suffix
     * @throws \Exception
     * @return \SplFileInfo
     */
    public function createTmpFile($suffix = null)
    {
        $file = uniqid();
        if ($suffix) {
            $file .= '-' . $suffix;
        }
        return $this->createFile($file);
    }

    /**
     * Creates named temporary file
     *
     * @param $fileName
     * @return \SplFileInfo
     * @throws \Exception
     */
    public function createFile($fileName)
    {
        $this->initRunFolder();
        $fileInfo = new \SplFileInfo($this->getTmpPath() . '/' . $fileName);
        $pathName = $fileInfo->getPathname();
        if (!file_exists(dirname($pathName))) {
            $this->filesystem->mkdir(dirname($pathName), 0777, true);
        }
        $this->filesystem->touch($pathName);
        $this->files[] = array(
            'file'  => $fileInfo
        );
        $this->filesystem->chmod($pathName, 0600);
        return $fileInfo;
    }

    /**
     * Set temp id
     *
     * @param $id
     */
    public function setId($id) {
        $this->id = $id;
    }

    /**
     * Delete all files created by syrup component run
     */
    function __destruct()
    {
        try {
            foreach ($this->files as $file) {
                if (file_exists($file['file']) && is_file($file['file'])) {
                    $this->filesystem->remove($file['file']->getPathname());
                }
            }
            $this->filesystem->remove($this->getTmpPath());
        } catch (\Exception $e) {
            // Graceful destructor, does not throw any errors.
            // Fixes issues when deleting files on a server that is just shutting down.
            // https://github.com/keboola/docker-bundle/issues/215
        }
    }
}