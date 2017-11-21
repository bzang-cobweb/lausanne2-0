<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * TeamPlayer
 *
 * @ORM\Entity(repositoryClass="AppBundle\Repository\TeamPlayerRepository")
 * @ORM\Table(name="team_player")
 * @UniqueEntity(fields={"team", "player"})
 *
 */



class TeamPlayer extends Model
{
    /**
     * @ORM\ManyToOne(targetEntity="Team" , inversedBy="teamPlayers")
     * @ORM\JoinColumn(name="team_id", referencedColumnName="id")
     */
    protected $team;

    /**
     * @ORM\ManyToOne(targetEntity="Player", inversedBy="playerTeams")
     * @ORM\JoinColumn(name="player_id", referencedColumnName="id")
     *
     */
    protected $player;



    /**
     * @return Team
     */
    public function getTeam()
    {
        return $this->team;
    }

    /**
     * @param team $team
     */
    public function setTeam(Team $team)
    {
        $this->team = $team;
    }

    /**
     * @return string
     */
    public function getTeamName(){
        return '' . $this->team;
    }

    /**
     * @return Player
     */
    public function getPlayer()
    {
        return $this->player;
    }

    /**
     * @param Player $player
     */
    public function setPlayer($player)
    {
        $this->player = $player;
    }

    /**
     * @return string
     */
    public function getPlayerName(){
        return '' . $this->player;
    }
}