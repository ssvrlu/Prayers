<?php

/*
 * Yahoo Controller
 * This file is part of the PrayerSocialBundle package.
 *
 * Copyright (C) Covalense Technologies Private Ltd http://www.covalense.com <info@covalense.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Prayer\SocialBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

// These import the "@Route" and "@Template" annotations
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;


class YahooController extends Controller
{ 
   /**
     * @Route("/", name="_yahoo")
     * @Template()
     */
   public function indexAction() {
	    $user = $this->getDoctrine()
					->getRepository('PrayerSocialBundle:User')
					->find(1);
		$gmail_service   = $this->get('yahoo');
        $request = $this->getRequest();
		
		$oauth_verifier = $request->query->get('oauth_verifier');
		$friendsLists = array();
		
		if(isset($oauth_verifier)){
		     $gmail_service->authenticate($user,$oauth_verifier);
			 return $this->redirect($this->generateUrl('_yahoo'));
		}
		
		$friendsLists = $gmail_service->fetchPeople($user);		
		return array('friendsLists' => $friendsLists); 
    }
}