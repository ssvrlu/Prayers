<?php

namespace Prayer\SocialBundle\Service\Facebook;

use Prayer\SocialBundle\Service\PLSocialService;
use Prayer\SocialBundle\Service\Facebook\Facebook;
use Doctrine\ORM\EntityManager;
use Prayer\SocialBundle\Entity\User;
use Prayer\SocialBundle\Entity\Socialtokens;
use Prayer\SocialBundle\Entity\Socialcache;

/**
 * Facebook Social Services related to all activities on Facebook functionality, records Add/Delete in Cache/Token tables
 *
**/
class FacebookSocialService extends PLSocialService {
 
    /**
     *
     * @var EntityManager 
    */ 
    protected $_em;
	protected $_facebook;  
	/**
     * table cosntants
     */
    const SOCIAL_CACHE             = 'PrayerSocialBundle:Socialcache';
	const SOCIAL_TOKEN             = 'PrayerSocialBundle:Socialtokens';

	/**
	 * Configure facebook appID, Secret code related to application
	 *
	**/
    public function __construct(EntityManager $entityManager,$appId,$secret) { 
	    $config = array('appId'  => $appId,
				        'secret' => $secret,
				  );
        $this->_facebook = new Facebook($config);
		$this->_em = $entityManager;
    }
	
	/**
	 * Get the service name
	 *
	**/
    public function getServiceName() {
        return 'FACEBOOK';
    }
    
	/**
	 * If FACEBOOK then store Facebook Identifier $user->setFacebookId()
	 * Redirect to $returnURL
	**/
    protected function authenticateUser($user) {		 
		 $user_id = $this->_facebook->getUser();
		 
		 if($user_id) {
		     try {			 
		     	$friendsLists = $this->_facebook->api('/me/friends?fields=username,first_name,last_name,email,picture&access_token='.$this->_facebook->getAccessToken());
			 } catch(FacebookApiException $e) {
			     self::__redirectFacebook();
			}
			 self::__saveToken($user);
 		 } else {
			 self::__redirectFacebook();
		 }
		
         self::__savefriendData($user,$friendsLists);
		 $friendsLists = self::__getfriendsList($user);
		 return $friendsLists;
    }
	
	protected function __redirectFacebook(){
	     header("Location:".$this->_facebook->getLoginUrl());
		 exit;
	}
	
	/**
	 * Save friends date in SocialCache table, if user details are not found or about to more than 3 Hrs cache
	 *
	**/
	protected function __savefriendData($user,array $friends) {
		foreach($friends['data'] as $data) {
			$socialcache = new Socialcache();
			$socialcache->setFetchedByUserId($user->getObjectId())
						->setFetchedFrom($this->getServiceName())
						->setFirstName($data['first_name'])
						->setLastName($data['last_name'])
						->setPictureUrl($data['picture']['data']['url'])
						->setEmail( (isset($data['email']) ? $data['email'] : NULL))
						->setIdentifier($data['id'])
						->setImportedAt(new \DateTime());

			$this->_em->persist($socialcache);
			$this->_em->flush();
		}
	}
    
	/**
	 * Fetch the Facebook friends list related to logged user, if Cache records are inserted not more than 3 Hrs.
	 *
	**/
	public function fetchPeople($user) {
		self::__clearSocialCache($user);
		// Query SocialCache table
		$queryBuild = $this->_em
						->getRepository(self::SOCIAL_CACHE)
						->createQueryBuilder('sc') 
						->where( "sc.fetchedByUserId = '".$user->getObjectId()."'" )
						->andwhere( "sc.fetchedFrom = '".$this->getServiceName()."'" );
		
		$query = $queryBuild->getQuery();
		$socialCache = $query->getResult();				
		if(count($socialCache) > 0 ){
			return $socialCache;
		}
	
		// If NULL then query service
		$token = $this->getAccessToken($user);
	
		if ($token === FALSE) {
			return $this->authenticateUser($user);
		} else {
			try {
			    $this->_facebook->setAccessToken($token);
				$friendsLists = $this->_facebook->api('/me/friends?fields=name,username,first_name,last_name,email,picture&access_token='.$token);
			} catch(FacebookApiException $e) {
			    
			    self::__redirectFacebook();
			}
			// Query Service with AccessToken
			self::__savefriendData($user,$friendsLists);
			$friendsLists = self::__getfriendsList($user);
			return $friendsLists;
		}
	}
	
	/**
	 * Save the User token related to Facebook login
	 *
	**/
	protected function __saveToken($user) {
		
		$socialcache = new Socialtokens();
		$socialcache->setAccessForUserId($user->getObjectId())
					->setAccessTo($this->getServiceName())
					->setToken($this->_facebook->getAccessToken()) 
					->setExpiresAt(new \DateTime());
		//$socialcache->save();
		$this->_em->persist($socialcache);
		$this->_em->flush();			 
	}
	
	/**
	 * Retrive all the facebook friends list related to logged user cache is alive
	 *
	**/
	protected function __getfriendsList($user) {
		return $this->_em
					->getRepository(self::SOCIAL_CACHE)
					->findByFetchedByUserId($user->getObjectId());
	}
	
	/**
	 * Delete the Facebook Friends list in Cache table, if user create is created at more then 3 Hrs.
	 *
	**/
	protected function __clearSocialCache() {    
	   
	    $queryBuild = $this->_em
						->getRepository(self::SOCIAL_CACHE)
						->createQueryBuilder('st') 
						->delete()
						->where( "st.importedAt < '".date('Y-m-d H:i:s',strtotime('-3 hours'))."'" );
	
		$query = $queryBuild->getQuery();	
		
		$socialToken = $query->getResult();	   
		
    }
	
	/**
	 * Retrive Facebook access token
	 *
	**/
	protected function getAccessToken($user) {
		// Query SocialTokens for valid token
		$queryBuild = $this->_em
						->getRepository(self::SOCIAL_TOKEN)
						->createQueryBuilder('st') 
						->where( "st.expiresAt < '". date('Y-m-d H:i:s') ."'" )
						->andwhere( "st.accessTo  = '". $this->getServiceName() ."'" )
						->andwhere( "st.accessForUserId = '". $user->getObjectId()."'" );
	
		$query = $queryBuild->getQuery();	
		$socialToken = $query->getResult();	
		if( count($socialToken) > 0 ){
			return $socialToken[0]->getToken();
		}	
		return false;
		// Return AccessToken or FALSE
	}
	
	/**
	 * Get facebook details
	 *
	**/
	public function getFacebookDetails() {	     
		 return $this->_facebook;
	}
}