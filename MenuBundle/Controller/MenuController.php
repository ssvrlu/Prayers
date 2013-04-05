<?php
/**
 This class is used to manage all application level menus.
 
 @author Covalense
 @package Prayer
 @copyright © 2013, Covalense Technologies
*/
namespace Prayer\MenuBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class MenuController extends Controller
{
	/**
	 *
	 * Inital View, navigate to Today's related view page (index.html.twig)
	 *
	 **/
    public function indexAction() {
        return $this->render('PrayerMenuBundle:Menu:index.html.twig');
    }
	
	/**
	 * 
	 * My Profile View, navigate to My Profile view page (myprofile.html.twig)
	 *
	 **/
	public function myprofileAction() {
        return $this->render('PrayerMenuBundle:Menu:myprofile.html.twig');
    }
	
	/**
	 *
	 * Prayer Feed View, navigate to Prayer Feed view page (prayerfeed.html.twig)
	 *
	 **/
	public function prayerfeedAction() {
        return $this->render('PrayerMenuBundle:Menu:prayerfeed.html.twig');
    }
	
	/**
	 *
	 * Friends View, navigate to Priends view page (friends.html.twig)
	 *
	 **/
	public function friendsAction() {
        return $this->render('PrayerMenuBundle:Menu:friends.html.twig');
    }
	
	/**
	 *
	 * Circles View, navigate to Friends view page (circles.html.twig)
	 *
	 **/
	public function circlesAction() {
        return $this->render('PrayerMenuBundle:Menu:circles.html.twig');
    }
	
	/**
	 *
	 * Groups View, navigate to Groups view page (groups.html.twig)
	 *
	 **/
	public function groupsAction() {
        return $this->render('PrayerMenuBundle:Menu:groups.html.twig');
    }
	
	/**
	 *
	 * Pages View, navigate to Pages view page (pages.html.twig)
	 *
	 **/
	public function pagesAction() {
        return $this->render('PrayerMenuBundle:Menu:pages.html.twig');
    }
	
	/**
	 *
	 * Give View, navigate to Give view page (give.html.twig)
	 *
	 **/
	public function giveAction() {
        return $this->render('PrayerMenuBundle:Menu:give.html.twig');
    }
	
	/**
	 *
	 * Notifications View, navigate to Notifications view page (notifications.html.twig)
	 *
	 **/
	public function notificationsAction() {
        return $this->render('PrayerMenuBundle:Menu:notifications.html.twig');
    }
}
