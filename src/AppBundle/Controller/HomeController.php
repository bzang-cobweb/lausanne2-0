<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Championship;
use AppBundle\Entity\Language;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class HomeController extends FrontController
{

    /**
     * @Route("/", name="home_route")
     * @Method({"GET"})
     */
    public function indexAction(Request $request)
    {

        if ($request->isXMLHttpRequest()) {
            $this->isAjax = true;
        }


        // get the higlighted championships
        $championships = $this->getDoctrine()->getRepository('AppBundle:Championship')->findBy(
            [
                'deleted' => false,
                'highlighted' => true
            ],
            [
                'updatedAt' => 'DESC',
                'createdAt' => 'DESC',
                'id' => 'DESC',
            ]
        );

        // current season
        $season = $this->getCurrentSeason();

        $standings = [];
        foreach ($championships as $championship){
            if($championship instanceof Championship) {
                $standings[$championship->getId()] = [
                    'players' => $this->getDoctrine()->getRepository('AppBundle:Player')->standing(
                        $championship->getId(), $season, self::MAX_ITEM_PER_PAGE
                    ),
                ];
                if(!$championship->isCup()){
                    $standings[$championship->getId()]['teams'] =
                        $this->getDoctrine()->getRepository('AppBundle:Team')->standing(
                            $championship->getId(), $season
                        );
                }

                $matches = $this->getDoctrine()->getRepository('AppBundle:Match')
                    ->findByLike(
                        [
                            'championship' => $championship->getId(),
                            'scheduledAt' => date('Y-m-d', strtotime('+8 days'))
                        ],
                        ['scheduledAt' => 'DESC'],
                        20
                    );
                $championship->setMatches($matches);
            }
        }

        return $this->render('home.html.twig',
            [
                'championships' => $championships,
                'standings' => $standings,
                'season' => $season
            ]
        );
    }
}
