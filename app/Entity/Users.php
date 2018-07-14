<?php
/**
 * Created by PhpStorm.
 */
namespace App\Entity;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\GeneratedValue;
/**
 * @Entity
 * @Table(name="users")
 */
class Users
{
    /**
     * @var integer
     *
     * @Id
     * @Column(name="id", type="integer")
     * @GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     * @Column(type="string", length=255)
     */
    protected $email;

    /**
     * @var string
     * @Column(type="string", length=255)
     */
    protected $username;

    /**
     * @var string
     * @Column(type="string", length=255)
     */
    protected $role;

    /**
     * @var string
     * @Column(type="string", length=255)
     */
    protected $password;

    /**
     * @var boolean
     * @Column(type="boolean")
     */
    protected $enable;

    /**
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $token;

    /**
     * @var \DateTime
     * @Column(type="datetime", nullable=true)
     */
    protected $password_requested_dead_time;

    /**
     * @var \DateTime
     * @Column(type="datetime", nullable=true)
     */
    protected $last_login;


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return Users
     */
    public function setEmail($email)
    {
        $this->email = $email;
    
        return $this;
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
     * @return Users
     */
    public function setUsername($username)
    {
        $this->username = $username;
    
        return $this;
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
     * Set role
     *
     * @param string $role
     *
     * @return Users
     */
    public function setRole($role)
    {
        $this->role = $role;
    
        return $this;
    }

    /**
     * Get role
     *
     * @return string
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Set password
     *
     * @param string $password
     *
     * @return Users
     */
    public function setPassword($password)
    {
        $options = [
            'cost' => 9,
        ];
        $this->password = password_hash($password, PASSWORD_BCRYPT, $options);

        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function checkPassword($pass)
    {
        return password_verify($pass, $this->password);
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set enable
     *
     * @param boolean $enable
     *
     * @return Users
     */
    public function setEnable($enable)
    {
        $this->enable = $enable;
    
        return $this;
    }

    /**
     * Get enable
     *
     * @return boolean
     */
    public function getEnable()
    {
        return $this->enable;
    }

    /**
     * Set token
     *
     * @param string $token
     *
     * @return Users
     */
    public function setToken($token)
    {
        $this->token = $token;
    
        return $this;
    }

    /**
     * Get token
     *
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set lastLogin
     *
     * @param \DateTime $lastLogin
     *
     * @return Users
     */
    public function setLastLogin($lastLogin)
    {
        $this->last_login = $lastLogin;
    
        return $this;
    }

    /**
     * Get lastLogin
     *
     * @return \DateTime
     */
    public function getLastLogin()
    {
        return $this->last_login;
    }

    /**
     * Set passwordRequestedDeadTime
     *
     * @param \DateTime $passwordRequestedDeadTime
     *
     * @return Users
     */
    public function setPasswordRequestedDeadTime($passwordRequestedDeadTime)
    {
        $this->password_requested_dead_time = $passwordRequestedDeadTime;
    
        return $this;
    }

    /**
     * Get passwordRequestedDeadTime
     *
     * @return \DateTime
     */
    public function getPasswordRequestedDeadTime()
    {
        return $this->password_requested_dead_time;
    }
}
