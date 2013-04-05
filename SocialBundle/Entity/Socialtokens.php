<?php

namespace Prayer\SocialBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Socialtokens
 *
 * @ORM\Table(name="socialtokens")
 * @ORM\Entity(repositoryClass="Prayer\SocialBundle\Repository\SocialtokensRepository")
 */
class Socialtokens
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="access_for_user_id", type="bigint", nullable=false)
     */
    private $accessForUserId;

    /**
     * @var string
     *
     * @ORM\Column(name="access_to", type="string", length=32, nullable=false)
     */
    private $accessTo;

    /**
     * @var string
     *
     * @ORM\Column(name="token", type="string", length=255, nullable=false)
     */
    private $token;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="expires_at", type="datetime", nullable=true)
     */
    private $expiresAt;



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
     * Set accessForUserId
     *
     * @param integer $accessForUserId
     * @return Socialtokens
     */
    public function setAccessForUserId($accessForUserId)
    {
        $this->accessForUserId = $accessForUserId;
    
        return $this;
    }

    /**
     * Get accessForUserId
     *
     * @return integer 
     */
    public function getAccessForUserId()
    {
        return $this->accessForUserId;
    }

    /**
     * Set accessTo
     *
     * @param string $accessTo
     * @return Socialtokens
     */
    public function setAccessTo($accessTo)
    {
        $this->accessTo = $accessTo;
    
        return $this;
    }

    /**
     * Get accessTo
     *
     * @return string 
     */
    public function getAccessTo()
    {
        return $this->accessTo;
    }

    /**
     * Set token
     *
     * @param string $token
     * @return Socialtokens
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
     * Set expiresAt
     *
     * @param \DateTime $expiresAt
     * @return Socialtokens
     */
    public function setExpiresAt($expiresAt)
    {
        $this->expiresAt = $expiresAt;
    
        return $this;
    }

    /**
     * Get expiresAt
     *
     * @return \DateTime 
     */
    public function getExpiresAt()
    {
        return $this->expiresAt;
    }
}