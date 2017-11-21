<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Championship;
use AppBundle\Entity\Language;
use AppBundle\Entity\Team;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class TeamController extends FrontController
{

    protected $page = 'team';


    /**
     * @Route("/championships/{championship}/{season}/team-{id}.html",
     *     name="team_route",
     *     requirements={"championship": "\d+", "id": "\d+"}
     * )
     * @Method({"GET"})
     */
    public function showAction(Request $request, $championship, $season, $id)
    {
        if ($request->isXMLHttpRequest()) {
            $this->ajax = true;
        }

        $team = $this->getDoctrine()->getRepository('AppBundle:Team')->find($id);

        if(!$team instanceof Team){
            die('team not found');
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
            'team' => $team,
            'season' => $season,
            'championships_highlight' => $championships,
            'current_season' => $this->getCurrentSeason()
        ];

        $championships = $this->getDoctrine()->getRepository('AppBundle:Championship')->findByTeam($id);
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
            $assign['players'] = $this->getDoctrine()->getRepository('AppBundle:Player')->findByTeamSeason(
                $id, $season
            );
            $assign['pictures'] = $this->getDoctrine()->getRepository('AppBundle:Picture')->findByTeamSeason(
                $id, $season
            );
            $teams = $this->getDoctrine()->getRepository('AppBundle:Team')->standing(
                $championship->getId(), $season
            );
            foreach ($teams as $t){
                if($t['id'] == $id){
                    $assign['stats'] = $t;
                    break;
                }
            }
        }

        $this->title = $team->getName();
        $this->metaKeys = implode(', ', explode(' ', $this->title));

        return $this->render('team/show.html.twig', $assign);
    }
}
