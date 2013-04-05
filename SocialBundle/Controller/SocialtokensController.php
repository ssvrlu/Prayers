<?php

namespace Prayer\SocialBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Prayer\SocialBundle\Entity\Socialtokens;

/**
 * Social Tokens Controller
 * This file is part of the PrayerSocialBundle package.
 *
 * Copyright (C) Covalense Technologies Private Ltd http://www.covalense.com <info@covalense.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @Route("/socialtokens")
 */
class SocialtokensController extends Controller
{
    /**
     * Lists all Socialtokens entities.
     *
     * @Route("/", name="socialtokens")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('PrayerSocialBundle:Socialtokens')->findAll();

        return array(
            'entities' => $entities,
        );
    }

    /**
     * Finds and displays a Socialtokens entity.
     *
     * @Route("/{id}", name="socialtokens_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('PrayerSocialBundle:Socialtokens')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Socialtokens entity.');
        }
        return array(
            'entity'      => $entity,
        );
    }
}