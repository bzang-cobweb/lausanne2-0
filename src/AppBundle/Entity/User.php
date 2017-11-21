<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * User
 *
 * @ORM\Table(
 *     name="user",
 *     uniqueConstraints={
 *          @ORM\UniqueConstraint(name="uniqueUserEmail", columns={"email"})
 *      }
 * )
 * @ORM\Entity(repositoryClass="AppBundle\Repository\UserRepository")
 * @ORM\HasLifecycleCallbacks()
 *
 */

// ...

class User extends  Model implements AdvancedUserInterface, \Serializable
{
    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255)
     */
    protected $email;

    /**
     * @var string
     *
     * @ORM\Column(name="username", type="string", length=255)
     */
    protected $username;

    /**
     * @var string
     *
     * @ORM\Column(name="first_name", type="string", length=255, nullable=true)
     */
    protected $firstname;

    /**
     * @var string
     *
     * @ORM\Column(name="last_name", type="string", length=255, nullable=true)
     */
    protected $lastname;

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=255)
     */
    protected $password;

    /**
     * @ORM\ManyToMany(targetEntity="Role")
     * @ORM\JoinTable(name="users_roles",
     *          joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *          inverseJoinColumns={@ORM\JoinColumn(name="role_id", referencedColumnName="id")}
     *      )
     */
    protected $roles;

    /**
     * @ORM\ManyToOne(targetEntity="Language")
     * @ORM\JoinColumn(name="language_id", referencedColumnName="id", nullable=false)
     */
    protected $language;

    /**
     * @var boolean
     *
     * @ORM\Column(name="enabled", type="boolean")
     */
    protected $enabled = true;

    /**
     * @var boolean
     *
     * @ORM\Column(name="blocked", type="boolean")
     */
    protected $blocked = false;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="connectedAt", type="datetime", nullable=true)
     */
    protected $connectedAt;






    public function __construct(array $properties = array())
    {
        $this->roles = new ArrayCollection();
        parent::__construct($properties);
    }

    /**
     * Set email
     *
     * @param string $email
     *
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set username
     *
     * @param string $username
     *
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set password
     *
     * @param string $password
     *
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
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
     * @param ArrayCollection $roles
     */
    public function setRoles(ArrayCollection $roles)
    {
        $this->roles = $roles;
    }

    /**
     * @return array
     */
    public function getRoles()
    {
        return $this->roles->toArray();
    }

    public function setLanguage($language)
    {
        $this->language = $language;
    }

    public function getLanguage()
    {
        return $this->language;
    }


    public function getLocale()
    {
        if($this->language instanceof Language)
            return $this->language->getCode();
        return 'en';
    }

    /**
     * Set enabled
     *
     * @param boolean $enabled
     *
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
    }

    /**
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * Set blocked
     *
     * @param boolean $blocked
     *
     */
    public function setBlocked($blocked)
    {
        $this->blocked = $blocked;
    }


    public function isAccountNonLocked()
    {
        return !$this->blocked;
    }

    public function isCredentialsNonExpired()
    {
        return !$this->deleted;
    }


    public function isAccountNonExpired()
    {
        return !$this->deleted;
    }

    /**
     * Set connectedAt
     *
     * @param \DateTime $connectedAt
     *
     * @return Admin
     */
    public function setConnectedAt($connectedAt)
    {
        $this->connectedAt = $connectedAt;
    }

    /**
     * Get connectedAt
     *
     * @return \DateTime
     */
    public function getConnectedAt()
    {
        return $this->connectedAt;
    }

    public function getSalt()
    {
        // Do you need to a Salt property? If you use bcrypt, no. Otherwise, yes.
        // All passwords must be hashed with a salt, but bcrypt does this internally.
        return 'sdsadfdsffweir87r27389';
    }

    public function eraseCredentials()
    {
    }

    public function serialize()
    {
        return serialize(array(
                $this->id,
                $this->username,
                $this->email,
                $this->password,
                $this->enabled,
                $this->blocked,
                $this->deleted
            ));
    }

    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->username,
            $this->email,
            $this->password,
            $this->enabled,
            $this->blocked,
            $this->deleted
            ) = unserialize($serialized);
    }

    /**
     * Get enabled
     *
     * @return boolean
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * Get blocked
     *
     * @return boolean
     */
    public function getBlocked()
    {
        return $this->blocked;
    }

    /**
     * Add role
     *
     * @param \AppBundle\Entity\Role $role
     *
     * @return User
     */
    public function addRole(\AppBundle\Entity\Role $role)
    {
        $this->roles[] = $role;

        return $this;
    }

    /**
     * Remove role
     *
     * @param \AppBundle\Entity\Role $role
     */
    public function removeRole(\AppBundle\Entity\Role $role)
    {
        $this->roles->removeElement($role);
    }

    /**
     * @return bool
     */
    public function isAdmin(){
        foreach ($this->roles as $role){
            if($role instanceof Role && $role->getName() == 'ROLE_ADMIN'){
                return true;
            }
        }
        return false;
    }



    public function __toString()
    {
        return ucwords($this->firstname) . ' ' . ucwords($this->lastname);
    }
}
