<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Championship;
use AppBundle\Entity\Language;
use AppBundle\Entity\Player;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class PlayerController extends FrontController
{

    protected $page = 'player';


    /**
     * @Route("/championships/{championship}/{season}/player-{id}.html",
     *     name="player_route",
     *     requirements={"championship": "\d+", "id": "\d+"}
     * )
     * @Method({"GET"})
     */
    public function showAction(Request $request, $championship, $season, $id)
    {
        if ($request->isXMLHttpRequest()) {
            $this->ajax = true;
        }

        $player = $this->getDoctrine()->getRepository('AppBundle:Player')->find($id);
        if(!$player instanceof Player){
            die('player not found');
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
                'id' => 'DESC'
            ]
        );

        $assign = [
            'player' => $player,
            'season' => $season,
            'championships_highlight' => $championships,
            'current_season' => $this->getCurrentSeason()
        ];

        $championships = $this->getDoctrine()->getRepository('AppBundle:Championship')->findByPlayer($id);
        foreach ($championships as $c){
            if($c instanceof Championship && $c->getId() == $championship){
                $championship = $c;
                break;
            }
        }

        if($championship instanceof Championship){
            $assign['championship'] = $championship;
            $assign['seasons'] = $this->getSeasons($championship);
            $assign['championships'] = $championships;
            $assign['pictures'] = $this->getDoctrine()->getRepository('AppBundle:Picture')->findByPlayerSeason(
                $id, $season
            );
            $players = $this->getDoctrine()->getRepository('AppBundle:Player')->standing(
                $championship->getId(), $season
            );
            foreach ($players as $t){
                if($t['id'] == $id){
                    $assign['stats'] = $t;
                    break;
                }
            }
        }

        $this->title = $player->getFirstname() . ' ' . $player->getLastname();
        $this->metaKeys = implode(', ', explode(' ', $this->title));

        return $this->render('player/show.html.twig', $assign);
    }
}
