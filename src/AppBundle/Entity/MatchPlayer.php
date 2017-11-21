<?php

namespace AppBundle\Entity;

use AppBundle\Form\Type\PlayerType;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * MatchPlayer
 *
 * @ORM\Entity(repositoryClass="AppBundle\Repository\MatchPlayerRepository")
 * @ORM\Table(name="match_player")
 * @UniqueEntity(fields={"match", "player"})
 *
 */



class MatchPlayer extends Model
{
    /**
     * @ORM\ManyToOne(targetEntity="Match", inversedBy="matchPlayers")
     * @ORM\JoinColumn(name="match_id", referencedColumnName="id")
     */
    protected $match;

    /**
     * @ORM\ManyToOne(targetEntity="Team")
     * @ORM\JoinColumn(name="team_id", referencedColumnName="id")
     *
     */
    protected $team;

    /**
     * @ORM\ManyToOne(targetEntity="Player", inversedBy="playerMatches")
     * @ORM\JoinColumn(name="player_id", referencedColumnName="id")
     *
     */
    protected $player;

    /**
     * @var integer
     *
     * @ORM\Column(name="goal", type="integer")
     */
    protected $goal = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="auto_goal", type="integer")
     */
    protected $autoGoal = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="yellow_card", type="integer")
     */
    protected $yellowCard = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="red_card", type="integer")
     */
    protected $redCard = 0;





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
     * @return Team
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
     * @return Player
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
     * @return int
     */
    public function getGoal()
    {
        return $this->goal;
    }

    /**
     * @param int $goal
     */
    public function setGoal($goal)
    {
        $this->goal = $goal;
    }

    /**
     * @return int
     */
    public function getAutoGoal()
    {
        return $this->autoGoal;
    }

    /**
     * @param int $autoGoal
     */
    public function setAutoGoal($autoGoal)
    {
        $this->autoGoal = $autoGoal;
    }

    /**
     * @return int
     */
    public function getYellowCard()
    {
        return $this->yellowCard;
    }

    /**
     * @param int $yellowCard
     */
    public function setYellowCard($yellowCard)
    {
        $this->yellowCard = $yellowCard;
    }

    /**
     * @return int
     */
    public function getRedCard()
    {
        return $this->redCard;
    }

    /**
     * @param int $redCard
     */
    public function setRedCard($redCard)
    {
        $this->redCard = $redCard;
    }

    /**
     * @return ArrayCollection
     */
    public function getTeams(){
        $teams = new ArrayCollection();
        if($this->match instanceof Match){
            if($this->match->getHome() instanceof Team){
                $teams->add($this->match->getHome());
            }

            if($this->match->getVisitor() instanceof Team){
                $teams->add($this->match->getVisitor());
            }
        }
        return $teams;
    }
}