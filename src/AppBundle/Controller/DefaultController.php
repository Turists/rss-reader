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

            $url = 'http://feeds.feedburner.com/Apollolv-AllArticles?format=xml';

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

            /*
            $graphUrl = 'https://graph.facebook.com/v2.4/me?fields=id,name,picture,email&access_token='.$accessToken;
            $userData = json_decode(file_get_contents($graphUrl));

            $data['fb'] = $userData;
            */

        }







        return $this->render('default/index.html.twig', $data);
    }


    public function loadUserByOAuthUserResponse(UserResponseInterface $response) {

        $email=$response->getEmail();// емэйл пользователя, например:totx@narod.ru
        $name=$response->getRealName();// имя пользователя на стороне oAuth-сервера, например:Nikolay Lebedenko
        $username=$response->getUsername();// уникальный ID пользователя на стороне oAuth-сервера, например:8d86a051742940e3
        $response->getAccessToken();// токен (уникальный идентификатор) для авторизации, например:ZxC1/2+3 (более 255 символов)
        $response->getExpiresIn();// предположительно: через какое время токен становится недействительным, например:3600 (секунд)
        $response->getProfilePicture();// изображение профиля, может не быть, например:пусто
        $response->getRefreshToken();// например:пусто
        $response->getTokenSecret();// например:пусто

        if(empty($email)){

            throw new UsernameNotFoundException('Вы не идентифицированы т.к. не получен Email-адрес');

        }

        $user=$this->getUserByWindowsLive($username);// находим пользователя
        /** @var $user \Acme\DemoBundle\Entity\User */

        // если пользователя нет в базе данных - добавим его
        if(!$user || !$user->getId()){

            $user=new User();
            $user->setName($name);
            $user->setEmail($email);
            $user->setWindowsLive($username);

            $this->doctrine->getManager()->persist($user);
            $this->doctrine->getManager()->flush();

            $user_id=$user->getId();
        }else{
            $user_id=$user->getId();
        }

        if(!$user_id){
            throw new UsernameNotFoundException('Возникла проблема добавления или определения пользователя');
        }

        return $this->loadUserByUsername($username);

    }
}
