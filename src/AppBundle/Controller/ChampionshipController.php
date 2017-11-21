<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Championship;
use AppBundle\Entity\Language;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ChampionshipController extends FrontController
{

    protected $page = 'championship';


    /**
     * @Route("/championships/{id}/{season}/news.html",
     *     name="championship_news_route",
     *     requirements={"id": "\d+"}
     * )
     * @Method({"GET"})
     */
    public function newsAction(Request $request, $id, $season)
    {
        if ($request->isXMLHttpRequest()) {
            $this->ajax = true;
        }

        $championship = $this->getDoctrine()->getRepository('AppBundle:Championship')->findOneBy([
            'id' => $id,
            'deleted' => false
        ]);

        if(!$championship instanceof Championship){
            throw new NotFoundHttpException('ksksk');
        }

        $assign = [
            'championship' => $championship,
            'season' => $season,
        ];

        return $this->render('championship/news.html.twig', $assign);
    }


    /**
     * @Route("/championships/{id}/{season}/matches.html",
     *     name="championship_match_route",
     *     requirements={"id": "\d+"}
     * )
     * @Method({"GET"})
     */
    public function matchAction(Request $request, $id, $season)
    {
        if ($request->isXMLHttpRequest()) {
            $this->ajax = true;
        }

        $championship = $this->getDoctrine()->getRepository('AppBundle:Championship')->findOneBy([
            'id' => $id,
            'deleted' => false
        ]);

        if(!$championship instanceof Championship){
            die('championship not found');
        }

        $assign = [
            'championship' => $championship,
            'season' => $season,
            'seasons' => $this->getSeasons($championship),
            'match_next_page' => $this->generateUrl('match_route', [
                'championship' => $id,
                'season' => $season
            ])
        ];

        return $this->render('championship/matches.html.twig', $assign);
    }

    /**
     * @Route("/championships/{id}/{season}/standings.html",
     *     name="championship_standings_route",
     *     requirements={"id": "\d+"}
     * )
     * @Method({"GET"})
     */
    public function standingAction(Request $request, $id, $season)
    {
        if ($request->isXMLHttpRequest()) {
            $this->ajax = true;
        }

        $championship = $this->getDoctrine()->getRepository('AppBundle:Championship')->findOneBy([
            'id' => $id,
            'deleted' => false
        ]);

        if(!$championship instanceof Championship){
            die('championship not found');
        }

        $assign = [
            'championship' => $championship,
            'season' => $season,
            'seasons' => $this->getSeasons($championship),
            'scorers' => $this->getDoctrine()->getRepository('AppBundle:Player')->standing(
                $id, $season
            ),
            'tab' => $request->get('tab', 0)
        ];

        if(!$championship->isCup()){
            $assign['teams'] = $this->getDoctrine()->getRepository('AppBundle:Team')->standing(
                $id, $season
            );
        }

        return $this->render('championship/standing.html.twig', $assign);
    }
}
