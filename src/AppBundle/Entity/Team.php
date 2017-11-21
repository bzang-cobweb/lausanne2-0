<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Team
 *
 * @ORM\Table(
 *     name="team",
 *     uniqueConstraints={
 *          @ORM\UniqueConstraint(name="uniqueTeamName", columns={"name"}),
 *          @ORM\UniqueConstraint(name="uniqueTeamTrigram", columns={"trigram"})
 *      }
 * )
 * @ORM\Entity(repositoryClass="AppBundle\Repository\TeamRepository")
 */
class Team extends Model
{
    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=50)
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(name="trigram", type="string", length=50)
     */
    protected $trigram;

    /**
     * @ORM\OneToMany(targetEntity="ChampionshipTeam" , mappedBy="team", cascade={"persist"})
     */
    protected $championshipTeams;

    /**
     * @var ArrayCollection
     */
    protected $championships;

    /**
     * @ORM\OneToMany(targetEntity="TeamPlayer" , mappedBy="team", cascade={"persist"})
     */
    protected $teamPlayers;

    /**
     * @var ArrayCollection
     */
    protected $players;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    protected $description;

    /**
     * @ORM\OneToMany(targetEntity="Match", mappedBy="home", cascade={"all"})
     */
    protected $homeMatches;

    /**
     * @ORM\OneToMany(targetEntity="Match", mappedBy="visitor", cascade={"all"})
     */
    protected $awayMatches;

    /**
     * @var ArrayCollection
     */
    protected $matches;

    /**
     * @ORM\OneToMany(targetEntity="Picture", mappedBy="team")
     */
    protected $pictures;

    /** @var  $cover cover picture */
    protected $cover;




    /**
     * Team constructor.
     * @param array $properties
     */
    public function __construct(array $properties = array())
    {
        $this->championships = new ArrayCollection();
        $this->championshipTeams = new ArrayCollection();
        $this->pictures = new ArrayCollection();
        $this->players = new ArrayCollection();
        $this->teamPlayers = new ArrayCollection();
        $this->homeMatches = new ArrayCollection();
        $this->awayMatches = new ArrayCollection();
        $this->matches = new ArrayCollection();
        parent::__construct($properties);
    }



    /**
     * Set name
     *
     * @param string $name
     *
     * @return Team
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }


    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
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
     * @return ArrayCollection
     */
    public function getChampionships()
    {
        $this->championships = new ArrayCollection();
        foreach ($this->championshipTeams as $championshipTeam){
            if($championshipTeam instanceof ChampionshipTeam && !$championshipTeam->isDeleted()){
                $this->championships->add($championshipTeam->getChampionship());
            }
        }
        return $this->championships;
    }

    /**
     * @param ArrayCollection $championships
     */
    public function setChampionships($championships)
    {
        $this->championships = $championships;
        $championshipTeams = new ArrayCollection();
        foreach ($this->championships as $championship){
            if($championship instanceof Championship) {
                $championshipTeam = $this->getChampionshipTeam($championship);
                if($championshipTeam instanceof ChampionshipTeam) {
                    $championshipTeams->add($championshipTeam);
                } else {
                    $championshipTeams->add(new ChampionshipTeam([
                        'team' => $this,
                        'championship' => $championship
                    ]));
                }
            }
        }

        foreach ($this->championshipTeams as $championshipTeam){
            if($championshipTeam instanceof ChampionshipTeam){
                if(!$championshipTeams->contains($championshipTeam)){
                    $championshipTeam->setDeleted(true);
                    $championshipTeam->setUpdatedAt(new \DateTime());
                }
            }
        }

        $this->championshipTeams = $championshipTeams;
    }

    /**
     * @param Championship $championship
     * @return ChampionshipTeam|null
     */
    protected function getChampionshipTeam(Championship $championship){
        foreach ($this->championshipTeams as $championshipTeam) {
            if($championshipTeam instanceof ChampionshipTeam && !$championshipTeam->isDeleted()){
                $c = $championshipTeam->getChampionship();
                if($c->isEqual($championship)){
                    return $championshipTeam;
                }
            }
        }
        return null;
    }

    /**
     * @return mixed
     */
    public function getChampionshipTeams()
    {
        return $this->championshipTeams;
    }

    /**
     * @param mixed $championshipTeams
     */
    public function setChampionshipTeams($championshipTeams)
    {
        $this->championshipTeams = $championshipTeams;
        $this->championships = new ArrayCollection();
        foreach ($this->championshipTeams as $championshipTeam){
            if($championshipTeam instanceof ChampionshipTeam && !$championshipTeam->isDeleted()){
                $championship = $championshipTeam->getChampionship();
                if(!$championship->isDeleted()){
                    $this->championships->add($championship);
                }
            }
        }
    }

    /**
     * @return ArrayCollection
     */
    public function getPlayers()
    {
        $this->players = new ArrayCollection();
        foreach ($this->teamPlayers as $teamPlayer){
            if($teamPlayer instanceof TeamPlayer && !$teamPlayer->isDeleted()){
                $this->players->add($teamPlayer->getPlayer());
            }
        }
        return $this->players;
    }

    /**
     * @param ArrayCollection $players
     */
    public function setPlayers($players)
    {
        $this->players = $players;
        $teamPlayers = new ArrayCollection();
        foreach ($this->players as $player){
            if($player instanceof Player) {
                $teamPlayer = $this->getTeamPlayer($player);
                if($teamPlayer instanceof TeamPlayer) {
                    $teamPlayers->add($teamPlayer);
                } else {
                    $teamPlayers->add(new TeamPlayer([
                        'team' => $this,
                        'player' => $player
                    ]));
                }
            }
        }

        foreach ($this->teamPlayers as $teamPlayer){
            if($teamPlayer instanceof TeamPlayer){
                if(!$teamPlayers->contains($teamPlayer)){
                    $teamPlayer->setDeleted(true);
                    $teamPlayer->setUpdatedAt(new \DateTime());
                }
            }
        }

        $this->teamPlayers = $teamPlayers;
    }

    /**
     * @param Player $player
     * @return TeamPlayer|null
     */
    protected function getTeamPlayer(Player $player){
        foreach ($this->teamPlayers as $teamPlayer) {
            if($teamPlayer instanceof TeamPlayer && !$teamPlayer->isDeleted()){
                $p = $teamPlayer->getPlayer();
                if($p->isEqual($player)){
                    return $teamPlayer;
                }
            }
        }
        return null;
    }

    /**
     * @return ArrayCollection
     */
    public function getTeamPlayers()
    {
        return $this->teamPlayers;
    }

    /**
     * @param ArrayCollection $teamPlayers
     */
    public function setTeamPlayers($teamPlayers)
    {
        $this->teamPlayers = $teamPlayers;
        $this->players = new ArrayCollection();
        foreach ($this->teamPlayers as $teamPlayer){
            if($teamPlayer instanceof TeamPlayer && !$teamPlayer->isDeleted()){
                $player = $teamPlayer->getPlayer();
                if(!$player->isDeleted()){
                    $this->players->add($player);
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
            foreach ($this->championshipTeams as $championshipTeam) {
                if ($championshipTeam instanceof ChampionshipTeam && !$championshipTeam->isDeleted()) {
                    $championshipTeam->setDeleted($deleted);
                    $championshipTeam->setUpdatedAt(new \DateTime());
                }
            }

            foreach ($this->teamPlayers as $teamPlayer) {
                if ($teamPlayer instanceof TeamPlayer && !$teamPlayer->isDeleted()) {
                    $teamPlayer->setDeleted($deleted);
                    $teamPlayer->setUpdatedAt(new \DateTime());
                }
            }
        }
        return parent::setDeleted($deleted);
    }


    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return ArrayCollection
     */
    public function getHomeMatches()
    {
        return $this->homeMatches;
    }

    /**
     * @param ArrayCollection $homeMatches
     */
    public function setHomeMatches($homeMatches)
    {
        $this->homeMatches = $homeMatches;
    }

    /**
     * @return ArrayCollection
     */
    public function getAwayMatches()
    {
        return $this->awayMatches;
    }

    /**
     * @param ArrayCollection $awayMatches
     */
    public function setAwayMatches($awayMatches)
    {
        $this->awayMatches = $awayMatches;
    }


    /**
     * @return ArrayCollection
     */
    public function getMatches()
    {
        $this->matches = $this->homeMatches;
        foreach($this->awayMatches as $match){
            if($match instanceof Match){
                $this->matches->add($match);
            }
        }
        return $this->matches;
    }

    /**
     * @return mixed
     */
    public function getPictures()
    {
        return $this->pictures;
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
     * @param mixed $pictures
     */
    public function setPictures($pictures)
    {
        $this->pictures = $pictures;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return ucfirst($this->name);
    }
}
