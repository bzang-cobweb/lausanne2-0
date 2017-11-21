<?php

namespace AppBundle\Entity;

use AppBundle\Utility\UploadUtility;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;


/**
 * Match
 *
 * @ORM\Table(name="matches")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\MatchRepository")
 */
class Match extends Model
{
    /**
     * @var string
     *
     * @ORM\Column(name="season", type="string", length=255)
     */
    protected $season;

    /**
     * @var string
     *
     * @ORM\Column(name="stage", type="integer", nullable=true)
     */
    protected $stage;

    /**
     * @var string
     *
     * @ORM\Column(name="place", type="string", length=255)
     */
    protected $place;
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="scheduledAt", type="datetime")
     */
    protected $scheduledAt;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    protected $description;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="author_id", referencedColumnName="id")
     */
    protected $author;

    /**
     * @ORM\ManyToOne(targetEntity="Team", inversedBy="homeMatches"))
     * @ORM\JoinColumn(name="home_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $home;

    /**
     * @ORM\ManyToOne(targetEntity="Team", inversedBy="awayMatches")
     * @ORM\JoinColumn(name="visitor_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $visitor;

    /**
     * @ORM\OneToMany(targetEntity="MatchPlayer", mappedBy="match")
     */
    protected $matchPlayers;

    /**
     * @ORM\OneToOne(targetEntity="Result", mappedBy="match", cascade={"persist"})
     */
    protected $result;

    /**
     * @ORM\ManyToOne(targetEntity="Championship", inversedBy="matches"))
     * @ORM\JoinColumn(name="championship_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $championship;

    /**
     * @ORM\OneToMany(targetEntity="Picture", mappedBy="match")
     */
    protected $pictures;




    /**
     * Match constructor.
     * @param array $properties
     */
    public function __construct(array $properties = array())
    {
        $this->matchPlayers = new ArrayCollection();
        $this->pictures = new ArrayCollection();
        parent::__construct($properties);
    }


    /**
     * Set scheduedAt
     *
     * @param \DateTime $scheduledAt
     *
     * @return Match
     */
    public function setScheduledAt($scheduledAt)
    {
        $this->scheduledAt = $scheduledAt;

        return $this;
    }

    /**
     * @return string
     */
    public function getPlace()
    {
        return $this->place;
    }

    /**
     * @param string $place
     */
    public function setPlace($place)
    {
        $this->place = $place;
    }

    /**
     * Get scheduledAt
     *
     * @return \DateTime
     */
    public function getScheduledAt()
    {
        return $this->scheduledAt;
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
    public function setAuthor(User $author)
    {
        $this->author = $author;
    }


    /**
     * @return Result
     */
    public function getResult()
    {
        return $this->result;
    }


    /**
     * @param Result $result
     */
    public function setResult(Result $result)
    {
        $result->setMatch($this);
        $this->result = $result;
    }

    /**
     * @return Team
     */
    public function getHome()
    {
        return $this->home;
    }

    /**
     * @param Team $home
     */
    public function setHome(Team $home)
    {
        $this->home = $home;
    }

    /**
     * @return Team
     */
    public function getVisitor()
    {
        return $this->visitor;
    }

    /**
     * @param Team $visitor
     */
    public function setVisitor(Team $visitor)
    {
        $this->visitor = $visitor;
    }

    /**
     * @return string
     */
    public function getSeason()
    {
        return $this->season;
    }

    /**
     * @param string $season
     */
    public function setSeason($season)
    {
        $this->season = $season;
    }

    /**
     * @return string
     */
    public function getStage()
    {
        return $this->stage;
    }

    /**
     * @param string $stage
     */
    public function setStage($stage)
    {
        $this->stage = $stage;
    }

    /**
     * @return Championship
     */
    public function getChampionship()
    {
        return $this->championship;
    }

    /**
     * @param Championship $championship
     */
    public function setChampionship($championship)
    {
        $this->championship = $championship;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return ArrayCollection
     */
    public function getMatchPlayers()
    {
        return $this->matchPlayers;
    }

    /**
     * @param mixed $matchPlayers
     */
    public function setMatchPlayers($matchPlayers)
    {
        $this->matchPlayers = $matchPlayers;
    }

    /**
     * @return ArrayCollection
     */
    public function getHomePlayers()
    {
        $players = new ArrayCollection();
        foreach ($this->matchPlayers as $matchPlayer){
            if($matchPlayer instanceof MatchPlayer){
                $team = $matchPlayer->getTeam();
                if($team instanceof Team){
                    if($team->isEqual($this->home)) {
                        $players->add($matchPlayer);
                    }
                }
            }
        }

        return $players;
    }

    /**
     * @return ArrayCollection
     */
    public function getVisitorPlayers()
    {
        $players = new ArrayCollection();
        foreach ($this->matchPlayers as $matchPlayer){
            if($matchPlayer instanceof MatchPlayer){
                $team = $matchPlayer->getTeam();
                if($team instanceof Team){
                    if($team->isEqual($this->visitor)) {
                        $players->add($matchPlayer);
                    }
                }
            }
        }

        return $players;
    }

    /**
     * @return ArrayCollection
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

    public function getName(){
        return $this->__toString();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->home . ' - ' . $this->visitor;
    }
}