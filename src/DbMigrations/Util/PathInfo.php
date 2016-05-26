<?php

namespace DbMigrations\Util;

use BaseExceptions\Exception\InvalidArgument\EmptyStringException;
use BaseExceptions\Exception\InvalidArgument\NotStringException;

/**
 * Class PathInfo
 * @package DbMigrations\Util
 */
class PathInfo
{
    /**
     * @var string
     */
    private $dirname;
    /**
     * @var string
     */
    private $basename;
    /**
     * @var string
     */
    private $extension;
    /**
     * @var string
     */
    private $filename;

    /**
     * PathInfo constructor.
     *
     * @param string $path
     */
    public function __construct($path)
    {
        if (!is_string($path)) {
            throw new NotStringException("path");
        }
        if ($path === "") {
            throw new EmptyStringException("path");
        }

        $pathInfo = pathinfo($path);
        $this->dirname = array_key_exists("dirname", $pathInfo) ? $pathInfo["dirname"] : null;
        $this->basename = array_key_exists("basename", $pathInfo) ? $pathInfo["basename"] : null;
        $this->extension = array_key_exists("extension", $pathInfo) ? $pathInfo["extension"] : null;
        $this->filename = array_key_exists("filename", $pathInfo) ? $pathInfo["filename"] : null;
    }

    /**
     * @return string
     */
    public function getDirname()
    {
        return $this->dirname;
    }

    /**
     * @return string
     */
    public function getBasename()
    {
        return $this->basename;
    }

    /**
     * @return string
     */
    public function getExtension()
    {
        return $this->extension;
    }

    /**
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }
}
