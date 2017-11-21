<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Picture
 *
 * @ORM\Table(name="picture")
 *
 * @ORM\Entity(repositoryClass="AppBundle\Repository\PictureRepository")
 */
class Picture extends Model
{
    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(name="pathname", type="string", length=255)
     */
    protected $pathname;

    /**
     * @var string
     *
     * @ORM\Column(name="real_pathname", type="string", length=255)
     */
    protected $realPathname;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255, nullable=true)
     */
    protected $title;

    /**
     * @var string
     *
     * @ORM\Column(name="alt", type="string", length=255, nullable=true)
     */
    protected $alt;

    /**
     * @var string
     *
     * @ORM\Column(name="mime_type", type="string", length=255)
     */
    protected $mimeType;

    /**
     * @var string
     *
     * @ORM\Column(name="extension", type="string", length=255)
     */
    protected $extension;

    /**
     * @ORM\ManyToOne(targetEntity="Match", inversedBy="pictures"))
     * @ORM\JoinColumn(name="match_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $match;

    /**
     * @ORM\ManyToOne(targetEntity="News", inversedBy="pictures"))
     * @ORM\JoinColumn(name="news_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $news;

    /**
     * @ORM\ManyToOne(targetEntity="Team", inversedBy="pictures"))
     * @ORM\JoinColumn(name="team_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $team;

    /**
     * @ORM\ManyToOne(targetEntity="Player", inversedBy="pictures"))
     * @ORM\JoinColumn(name="player_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $player;

    /**
     * @var boolean
     *
     * @ORM\Column(name="cover", type="boolean", options={"default" : 0})
     */
    protected $cover = false;

    /**
     * @var UploadedFile
     */
    protected $file;

    /** @var array  */
    public static $acceptExtensions = ['jpeg', '.jpg', '.png'];




    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getPathname()
    {
        return $this->pathname;
    }

    /**
     * @param string $pathname
     */
    public function setPathname($pathname)
    {
        $this->pathname = $pathname;
    }

    /**
     * @return string
     */
    public function getRealPathname()
    {
        return $this->realPathname;
    }

    /**
     * @param string $realPathname
     */
    public function setRealPathname($realPathname)
    {
        $this->realPathname = $realPathname;
    }

    /**
     * @return string
     */
    public function getMimeType()
    {
        return $this->mimeType;
    }

    /**
     * @param string $mimeType
     */
    public function setMimeType($mimeType)
    {
        $this->mimeType = $mimeType;
    }

    /**
     * @return string
     */
    public function getExtension()
    {
        return $this->extension;
    }

    /**
     * @param string $extension
     */
    public function setExtension($extension)
    {
        $this->extension = $extension;
    }

    /**
     * @return Match
     */
    public function getMatch()
    {
        return $this->match;
    }

    /**
     * @param mixed $match
     */
    public function setMatch($match)
    {
        $this->match = $match;
    }

    /**
     * @return News
     */
    public function getNews()
    {
        return $this->news;
    }

    /**
     * @param mixed $news
     */
    public function setNews($news)
    {
        $this->news = $news;
    }

    /**
     * @return bool
     */
    public function getCover()
    {
        return $this->cover;
    }

    /**
     * @return bool
     */
    public function isCover()
    {
        return $this->cover;
    }

    /**
     * @param bool $cover
     */
    public function setCover($cover)
    {
        $this->cover = $cover;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getTeam()
    {
        return $this->team;
    }

    /**
     * @param mixed $team
     */
    public function setTeam($team)
    {
        $this->team = $team;
    }

    /**
     * @return mixed
     */
    public function getPlayer()
    {
        return $this->player;
    }

    /**
     * @param mixed $player
     */
    public function setPlayer($player)
    {
        $this->player = $player;
    }

    /**
     * @return UploadedFile
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param mixed $file
     */
    public function setFile($file)
    {
        $this->file = $file;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getAlt()
    {
        return $this->alt;
    }

    /**
     * @param string $alt
     */
    public function setAlt($alt)
    {
        $this->alt = $alt;
    }

    /**
     * @return bool
     */
    public function exists(){
        return file_exists($this->realPathname);
    }

    /**
     * @return bool
     */
    public function deleteFile(){
        if($this->exists()){
            return @unlink($this->realPathname);
        }
    }

    /**
     * @return string
     */
    public static function getMaxFileSize(){
        $size = UploadedFile::getMaxFilesize();
        if ($size < 1000000){
            $ko = round($size/1024,2);
            return $ko . ' KO';
        } else {
            if ($size < 1000000000){
                $mo = round($size/(1024*1024),2);
                return $mo . ' MO';
            } else {
                $go = round($size/(1024*1024*1024),2);
                return $go . ' GO';
            }
        }
    }

}
