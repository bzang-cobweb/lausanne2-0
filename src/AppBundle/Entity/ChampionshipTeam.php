<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * ChampionshipTeam
 *
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ChampionshipTeamRepository")
 * @ORM\Table(name="championship_team")
 * @UniqueEntity(fields={"team", "championship"})
 *
 */



class ChampionshipTeam extends Model
{
    /**
     * @ORM\ManyToOne(targetEntity="Team" , inversedBy="championshipTeams")
     * @ORM\JoinColumn(name="team_id", referencedColumnName="id")
     */
    protected $team;

    /**
     * @ORM\ManyToOne(targetEntity="Championship", inversedBy="championshipTeams")
     * @ORM\JoinColumn(name="championship_id", referencedColumnName="id")
     *
     */
    protected $championship;



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
     * @return string
     */
    public function getChampionshipName(){
        return '' . $this->championship;
    }
}