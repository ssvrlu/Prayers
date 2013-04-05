<?php

namespace Prayer\SocialBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Socialcache
 *
 * @ORM\Table(name="socialcache")
 * @ORM\Entity(repositoryClass="Prayer\SocialBundle\Repository\SocialcacheRepository")
 */
class Socialcache
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
     * @ORM\Column(name="fetched_by_user_id", type="bigint", nullable=false)
     */
    private $fetchedByUserId;

    /**
     * @var string
     *
     * @ORM\Column(name="fetched_from", type="string", length=32, nullable=false)
     */
    private $fetchedFrom;

    /**
     * @var string
     *
     * @ORM\Column(name="first_name", type="string", length=255, nullable=false)
     */
    private $firstName;

    /**
     * @var string
     *
     * @ORM\Column(name="last_name", type="string", length=255, nullable=false)
     */
    private $lastName;

    /**
     * @var string
     *
     * @ORM\Column(name="picture_url", type="string", length=255, nullable=false)
     */
    private $pictureUrl;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255, nullable=true)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name=" identifier", type="string", length=255, nullable=true)
     */
    private $identifier;

    /**
     * @var string
     *
     * @ORM\Column(name="imported_at", type="datetime", nullable=true)
     */
    private $importedAt;



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
     * Set fetchedByUserId
     *
     * @param integer $fetchedByUserId
     * @return Socialcache
     */
    public function setFetchedByUserId($fetchedByUserId)
    {
        $this->fetchedByUserId = $fetchedByUserId;
    
        return $this;
    }

    /**
     * Get fetchedByUserId
     *
     * @return integer 
     */
    public function getFetchedByUserId()
    {
        return $this->fetchedByUserId;
    }

    /**
     * Set fetchedFrom
     *
     * @param string $fetchedFrom
     * @return Socialcache
     */
    public function setFetchedFrom($fetchedFrom)
    {
        $this->fetchedFrom = $fetchedFrom;
    
        return $this;
    }

    /**
     * Get fetchedFrom
     *
     * @return string 
     */
    public function getFetchedFrom()
    {
        return $this->fetchedFrom;
    }

    /**
     * Set firstName
     *
     * @param string $firstName
     * @return Socialcache
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    
        return $this;
    }

    /**
     * Get firstName
     *
     * @return string 
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Set lastName
     *
     * @param string $lastName
     * @return Socialcache
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    
        return $this;
    }

    /**
     * Get lastName
     *
     * @return string 
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Set pictureUrl
     *
     * @param string $pictureUrl
     * @return Socialcache
     */
    public function setPictureUrl($pictureUrl)
    {
        $this->pictureUrl = $pictureUrl;
    
        return $this;
    }

    /**
     * Get pictureUrl
     *
     * @return string 
     */
    public function getPictureUrl()
    {
        return $this->pictureUrl;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return Socialcache
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
     * Set identifier
     *
     * @param string $identifier
     * @return Socialcache
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
    
        return $this;
    }

    /**
     * Get identifier
     *
     * @return string 
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Set importedAt
     *
     * @param string $importedAt
     * @return Socialcache
     */
    public function setImportedAt($importedAt)
    {
        $this->importedAt = $importedAt;
    
        return $this;
    }

    /**
     * Get importedAt
     *
     * @return string 
     */
    public function getImportedAt()
    {
        return $this->importedAt;
    }
}