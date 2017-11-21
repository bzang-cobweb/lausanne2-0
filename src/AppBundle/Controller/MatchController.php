<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Language;
use AppBundle\Entity\Match;
use AppBundle\Entity\News;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class MatchController extends FrontController
{

    protected $page = 'match';

    const MAX_ITEM_PER_PAGE = 20;


    /**
     * @Route("/matches/",
     *     name="match_route",
     *     requirements={"championship": "\d+", "page": "\d+"}
     * )
     * @Method({"GET"})
     */
    public function indexAction(Request $request)
    {
        if ($request->isXMLHttpRequest()) {
            $this->ajax = true;
        }

        $page = $request->get('page', 1);

        $params = [
            'criteria' => [],
            'orderBy' => [
                'scheduledAt' => 'DESC'
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

        // filter by season
        $season = $request->get('season', '');
        if($season != ''){
            $params['criteria']['season'] = $season;
        }

        // filter by team
        $team = $request->get('team', 0);
        if($team > 0){
            $params['criteria']['team'] = (int)$team;
        }

        list($matches, $total) = $this->getDoctrine()->getRepository('AppBundle:Match')->findByLike(
            $params['criteria'],
            $params['orderBy'],
            $params['limit'],
            $params['offset']
        );

        $assign = [
            'matches' => $matches,
        ];

        if($params['total'] < $total){
            $args = [];
            if(isset($params['criteria']['championship'])){
                $args['championship'] = $params['criteria']['championship'];
            }
            if(isset($params['criteria']['season'])){
                $args['season'] = $params['criteria']['season'];
            }
            $args['page'] = $page + 1;
            $assign['match_next_page'] = $this->generateUrl('match_route', $args);
        }

        return $this->render($this->ajax ? 'match/ajax_list.html.twig' : 'match/list.html.twig', $assign);
    }


    /**
     * @Route("/championships/{championship}/{season}/match-{id}.html",
     *     name="match_detail_route",
     *     requirements={"championship": "\d+", "id": "\d+"}
     * )
     * @Method({"GET"})
     */
    public function showAction(Request $request, $championship, $season, $id)
    {
        if ($request->isXMLHttpRequest()) {
            $this->ajax = true;
        }

        $match = $this->getDoctrine()->getRepository('AppBundle:Match')->find($id);

        if(!$match instanceof Match){
            if($this->ajax){
               return;
            } else {
                return $this->redirect(
                    $this->generateUrl(
                        'championship_match_route',
                        [
                            'id' => $championship,
                            'season' => $season
                        ]
                    )
                );
            }
        }

        $this->title = ' - ' . $match->getHome() . ' vs ' . $match->getVisitor();
        $this->metaKeys = $match->getHome() . ', ' . $match->getVisitor();

        $assign = [
            'match' => $match,
            'seasons' => $this->getSeasons($match->getChampionship()),
        ];

        return $this->render('match/show.html.twig', $assign);
    }
}
