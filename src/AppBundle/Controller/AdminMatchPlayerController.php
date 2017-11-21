<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Championship;
use AppBundle\Entity\Match;
use AppBundle\Entity\MatchPlayer;
use AppBundle\Entity\Player;
use AppBundle\Entity\Result;
use AppBundle\Entity\Team;
use AppBundle\Form\Type\MatchPlayerType;
use AppBundle\Lib\DataTable;
use AppBundle\Utility\EntityUtility;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use AppBundle\Form\Type\MatchType;
use Symfony\Component\Form\Form;

class AdminMatchPlayerController extends AdminController
{

    protected $page = 'match';

    /**
     * Add match player
     *
     * @Route("/admin/matches/{id}/player/", name="admin_add_match_player_route",
     *     requirements={"id": "\d+"}
     * )
     * @Method({"GET","POST"})
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function addAction(Request $request, $id)
    {
        $match = $id > 0 ? $this->getEntity($id, 'Match') : null;
        if($match instanceof Match) {
            $matchPlayer = new MatchPlayer();
            $matchPlayer->setMatch($match);
            $cancel = $this->generateUrl('admin_detail_match_route', ['id' => $id, 'tab' => 1]);

            $form = $this->createForm(MatchPlayerType::class, $matchPlayer);
            if ($request->isMethod('POST')) {
                $form->handleRequest($request);
                if ($form->isSubmitted()) {
                    if($form->get('save')->isClicked() || $form->get('saveAndAdd')->isClicked()){
                        try {
                            $response = $this->submitModelForm($request, $matchPlayer, $form, false);
                            if ($response == 'saveAndAdd') {
                                return $this->redirect($this->generateUrl('admin_add_match_player_route', ['id' => $id]));
                            } elseif ($response == 'save') {
                                return $this->redirect($cancel);
                            }
                        } catch (\Exception $e) {
                            $this->get('session')->getFlashBag()->set('error', $e->getMessage());
                        }
                    } else {
                        $form = $this->createForm(MatchPlayerType::class, $matchPlayer);
                    }
                }
            }

            $parameters = [
                'form_model' => $form->createView(),
                'form_title' => $this->get('translator')->trans('label.player'),
                'cancel_url' => $cancel,
                'matchPlayer' => $matchPlayer,
                'title' => $this->get('translator')->trans('match.match') . ' ' . $id,
            ];

            return $this->render('match/player.html.twig', $parameters);
        }

        $this->get('session')->getFlashBag()->set('error', $this->get('translator')->trans('error.not_found'));
        return $this->redirect($this->generateUrl('admin_match_route'));
    }

    /**
     * Edit match player
     *
     * @Route("/admin/match-player/{id}/", name="admin_edit_match_player_route",
     *     requirements={"id": "\d+"}
     * )
     * @Method({"GET","POST"})
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function editAction(Request $request, $id)
    {
        $matchPlayer = $id > 0 ? $this->getEntity($id, 'MatchPlayer') : null;
        if($matchPlayer instanceof MatchPlayer) {
            $cancel = $this->generateUrl('admin_detail_match_route', ['id' => $matchPlayer->getMatch()->getId(), 'tab' => 1]);

            $form = $this->createForm(MatchPlayerType::class, $matchPlayer);
            if ($request->isMethod('POST')) {
                $form->handleRequest($request);
                if ($form->isSubmitted()) {
                    if($form->get('save')->isClicked() || $form->get('saveAndAdd')->isClicked()){
                        try {
                            $response = $this->submitModelForm($request, $matchPlayer, $form, false);
                            if ($response == 'saveAndAdd') {
                                return $this->redirect($this->generateUrl('admin_add_match_player_route', [
                                        'id' => $matchPlayer->getMatch()->getId()
                                    ]
                                ));
                            } elseif ($response == 'save') {
                                return $this->redirect($cancel);
                            }
                        } catch (\Exception $e) {
                            $this->get('session')->getFlashBag()->set('error', $e->getMessage());
                        }
                    } else {
                        $form = $this->createForm(MatchPlayerType::class, $matchPlayer);
                    }
                }
            }

            $parameters = [
                'form_model' => $form->createView(),
                'form_title' => $this->get('translator')->trans('label.player'),
                'cancel_url' => $cancel,
                'matchPlayer' => $matchPlayer,
                'title' => $this->get('translator')->trans('match.match') . ' ' . $matchPlayer->getMatch()->getId()
            ];

            return $this->render('match/player.html.twig', $parameters);
        }

        $this->get('session')->getFlashBag()->set('error', $this->get('translator')->trans('error.not_found'));
        return $this->redirect($this->generateUrl('admin_match_route'));
    }

    /**
     *
     * @Route("/admin/match-player/{id}/delete/", name="admin_delete_match_player_route", requirements={"id": "\d+"})
     * @Method({"GET"})
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function deleteAction(Request $request, $id)
    {
        if(!$request->isXMLHttpRequest()){
            return $this->redirect($this->generateUrl('admin_match_route'));
        }
        $matchPlayer = $id > 0 ? $this->getEntity($id, 'MatchPlayer') : null;
        if ($matchPlayer instanceof MatchPlayer) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($matchPlayer);
            $em->flush();

            return $this->json([
                'element' => 'match-player-' . $id
            ]);
        } else {
            return $this->json([
                'element' => 'match-player-' . $id
                ],
                404
            );
        }
    }

    /**
     * @param MatchPlayer $matchPlayer
     * @param Form $form
     */
    protected function validate(MatchPlayer $matchPlayer, Form $form){
        // required team
        $team = $matchPlayer->getTeam();
        if(!$team instanceof Team){
            $form->get('team')->addError(
                new FormError($this->get('translator')->trans('error.required'))
            );
        }

        // required player
        $player = $matchPlayer->getPlayer();
        if(!$player instanceof Player){
            $form->get('player')->addError(
                new FormError($this->get('translator')->trans('error.required'))
            );
        }
    }
}
