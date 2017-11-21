<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Player
 *
 * @ORM\Table(
 *     name="player",
 *     uniqueConstraints={
 *          @ORM\UniqueConstraint(name="uniquePlayerTrigram", columns={"trigram"})
 *     }
 * )
 * @ORM\Entity(repositoryClass="AppBundle\Repository\PlayerRepository")
 */
class Player extends Model
{
    /**
     * @var string
     *
     * @ORM\Column(name="first_name", type="string", length=255)
     */
    protected $firstname;

    /**
     * @var string
     *
     * @ORM\Column(name="last_name", type="string", length=255)
     */
    protected $lastname;

    /**
     * @var string
     *
     * @ORM\Column(name="trigram", type="string", length=50)
     */
    protected $trigram;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    protected $description;

    /**
     * @ORM\OneToMany(targetEntity="TeamPlayer" , mappedBy="player", cascade={"persist"})
     */
    protected $playerTeams;

    /**
     * @ORM\OneToMany(targetEntity="MatchPlayer" , mappedBy="player")
     */
    protected $playerMatches;

    /**
     * @var ArrayCollection
     */
    protected $teams;

    /**
     * @ORM\OneToMany(targetEntity="Picture", mappedBy="player")
     */
    protected $pictures;

    /** @var  $cover cover picture */
    protected $cover;




    /**
     * Player constructor.
     * @param array $properties
     */
    public function __construct(array $properties = array())
    {
        $this->teams = new ArrayCollection();
        $this->playerTeams = new ArrayCollection();
        $this->playerMatches = new ArrayCollection();
        $this->pictures = new ArrayCollection();
        parent::__construct($properties);
    }


    /**
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * @param string $firstname
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;
    }

    /**
     * @return string
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * @param string $lastname
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;
    }

    /**
     * @return string
     */
    public function getTrigram()
    {
        return $this->trigram;
    }

    /**
     * @param string $trigram
     */
    public function setTrigram($trigram)
    {
        $this->trigram = $trigram;
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
    public function getTeams()
    {
        $this->teams = new ArrayCollection();
        foreach ($this->playerTeams as $teamPlayer){
            if($teamPlayer instanceof TeamPlayer && !$teamPlayer->isDeleted()){
                $this->teams->add($teamPlayer->getTeam());
            }
        }
        return $this->teams;
    }

    /**
     * @param ArrayCollection $teams
     */
    public function setTeams($teams)
    {
        $this->teams = $teams;
        $playerTeams = new ArrayCollection();
        foreach ($this->teams as $team){
            if($team instanceof Team) {
                $teamPlayer = $this->getTeamPlayer($team);
                if($teamPlayer instanceof TeamPlayer) {
                    $playerTeams->add($teamPlayer);
                } else {
                    $playerTeams->add(new TeamPlayer([
                        'team' => $team,
                        'player' => $this
                    ]));
                }
            }
        }

        foreach ($this->playerTeams as $teamPlayer){
            if($teamPlayer instanceof TeamPlayer){
                if(!$playerTeams->contains($teamPlayer)){
                    $teamPlayer->setDeleted(true);
                    $teamPlayer->setUpdatedAt(new \DateTime());
                }
            }
        }

        $this->playerTeams = $playerTeams;
    }

    /**
     * @param Team $team
     * @return TeamPlayer|null
     */
    protected function getTeamPlayer(Team $team){
        foreach ($this->playerTeams as $teamPlayer) {
            if($teamPlayer instanceof TeamPlayer && !$teamPlayer->isDeleted()){
                $t = $teamPlayer->getTeam();
                if($t->isEqual($team)){
                    return $teamPlayer;
                }
            }
        }
        return null;
    }

    /**
     * @return ArrayCollection
     */
    public function getPlayerTeams()
    {
        return $this->playerTeams;
    }

    /**
     * @param ArrayCollection $playerTeams
     */
    public function setPlayerTeams($playerTeams)
    {
        $this->playerTeams = $playerTeams;
        $this->teams = new ArrayCollection();
        foreach ($this->playerTeams as $teamPlayer){
            if($teamPlayer instanceof TeamPlayer && !$teamPlayer->isDeleted()){
                $team = $teamPlayer->getTeam();
                if(!$team->isDeleted()){
                    $this->teams->add($team);
                }
            }
        }
    }

    /**
     * @param bool $deleted
     * @return Team
     */
    public function setDeleted($deleted)
    {
        if($deleted) {
            foreach ($this->playerTeams as $teamPlayer) {
                if ($teamPlayer instanceof TeamPlayer && !$teamPlayer->isDeleted()) {
                    $teamPlayer->setDeleted($deleted);
                    $teamPlayer->setUpdatedAt(new \DateTime());
                }
            }
        }
        return parent::setDeleted($deleted);
    }

    /**
     * @return ArrayCollection
     */
    public function getPlayerMatches()
    {
        return $this->playerMatches;
    }

    /**
     * @param mixed $playerMatches
     */
    public function setPlayerMatches($playerMatches)
    {
        $this->playerMatches = $playerMatches;
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
        return ucwords($this->firstname) . ' ' . ucwords($this->lastname);
    }
}
