<?php

namespace Prayer\SocialBundle\Service\Yahoo;

use Prayer\SocialBundle\Service\PLSocialService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Doctrine\ORM\EntityManager;
use Prayer\SocialBundle\Entity\User;
use Prayer\SocialBundle\Entity\Socialtokens;
use Prayer\SocialBundle\Entity\Socialcache;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Router;

require 'lib/globals.php';
require 'lib/oauth_helper.php';

/**
 * Yahoo Services related to all activities on Yahoo functionality, records Add/Delete in Cache/Token tables
 *
**/
class YahooSocialService extends PLSocialService 
{ 
    /**
     *
     * @var EntityManager 
    */	
    protected $_em;
	protected $_router;
	protected $_consumer_key;
	protected $_consumer_secret;
	protected $_gmail; 
	protected $_session;
	  
	/**
     * Table constants
     */
    const SOCIAL_CACHE             = 'PrayerSocialBundle:Socialcache';
	const SOCIAL_TOKEN             = 'PrayerSocialBundle:Socialtokens';
 

	/**
	* 
	* Configure Yahoo appID, Secret code related to application
	*
	* Documentation: http://code.google.com/apis/gdata/docs/2.0/basics.html
	* Visit https://code.google.com/apis/console?api=contacts to generate your
	* oauth2_client_id, oauth2_client_secret, and register your oauth2_redirect_uri.
	**/
	public function __construct(Router $router,EntityManager $entityManager,$appId,$secret) { 
		$config = array(); 
		$this->_consumer_key = $appId;
		$this->_consumer_secret = $secret;
		$this->_em = $entityManager;
		$this->_router = $router;
		define('OAUTH_CONSUMER_KEY', $appId);
		define('OAUTH_CONSUMER_SECRET', $secret);
	}
	
	/**
	 * Get the service name for Yahoo
	 *
	**/
    public function getServiceName() {
        return 'YAHOO';
    }

	/**
	 * To authenticate the user with his Yahoo email credentials and return the control back to Prayer
	 *
	**/
	public function authenticate($user,$oauth_verifier){
	    $request_token= $_COOKIE['RequestToken'];
		$request_token_secret = $_COOKIE['RequestTokenSecret'];
		$oauth_verifier= $_REQUEST['oauth_verifier'];
		
		// Get the access token using HTTP GET and HMAC-SHA1 signature
		$retarr = $this->get_access_token(OAUTH_CONSUMER_KEY, OAUTH_CONSUMER_SECRET,
		$request_token, $request_token_secret,
		$oauth_verifier, false, true, true);
		
		$retRestult=array();
		
		if (! empty($retarr)) 
		{
			list($info, $headers, $body, $body_parsed) = $retarr;
			if ($info['http_code'] == 200 && !empty($body)) 
			{

				$guid=$body_parsed['xoauth_yahoo_guid'];
				$access_token=rfc3986_decode($body_parsed['oauth_token']);
				$access_token_secret=$body_parsed['oauth_token_secret'];

				// Call Contact API
				$friends = $this->importContacts(OAUTH_CONSUMER_KEY, OAUTH_CONSUMER_SECRET,
				$guid, $access_token, $access_token_secret,
				false, true);
			
				self::__savefriendData($user,$friends);
				 
			}
			return true;
			
		}
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
						->setFirstName( (isset($data['name']) ? $data['name'] : NULL))
						->setEmail( (isset($data['email']) ? $data['email'] : NULL))
						->setIdentifier($data['id'])
						->setImportedAt(new \DateTime());
			$this->_em->persist($socialcache);
			$this->_em->flush();
		}
	}

	/**
	* Fetch the Yahoo friends list related to logged user, if Cache records are inserted not more than 3 Hrs.
	*
	**/
	public function fetchPeople($user) {
	    self::__clearSocialCache();
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
		
		$token = $this->getAccessToken($user);	
		if ($token === FALSE) {
			return $this->authenticateUser($user);
			
		} else { 
		    self::__removeSocialToken($user);
			return $this->authenticateUser($user);
		}
	}
	
	/**
	 * Delete the Yahoo email contacts list in Cache table, if user create is created at more then 3 Hrs.
	 *
	**/
	protected function __removeSocialToken($user) {	
		$queryBuild = $this->_em
						->getRepository(self::SOCIAL_TOKEN)
						->createQueryBuilder('st') 
						->delete()
						->andwhere( "st.accessTo  = '". $this->getServiceName() ."'" )
						->andwhere( "st.accessForUserId = '". $user->getObjectId()."'" );
	
		$query = $queryBuild->getQuery();	
		
		$socialToken = $query->getResult();	   
			
	}

	/**
	* Save the User token related to Yahoo login
	*
	**/	
	protected function __saveToken($user,$token) {	
		$socialcache = new Socialtokens();
		$socialcache->setAccessForUserId($user->getObjectId())
			->setAccessTo($this->getServiceName())
			->setToken($token)
			->setExpiresAt(new \DateTime());
	
		$this->_em->persist($socialcache);
		$this->_em->flush();			 
	}
	
	/**
	* Retrive all the Yahoo friends list related to logged user cache is alive
	*
	**/
	protected function __getfriendsList($user) {	
		return $this->_em
					->getRepository(self::SOCIAL_CACHE)
					->findByFetchedByUserId($user->getObjectId());	
	}

	/**
	 * Delete the Yahoo Friends list in Cache table, if user create is created at more then 3 Hrs.
	 *
	**/
	protected function __clearSocialCache() {	
		$queryBuild = $this->_em
						->getRepository(self::SOCIAL_CACHE)
						->createQueryBuilder('sc') 
						->delete()
						->where( "sc.importedAt < '".date('Y-m-d H:i:s',strtotime('-3 hours'))."'" );
	
		$query = $queryBuild->getQuery();	
		
		$socialToken = $query->getResult();	   
			
	}
	
	
	/**
	 * Retrieve Yahoo access token from database
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
			return json_decode($socialToken[0]->getToken());
		}	
		return false;
		//Return AccessToken or FALSE
	}

	/**
	 * Get the request token to connect to user's Yahoo account
	 *
	**/
	
	//Get the request token using HTTP GET and HMAC-SHA1 signature
	protected function authenticateUser($user)
	{
		$usePost=false;
		$useHmacSha1Sig=true;
		$passOAuthInHeader=false;
		$retarr = array();  // return value
		$response = array();
		$headers = array();
		$context = $this->_router->getContext();
		$callback = $context->getScheme(). '://'. $context->getHost() . ':' .
		                $context->getHttpPort() . 
						$this->_router->generate('_yahoo') ;
		
		$url = 'https://api.login.yahoo.com/oauth/v2/get_request_token';
		$params['oauth_version'] = '1.0';
		$params['oauth_nonce'] = mt_rand();
		$params['oauth_timestamp'] = time();
		$params['oauth_consumer_key'] = $this->_consumer_key;
		$params['oauth_callback'] = $callback;
 		
		// Compute signature and add it to the params list
		if ($useHmacSha1Sig) 
		{
			$params['oauth_signature_method'] = 'HMAC-SHA1';
			$params['oauth_signature'] =
			oauth_compute_hmac_sig($usePost? 'POST' : 'GET', $url, $params,
			 $this->_consumer_secret, null);
		} 
		else 
		{
			$params['oauth_signature_method'] = 'PLAINTEXT';
			$params['oauth_signature'] =
			oauth_compute_plaintext_sig(consumer_secret, null);
		}

		// Pass OAuth credentials in a separate header or in the query string
		if ($passOAuthInHeader) 
		{

			$query_parameter_string = oauth_http_build_query($params, FALSE);

			$header = build_oauth_header($params, "yahooapis.com");
			$headers[] = $header;
		} 
		else 
		{
			$query_parameter_string = oauth_http_build_query($params);
		}

		// POST or GET the request
		if ($usePost) 
		{
			$request_url = $url;
			logit("getreqtok:INFO:request_url:$request_url");
			logit("getreqtok:INFO:post_body:$query_parameter_string");
			$headers[] = 'Content-Type: application/x-www-form-urlencoded';
			$response = do_post($request_url, $query_parameter_string, 443, $headers);
		} 
		else 
		{
			$request_url = $url . ($query_parameter_string ?
			('?' . $query_parameter_string) : '' );

			logit("getreqtok:INFO:request_url:$request_url");

			$response = do_get($request_url, 443, $headers);
		}

		// Extract successful response
		if (! empty($response)) 
		{
			list($info, $header, $body) = $response;
			$body_parsed = oauth_parse_str($body);
			if (! empty($body_parsed)) 
			{
				logit("getreqtok:INFO:response_body_parsed:");
			}
			$retarr = $response;
			$retarr[] = $body_parsed;
		}

		if (! empty($retarr)){
			list($info, $headers, $body, $body_parsed) = $retarr;
			if(isset($body_parsed['oauth_problem']) && $body_parsed['oauth_problem']!='')
			{
				return new NotFoundHttpException("Error while connecting. please try again");
			}
			if ($info['http_code'] == 200 && !empty($body)) 
			{
				$data = array('RequestToken'=>$body_parsed['oauth_token'], 'RequestTokenSecret'=>$body_parsed['oauth_token_secret'],
				              'RequestUrl'=>$body_parsed['xoauth_request_auth_url']);
				self::__saveToken($user,json_encode($data));
				setcookie('RequestToken',$body_parsed['oauth_token'],0);
				setcookie('RequestTokenSecret',$body_parsed['oauth_token_secret'],0);
				if(isset($body_parsed['xoauth_request_auth_url']) && $body_parsed['xoauth_request_auth_url']!="")
				{
				    header("Location:".urldecode($body_parsed['xoauth_request_auth_url']));
					exit;
				}		
			}
		}
		
		return $retarr;
	}
	
	
	/* 
	Get the access token using HTTP GET and HMAC-SHA1 signature
	Input: Taking the yahoo API credentails from the request token returning function
	Output: Returns the access token for Yahoo.	
	*/
	public function get_access_token($consumer_key, $consumer_secret, $request_token, $request_token_secret, $oauth_verifier, $usePost=false, $useHmacSha1Sig=true, $passOAuthInHeader=true)
	{
		$retarr = array();  // return value
		$response = array();

		$url = 'https://api.login.yahoo.com/oauth/v2/get_token';
		$params['oauth_version'] = '1.0';
		$params['oauth_nonce'] = mt_rand();
		$params['oauth_timestamp'] = time();
		$params['oauth_consumer_key'] = $consumer_key;
		$params['oauth_token']= $request_token;
		$params['oauth_verifier'] = $oauth_verifier;

		// Compute signature and add it to the params list
		if ($useHmacSha1Sig) 
		{
			$params['oauth_signature_method'] = 'HMAC-SHA1';
			$params['oauth_signature'] =
			oauth_compute_hmac_sig($usePost? 'POST' : 'GET', $url, $params,
								 $consumer_secret, $request_token_secret);
		} else 
		{
			$params['oauth_signature_method'] = 'PLAINTEXT';
			$params['oauth_signature'] =
			oauth_compute_plaintext_sig($consumer_secret, $request_token_secret);
		}

		// Pass OAuth credentials in a separate header or in the query string
		if ($passOAuthInHeader) 
		{
		$query_parameter_string = oauth_http_build_query($params, false);
		$header = build_oauth_header($params, "yahooapis.com");
		$headers[] = $header;
		} else 
		{
		$query_parameter_string = oauth_http_build_query($params);
		}

		// POST or GET the request
		if ($usePost) {
		$request_url = $url;
		logit("getacctok:INFO:request_url:$request_url");
		logit("getacctok:INFO:post_body:$query_parameter_string");
		$headers[] = 'Content-Type: application/x-www-form-urlencoded';
		$response = do_post($request_url, $query_parameter_string, 443, $headers);
		} else {
		$request_url = $url . ($query_parameter_string ?
						   ('?' . $query_parameter_string) : '' );
		logit("getacctok:INFO:request_url:$request_url");
		$response = do_get($request_url, 443, $headers);
		}

		// Extract successful response
		if (! empty($response)) {
		list($info, $header, $body) = $response;
		$body_parsed = oauth_parse_str($body);
		if (! empty($body_parsed)) {
		logit("getacctok:INFO:response_body_parsed:");
		}
		$retarr = $response;
		$retarr[] = $body_parsed;
		}
        
		return $retarr;
	}
	
	/* 
	Call Contact API for geting the contacts.
	Input: Taking the Yahoo API credentails from above function.
	Output: Returns the list of conatcst in an Array format.
	*/
	public function importContacts($consumer_key, $consumer_secret, $guid, $access_token, $access_token_secret, $usePost=false, $passOAuthInHeader=true)
	{
	$retarr = array();  // return value
	$response = array();

	$url = 'http://social.yahooapis.com/v1/user/' . $guid . '/contacts;';
	$params['format'] = 'json';
	$params['view'] = 'compact';
	$params['oauth_version'] = '1.0';
	$params['oauth_nonce'] = mt_rand();
	$params['oauth_timestamp'] = time();
	$params['oauth_consumer_key'] = $consumer_key;
	$params['oauth_token'] = $access_token;

	// compute hmac-sha1 signature and add it to the params list
	$params['oauth_signature_method'] = 'HMAC-SHA1';
	$params['oauth_signature'] =
	oauth_compute_hmac_sig($usePost? 'POST' : 'GET', $url, $params,
		 $consumer_secret, $access_token_secret);

	// Pass OAuth credentials in a separate header or in the query string
	if ($passOAuthInHeader) 
	{
		$query_parameter_string = oauth_http_build_query($params, true);
		$header = build_oauth_header($params, "yahooapis.com");
		$headers[] = $header;
	} 
	else 
	{
		$query_parameter_string = oauth_http_build_query($params);
	}

	// POST or GET the request
	if ($usePost) 
	{
	$request_url = $url;
	logit("callcontact:INFO:request_url:$request_url");
	logit("callcontact:INFO:post_body:$query_parameter_string");
	$headers[] = 'Content-Type: application/x-www-form-urlencoded';
	$response = do_post($request_url, $query_parameter_string, 80, $headers);
	} 
	else 
	{
	$request_url = $url . ($query_parameter_string ?
	   ('?' . $query_parameter_string) : '' );
	logit("callcontact:INFO:request_url:$request_url");
	$response = do_get($request_url, 80, $headers);
	}

	// Extract successful response
	if (! empty($response)) 
	{
		list($info, $header, $body) = $response;
			if ($body) 
			{
				logit("callcontact:INFO:response:");
				$returnArray=array();
				$json_o=json_decode(json_pretty_print($body));
				for($i=0;$i<$json_o->contacts->count; $i++)
				{

					if(($json_o->contacts->contact[$i]->fields[0]->type=='email') )
					{
						$returnArray[$i]['id']=$json_o->contacts->contact[$i]->id;			
						$returnArray[$i]['email']=$json_o->contacts->contact[$i]->fields[0]->value;		
						
						if(isset($json_o->contacts->contact[$i]->fields[1]->value) && is_object($json_o->contacts->contact[$i]->fields[1]->value)){
							$nameValue = $json_o->contacts->contact[$i]->fields[1]->value;
							$returnArray[$i]['name']=$nameValue->givenName;			
						}
					}
					else if( isset($json_o->contacts->contact[$i]->fields[1]->type) && ($json_o->contacts->contact[$i]->fields[1]->type=='email') )
					{
						$returnArray[$i]['id']=$json_o->contacts->contact[$i]->id;		
						$returnArray[$i]['email']=$json_o->contacts->contact[$i]->fields[1]->value;
						if(isset($json_o->contacts->contact[$i]->fields[0]->value) && is_object($json_o->contacts->contact[$i]->fields[0]->value)){
							$nameValue = $json_o->contacts->contact[$i]->fields[0]->value;
							$returnArray[$i]['name']=$nameValue->givenName;			
						}					
					}			
				}	
			}
		$retarr = $returnArray;
	}

	return $retarr;
	}
}