<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * News
 *
 * @ORM\Table(name="news")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\NewsRepository")
 */
class News extends Model
{
    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    protected $title;

    /**
     * @var string
     *
     * @ORM\Column(name="teaser", type="text")
     */
    protected $teaser;

    /**
     * @var string
     *
     * @ORM\Column(name="body", type="text", nullable=true)
     */
    protected $body;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="author_id", referencedColumnName="id")
     */
    protected $author;

    /**
     * @ORM\OneToMany(targetEntity="Picture", mappedBy="news")
     */
    protected $pictures;

    /**
     * @ORM\ManyToOne(targetEntity="Championship", inversedBy="news"))
     * @ORM\JoinColumn(name="championship_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $championship;

    /** @var  $cover cover picture */
    protected $cover;





    public function __construct(array $properties = array())
    {
        $this->pictures = new ArrayCollection();
        parent::__construct($properties);
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
    public function getTeaser()
    {
        return $this->teaser;
    }

    /**
     * @param string $teaser
     */
    public function setTeaser($teaser)
    {
        $this->teaser = $teaser;
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param string $body
     */
    public function setBody($body)
    {
        $this->body = $body;
    }

    /**
     * @return User
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @param User $author
     */
    public function setAuthor($author)
    {
        $this->author = $author;
    }

    /**
     * @return mixed
     */
    public function getPictures()
    {
        return $this->pictures;
    }

    /**
     * @param mixed $pictures
     */
    public function setPictures($pictures)
    {
        $this->pictures = $pictures;
    }

    /**
     * @return cover
     */
    public function getCover()
    {
        if(!$this->cover instanceof Picture){
            foreach ($this->pictures as $picture){
                if($picture instanceof Picture){
                    $this->cover = $picture;
                    if($picture->isCover()){
                        break;
                    }
                }
            }
        }
        return $this->cover;
    }

    /**
     * @return Championship
     */
    public function getChampionship()
    {
        return $this->championship;
    }

    /**
     * @param mixed $championship
     */
    public function setChampionship($championship)
    {
        $this->championship = $championship;
    }

    /**
     * @return string
     */
    public function getName(){
        return $this->__toString();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return ucwords($this->title);
    }
}
