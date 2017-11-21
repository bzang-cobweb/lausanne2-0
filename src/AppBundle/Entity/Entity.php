<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class Entity
 * @package AppBundle\Entity
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks()
 */
abstract class Entity
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="createdAt", type="datetime")
     */
    protected $createdAt;


    public function __construct(array $properties = array()){
        if(count($properties) > 0){
            $this->hydrate($properties);
        }
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
     * @return bool
     */
    public function isNew(){
        return (int) $this->id <= 0;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Player
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @ORM\PrePersist
     */
    public function setCreatedAtValue()
    {
        $this->createdAt = new \DateTime();
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param array $properties
     */
    public function hydrate(array $properties){
        foreach($properties as $property => $value){
            $method = 'set'.ucfirst($property);
            if(method_exists($this, $method)){
                $this->$method($value);
            } elseif(property_exists($this, $property)){
                $this->$property = $value;
            }
        }
    }

    /**
     * @param $entity
     * @return bool
     */
    public function isEqual($entity){
        if($entity instanceof Entity){
            if(!$entity->isNew()){
                return (int) $entity->getId() == (int) $this->getId();
            }
        }
        return false;
    }
}
