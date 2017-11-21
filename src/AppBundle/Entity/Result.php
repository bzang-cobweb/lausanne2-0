<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Result
 *
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ResultRepository")
 * @ORM\Table(name="result")
 *
 */





class Result extends Model
{
    /**
     * @var integer
     *
     * @ORM\Column(name="home_goal", type="integer")
     */
    protected $homeGoal = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="visitor_goal", type="integer")
     */
    protected $visitorGoal = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="home_yellow_card", type="integer")
     */
    protected $homeYellowCard = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="visitor_yellow_card", type="integer")
     */
    protected $visitorYellowCard = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="home_red_card", type="integer")
     */
    protected $homeRedCard = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="visitor_red_card", type="integer")
     */
    protected $visitorRedCard = 0;

    /**
     * @ORM\OneToOne(targetEntity="Match", inversedBy="result")
     * @ORM\JoinColumn(name="match_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $match;




    /**
     * @return Match
     */
    public function getMatch()
    {
        return $this->match;
    }

    /**
     * @param Match $match
     */
    public function setMatch(Match $match)
    {
        $this->match = $match;
    }

    /**
     * @return int
     */
    public function getHomeGoal()
    {
        return $this->homeGoal;
    }

    /**
     * @param int $homeGoal
     */
    public function setHomeGoal($homeGoal)
    {
        $this->homeGoal = $homeGoal;
    }

    /**
     * @return int
     */
    public function getVisitorGoal()
    {
        return $this->visitorGoal;
    }

    /**
     * @param int $visitorGoal
     */
    public function setVisitorGoal($visitorGoal)
    {
        $this->visitorGoal = $visitorGoal;
    }

    /**
     * @return mixed
     */
    public function getHomeYellowCard()
    {
        return $this->homeYellowCard;
    }

    /**
     * @param mixed $homeYellowCard
     */
    public function setHomeYellowCard($homeYellowCard)
    {
        $this->homeYellowCard = $homeYellowCard;
    }

    /**
     * @return int
     */
    public function getVisitorYellowCard()
    {
        return $this->visitorYellowCard;
    }

    /**
     * @param int $visitorYellowCard
     */
    public function setVisitorYellowCard($visitorYellowCard)
    {
        $this->visitorYellowCard = $visitorYellowCard;
    }

    /**
     * @return mixed
     */
    public function getHomeRedCard()
    {
        return $this->homeRedCard;
    }

    /**
     * @param mixed $homeRedCard
     */
    public function setHomeRedCard($homeRedCard)
    {
        $this->homeRedCard = $homeRedCard;
    }

    /**
     * @return int
     */
    public function getVisitorRedCard()
    {
        return $this->visitorRedCard;
    }

    /**
     * @param int $visitorRedCard
     */
    public function setVisitorRedCard($visitorRedCard)
    {
        $this->visitorRedCard = $visitorRedCard;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->homeGoal . ' - ' . $this->visitorGoal;
    }
}
