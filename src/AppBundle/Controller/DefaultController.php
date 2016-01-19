<?php

namespace AppBundle\Controller;

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


        $reader = $this->container->get('debril.reader');



        // this date is used to fetch only the latest items
        $date = new \DateTime('2015-01-01');

        // the feed you want to read
        $url = 'http://feeds.feedburner.com/Apollolv-AllArticles?format=xml';

        // now fetch its (fresh) content
        $feed = $reader->getFeedContent($url, $date);

        // the $content object contains as many Item instances as you have fresh articles in the feed
        $items = $feed->getItems();

        // replace this example code with whatever you need
        return $this->render('default/index.html.twig', ['items' => $items]);
    }

    public function loginAction()
    {
        return $this->render('default/login.html.twig');
    }
}
