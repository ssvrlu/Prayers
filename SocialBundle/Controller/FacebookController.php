<?php

/**
 * Facebook Controller
 * This file is part of the PrayerSocialBundle package.
 *
 * Copyright (C) Covalense Technologies Private Ltd http://www.covalense.com <info@covalense.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Prayer\SocialBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Prayer\SocialBundle\Service\FacebookSocialService;

// These import the "@Route" and "@Template" annotations
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Facebook controller for facebook activities
 *
**/
class FacebookController extends Controller
{ 
   /**
     * @Route("/", name="_facebook")
     * @Template()
     */
   public function indexAction() {
	    $user = $this->getDoctrine()
					->getRepository('PrayerSocialBundle:User')
					->find(1);
		$fb_service   = $this->get('face_book');
		$friendsLists = array();
		$friendsLists = $fb_service->fetchPeople($user);
		return array('friendsLists' => $friendsLists); 
    }
}