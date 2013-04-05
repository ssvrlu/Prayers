<?php

namespace Prayer\SocialBundle\Service;

/**
 * Abstract class which all social services must implement
 */
abstract class PLSocialService {
    /**
     * Returns a string of the name of the social service implemented.
     */
    public abstract function getServiceName();
    
    /**
     * Handles OAuth 2.0 authentication process. Tries to authenticate
     * the $user object passed in. Exception to be thrown on error
     * otherwise save the access token in the SocialTokens table and redirect
     * the user to the $returnURL on success.
     */
    protected abstract function authenticateUser($user);
    
    /**
     * Will use an access token if available for the user and service, otherwise
     * will fire off the authenticateUser method.
     *
     * If the SocialCache contains entries for this social service less than 3 hours
     * old then return an array of people.
     * 
     * Will connect to the social service and fully populate the SocialCache table
     * for the user of the people available through the social service. It will then
     * return an array of people.
     */
    public abstract function fetchPeople($user);
    
    protected function getAccessToken($user) {
        // Query SocialTokens for valid token
        // SELECT * FROM SocialTokens WHERE access_for_user_id = $user->getId()
        // AND access_to = $this->getServiceName() AND expires_at < [NOW IN UTC]
         
        // Return AccessToken or FALSE
    }
}