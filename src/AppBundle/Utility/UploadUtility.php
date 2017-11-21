<?php
/**
 * Created by PhpStorm.
 * User: bzang
 * Date: 04/02/16
 * Time: 22:10
 */

namespace AppBundle\Utility;



use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\File\File;

class UploadUtility
{

    /**
     * @var ArrayCollection
     */
    protected $files;

    /**
     * @var int
     */
    protected $maxFiles = 1;

    /**
     * @var string
     */
    protected $directory;

    /**
     * @var array
     */
    protected $allowedExtensions;


    public function __construct(){
        $this->files = new ArrayCollection();
    }

    /**
     * @return ArrayCollection
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * @param ArrayCollection $files
     */
    public function setFiles($files)
    {
        $this->files = $files;
    }

    /**
     * @return int
     */
    public function getMaxFiles()
    {
        return $this->maxFiles;
    }

    /**
     * @param int $maxFiles
     */
    public function setMaxFiles($maxFiles)
    {
        $this->maxFiles = $maxFiles;
        $this->files->add(new File(''));
    }

    /**
     * @return string
     */
    public function getDirectory()
    {
        return $this->directory;
    }

    /**
     * @param string $directory
     */
    public function setDirectory($directory)
    {
        $this->directory = $directory;
    }

    /**
     * @return array
     */
    public function getAllowedExtensions()
    {
        return $this->allowedExtensions;
    }

    /**
     * @param array $allowedExtensions
     */
    public function setAllowedExtensions($allowedExtensions)
    {
        $this->allowedExtensions = $allowedExtensions;
    }
}