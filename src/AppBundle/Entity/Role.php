<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\Role\RoleInterface;

/**
 * Role
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="AppBundle\Repository\RoleRepository")
 * @ORM\HasLifecycleCallbacks()
 *
 */

// ...

class Role extends Model implements RoleInterface
{
    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, unique=true)
     */
    protected $name;




    /**
     * Set name
     *
     * @param string $name
     *
     */
    public function setName($name)
    {
        $this->name = $name;
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

    public function getRole()
    {
        return $this->name;
    }
}
