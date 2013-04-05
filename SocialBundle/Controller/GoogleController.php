<?php

/**
 * Google/Gmail Controller
 * This file is part of the PrayerSocialBundle package.
 *
 * Copyright (C) Covalense Technologies Private Ltd http://www.covalense.com <info@covalense.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace Prayer\SocialBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Prayer\SocialBundle\Service\GoogleSocialService;

// These import the "@Route" and "@Template" annotations
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;


class GoogleController extends Controller
{ 
   /**
     * @Route("/", name="_google")
     * @Template()
     */
   public function indexAction() {
	    $user = $this->getDoctrine()
					->getRepository('PrayerSocialBundle:User')
					->find(1);
		$gmailService   = $this->get('google');
        $request = $this->getRequest();
		$oauthVerifier = $request->query->get('code');
		$friendsLists = array();
		if(isset($oauthVerifier)){
		     $gmailService->authenticate();
			 $gmailService->importContacts($user);
			 return $this->redirect($this->generateUrl('_google'));
		}
		$friendsLists = $gmailService->fetchPeople($user);		
		return array('friendsLists' => $friendsLists); 
    }
}