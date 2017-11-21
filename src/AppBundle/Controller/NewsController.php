<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Language;
use AppBundle\Entity\News;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class NewsController extends FrontController
{

    protected $page = 'news';


    /**
     * @Route("/news/", name="news_route", requirements={"page": "\d+"})
     * @Method({"GET"})
     */
    public function indexAction(Request $request)
    {
        if ($request->isXMLHttpRequest()) {
            $this->ajax = true;
        } else {
            $this->metaKeys = 'news, actualités';
        }

        $page = $request->get('page', 1);

        $params = [
            'criteria' => [
                'deleted' => false
            ],
            'orderBy' => [
                'createdAt' => 'DESC'
            ],
            'offset' => $page > 1 ? ($page - 1) * self::MAX_ITEM_PER_PAGE : 0,
            'limit' => self::MAX_ITEM_PER_PAGE,
            'total' => $page * self::MAX_ITEM_PER_PAGE
        ];

        // filter by championship
        $championship = $request->get('championship', 0);
        if($championship > 0){
            $params['criteria']['championship'] = (int)$championship;
        }

        $max = 100;

        list($news, $total) = $this->getDoctrine()->getRepository('AppBundle:News')->findByLike(
            $params['criteria'],
            $params['orderBy'],
            $params['limit'],
            $params['offset']
        );

        $assign = [
            'news' => $news,
            'season' => $request->get('season', $this->getCurrentSeason())
        ];

        if($params['total'] < $total && $max > $params['total']){
            $assign['news_next_page'] = $this->generateUrl('news_route', [
                'page' => $page + 1
            ]);
        }

        return $this->render($this->ajax ? 'news/ajax_list.html.twig' : 'news/list.html.twig', $assign);
    }


    /**
     * @Route("/championships/{championship}/{season}/news-{id}.html",
     *     name="news_detail_route",
     *     requirements={"championship": "\d+", "id": "\d+"}
     * )
     * @Method({"GET"})
     */
    public function showAction(Request $request, $championship, $season, $id)
    {
        if ($request->isXMLHttpRequest()) {
            $this->ajax = true;
        }

        $news = $this->getDoctrine()->getRepository('AppBundle:News')->findOneBy([
            'id' => $id,
            'deleted' => false
        ]);

        if(!$news instanceof News){
            if($this->ajax){
               return;
            } else {
                return $this->redirect(
                    $this->generateUrl(
                        'championship_news_route',
                        [
                            'id' => $championship,
                            'season' => $season
                        ]
                    )
                );
            }
        }

        $this->title = $news->getTitle();
        $this->metaDesc = $news->getTeaser();

        $assign = [
            'news' => $news,
            'season' => $season
        ];

        return $this->render('news/show.html.twig', $assign);
    }
}
