<?php

namespace Prayer\SocialBundle\Service\Google;

use Prayer\SocialBundle\Service\PLSocialService;
use Prayer\SocialBundle\Service\Google\lib\GoogleClient;
use Prayer\SocialBundle\Service\Google\lib\io\Google_HttpRequest; 
use Doctrine\ORM\EntityManager;
use Prayer\SocialBundle\Entity\User;
use Prayer\SocialBundle\Entity\Socialtokens;
use Prayer\SocialBundle\Entity\Socialcache;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Router;

/**
 * Google/Gmail Services related to all activities on Gmail functionality, records Add/Delete in Cache/Token tables
 *
**/
class GoogleSocialService extends PLSocialService 
{ 
    /**
     * 
     * @var EntityManager 
    */	
    protected $_em;
	protected $_gmail; 
	protected $_session; 
	 
	/**
     * table cosntants
     */
    const SOCIAL_CACHE             = 'PrayerSocialBundle:Socialcache';
	const SOCIAL_TOKEN             = 'PrayerSocialBundle:Socialtokens';


	/**
	* 
	* Configure google/gmail appID, Secret code related to application
	*
	* Documentation: http://code.google.com/apis/gdata/docs/2.0/basics.html
	* Visit https://code.google.com/apis/console?api=contacts to generate your
	* oauth2_client_id, oauth2_client_secret, and register your oauth2_redirect_uri.
	**/
	public function __construct(Router $router,EntityManager $entityManager,$appId,$secret) {
		$config = array(); 
		$context = $router->getContext();
		$redirect_uri = $context->getScheme(). '://'. $context->getHost() . ':' .
		                $context->getHttpPort() . $router->generate('_google') ;
		$this->_gmail = new GoogleClient();
		$this->_gmail->setScopes("http://www.google.com/m8/feeds/contacts/default/full");
		$this->_gmail->setClientId($appId);
		$this->_gmail->setClientSecret($secret);
		$this->_gmail->setRedirectUri($redirect_uri);		
		$this->_em = $entityManager;
	}
	
	/**
	 * Get the service name
	 *
	**/
    public function getServiceName() {
        return 'GOOGLE';
    }

	/**
	 * 
	 *
	**/
	public function authenticate(){
	    $this->_session = new Session();
	    $this->_gmail->authenticate();
		$this->_session->set('token',$this->_gmail->getAccessToken());		
	}
    
	/**
	* If GOOGLE then store Google Identifier $user->setGoogleId()
	* Redirect to $returnURL
	**/
	protected function authenticateUser($user) {
		header("Location: ".$this->_gmail->createAuthUrl());
		exit();
		return true;	
	}
	
	/**
	* Save friends date in SocialCache table, if user details are not found or about to more than 3 Hrs cache
	*
	**/
	protected function __savefriendData($user,array $friends) {
		foreach($friends as $data) {
			$socialcache = new Socialcache();
			$socialcache->setFetchedByUserId($user->getObjectId())
						->setFetchedFrom($this->getServiceName())
						->setFirstName($data['name'])
						->setEmail( (isset($data['email']) ? $data['email'] : NULL))
						->setIdentifier($data['id'])
						->setImportedAt(new \DateTime());
			$this->_em->persist($socialcache);
			$this->_em->flush();
		}
	}

	/**
	* Import all the contacts based on user token and Save the Token in SocialToken table and User details in SocialCache table.
	*
	**/
	public function importContacts($user){
		
		$this->_gmail->setAccessToken($this->_session->get('token')); 
		
		$token = $this->_gmail->getAccessToken();
		$req = new Google_HttpRequest("https://www.google.com/m8/feeds/contacts/default/full");
		$val = $this->_gmail->getIo()->authenticatedRequest($req);
		$str= simplexml_load_string(str_replace('gd:email','contactmail',$val->getResponseBody()));
	
		// The contacts api only returns XML responses.
		$response = json_encode($str);
		$response = json_decode($response, true);
		$friends = array();
		foreach($response['entry'] as $contacts){
			$data['name'] = '';
			if(!is_array($contacts['title']))$data['name'] = $contacts['title'];
			$data['email'] = $contacts['contactmail']['@attributes']['address'];
			$arrEmail = explode('base/',$contacts['id']);
			$data['id'] = $arrEmail[1];
			array_push($friends,$data);
		}
		self::__saveToken($user);
		self::__savefriendData($user,$friends);
		return true;
	}

	/**
	* Fetch the Google/Gmail friends list related to logged user, if Cache records are inserted not more than 3 Hrs.
	*
	**/
	public function fetchPeople($user) {
	    self::__clearSocialCache();
		// Query SocialCache table
		// SELECT * FROM SocialCache WHERE fetched_by_user_id = $user->getId()
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
		    $this->_session = new Session();
			self::importContacts($user);
			$friendsLists = self::__getfriendsList($user);
			return $friendsLists;
		}
	}

	/**
	* Save the User token related to Google/Gmail login
	*
	**/	
	protected function __saveToken($user) {	
		$socialcache = new Socialtokens();
		$socialcache->setAccessForUserId($user->getObjectId())
			->setAccessTo($this->getServiceName())
			->setToken($this->_gmail->getAccessToken()) 
			->setExpiresAt(new \DateTime());
	
		$this->_em->persist($socialcache);
		$this->_em->flush();			 
	}
	
	/**
	* Retrive all the Google/Gmail friends list related to logged user cache is alive
	*
	**/
	protected function __getfriendsList($user) {	
		return $this->_em
					->getRepository(self::SOCIAL_CACHE)
					->findByFetchedByUserId($user->getObjectId());	
	}

	/**
	 * Delete the Google/Gmail Friends list in Cache table, if user create is created at more then 3 Hrs.
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
	 * Retrive Google/Gmail access token
	 *
	**/
	protected function getAccessToken($user) {
		//$getcontact=new GmailGetContacts();
		//$access_token=$getcontact->get_request_token($oauth, false, true, true);
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
	 * Get Gmail details
	 *
	**/
	public function getFacebookDetails(){
	     
		 return $this->_gmail;
	}
}