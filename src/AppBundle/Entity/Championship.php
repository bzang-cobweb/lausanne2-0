<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;


/**
 * Championship
 *
 * @ORM\Table(
 *     name="championship",
 *     uniqueConstraints={
 *          @ORM\UniqueConstraint(name="uniqueChampionshipName", columns={"name"})
 *     }
 * )
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ChampionshipRepository")
 */
class Championship extends Model
{
    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    protected $name;

    /**
     * @var boolean
     *
     * @ORM\Column(name="highlighted", type="boolean", options={"default" : 0})
     */
    protected $highlighted = false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="cup", type="boolean", options={"default" : 0})
     */
    protected $cup = false;

    /**
     * @ORM\OneToMany(targetEntity="ChampionshipTeam", mappedBy="championship", cascade={"persist"})
     */
    protected $championshipTeams;

    /**
     * @var ArrayCollection
     */
    protected $teams;

    /**
     * @ORM\OneToMany(targetEntity="Match", mappedBy="championship")
     * @ORM\OrderBy({"scheduledAt" = "DESC"})
     */
    protected $matches;

    /**
     * @ORM\OneToMany(targetEntity="News", mappedBy="championship")
     */
    protected $news;





    /**
     * Championship constructor.
     * @param array $properties
     */
    public function __construct(array $properties = array())
    {
        $this->championshipTeams = new ArrayCollection();
        $this->teams = new ArrayCollection();
        $this->matches = new ArrayCollection();
        $this->news = new ArrayCollection();
        parent::__construct($properties);
    }

    /**
     * Return the lastest 5 matches for this championship
     * @param int $max
     * @return ArrayCollection
     */
    public function getLatestMatches($max = 5){
        $matches = new ArrayCollection();
        $now = new \DateTime();
        $count = 0;
        foreach ($this->matches as $match){
            if($match instanceof Match &&
                $match->getScheduledAt() < $now &&
                $match->getResult() instanceof Result){
                $matches->add($match);
                $count++;
            }
            if($count == $max){
                break;
            }
        }
        return $matches;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param $highlighted
     * @return $this
     */
    public function setHighlighted($highlighted)
    {
        $this->highlighted = $highlighted;

        return $this;
    }

    /**
     * @return bool
     */
    public function getHighlighted()
    {
        return $this->highlighted;
    }

    /**
     * @return bool
     */
    public function getCup()
    {
        return $this->cup;
    }

    /**
     * @return bool
     */
    public function isCup()
    {
        return $this->cup;
    }

    /**
     * @param bool $cup
     */
    public function setCup($cup)
    {
        $this->cup = $cup;
    }

    /**
     * @return bool
     */
    public function isHighlighted()
    {
        return $this->highlighted;
    }

    /**
     * @return ArrayCollection
     */
    public function getTeams()
    {
        $this->teams = new ArrayCollection();
        foreach ($this->championshipTeams as $championshipTeam){
            if($championshipTeam instanceof ChampionshipTeam && !$championshipTeam->isDeleted()){
                $this->teams->add($championshipTeam->getTeam());
            }
        }
        return $this->teams;
    }

    /**
     * @param ArrayCollection $teams
     */
    public function setTeams(ArrayCollection $teams)
    {
        $this->teams = $teams;
        $championshipTeams = new ArrayCollection();
        foreach ($this->teams as $team){
            if($team instanceof Team) {
                $championshipTeam = $this->getChampionshipTeam($team);
                if($championshipTeam instanceof ChampionshipTeam) {
                    $championshipTeams->add($championshipTeam);
                } else {
                    $championshipTeams->add(new ChampionshipTeam([
                        'team' => $team,
                        'championship' => $this
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
     * @param Team $team
     * @return ChampionshipTeam|null
     */
    protected function getChampionshipTeam(Team $team){
        foreach ($this->championshipTeams as $championshipTeam) {
            if($championshipTeam instanceof ChampionshipTeam && !$championshipTeam->isDeleted()){
                $t = $championshipTeam->getTeam();
                if($t->isEqual($team)){
                    return $championshipTeam;
                }
            }
        }
        return null;
    }

    /**
     * @return ArrayCollection
     */
    public function getChampionshipTeams()
    {
        return $this->championshipTeams;
    }

    /**
     * @param ArrayCollection $championshipTeams
     */
    public function setChampionshipTeams($championshipTeams)
    {
        $this->championshipTeams = $championshipTeams;
        $this->teams = new ArrayCollection();
        foreach ($this->championshipTeams as $championshipTeam){
            if($championshipTeam instanceof ChampionshipTeam && !$championshipTeam->isDeleted()){
                $team = $championshipTeam->getTeam();
                if(!$team->isDeleted()){
                    $this->teams->add($team);
                }
            }
        }
    }

    /**
     * @param bool $deleted
     * @return Championship
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
        }
        return parent::setDeleted($deleted);
    }



    /**
     * @return mixed
     */
    public function getMatches()
    {
        return $this->matches;
    }

    /**
     * @param mixed $macthes
     */
    public function setMatches($macthes)
    {
        $this->matches = $macthes;
    }

    /**
     * @param Match $match
     */
    public function addMatch(Match $match){
        $this->team->add($match);
    }

    /**
     * @param Match $match
     */
    public function removeMatch(Match $match){
        $this->matches->removeElement($match);
    }

    /**
     * @return ArrayCollection
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
     * @return string
     */
    public function __toString()
    {
        return ucfirst($this->name);
    }
}
