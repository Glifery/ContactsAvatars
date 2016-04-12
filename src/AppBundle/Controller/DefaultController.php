<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        // replace this example code with whatever you need
        return $this->render('default/index.html.twig', array(
            'base_dir' => realpath($this->container->getParameter('kernel.root_dir').'/..'),
        ));
    }

    /**
     * @Route("/test", name="test")
     */
    public function testAction(Request $request)
    {
        $template = 'https://www.google.com/m8/feeds/contacts/default/full?alt=json&v=3.0&oauth_token=%s';
//        https://github.com/google/google-api-php-client/issues/462
//        https://developers.google.com/google-apps/contacts/v3/#retrieving_all_contacts

        /** @var User $user */
        $user = $this->getUser();
        $token = $user->getGoogleAccessToken();
        $fff = $this->get('google_contact_provider')->getContactsByToken($token);
        die($fff);

        $client = $this->get('guzzle.client');
        $request = $client->get('http://www.example.com/');
        $request = $request;
    }
}
