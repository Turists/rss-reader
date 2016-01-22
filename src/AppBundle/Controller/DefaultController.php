<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;


class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {

        $data = [];

        if ($this->isGranted('ROLE_OAUTH_USER')) {

            $reader = $this->container->get('debril.reader');

            $date = new \DateTime('2016-01-01');

            $url = $this->container->getParameter('feed_url');

            $feed = $reader->getFeedContent($url, $date);


            $items = $feed->getItems();

            $articles = [];
            foreach($items as $key => $item) {

                $articles[$key]['title'] = $item->getTitle();

                $description = $item->getDescription();

                $articles[$key]['description'] = strip_tags($description);

                preg_match( '@src="([^"]+)"@' , $description, $match );
                $src = array_pop($match);

                $articles[$key]['img'] = $src;
                $articles[$key]['published'] = $item->getUpdated()->format('d.m.Y H:i');
                $articles[$key]['link'] = $item->getLink();
                $articles[$key]['author'] = $item->getAuthor();
                $articles[$key]['comment'] = $item->getComment();

            }

            $paginator  = $this->get('knp_paginator');
            $pagination = $paginator->paginate(
                $articles,
                $request->query->getInt('page', 1)/*page number*/,
                10/*limit per page*/
            );

            $data = ['articles' => $pagination];

            $accessToken = $this->container->get('security.context')->getToken()->getAccessToken();
            
        }

        return $this->render('default/index.html.twig', $data);
    }
}
