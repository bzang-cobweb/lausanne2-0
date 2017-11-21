<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Championship;
use AppBundle\Entity\Match;
use AppBundle\Entity\Result;
use AppBundle\Entity\Team;
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

class AdminMatchController extends AdminController
{

    protected $page = 'match';
    

    /**
     * @Route("/admin/matches/", name="admin_match_route")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function listAction(Request $request)
    {
        $this->action = 'list';

        // execute data table actions
        $this->dataTableAction($request, 'AppBundle:Match');

        $params = [];
        $teamId = $request->get('team', 0);
        if($teamId > 0){
            $params['team'] = $teamId;
        }

        $championshipId = $request->get('champ', 0);
        if($championshipId > 0){
            $params['champ'] = $championshipId;
        }

        // init data table elements
        $this->initDataTable($request, $params);

        // get query params in order to filter data from db
        $qParams = $this->dataTable->getQueryParams();
        if(!$qParams['success']){
            die($qParams['reason']);
        }

        if(isset($params['team'])){
            $qParams['data']['criteria']['team'] = $params['team'];
        } else if(isset($params['champ'])){
            $qParams['data']['criteria']['championship'] = $params['champ'];
        }

        list($matches, $total) = $this->getDoctrine()->getRepository('AppBundle:Match')->findByLike(
            $qParams['data']['criteria'],
            $qParams['data']['orderBy'],
            $qParams['data']['limit'],
            $qParams['data']['offset']
        );

        // load data into data table
        $this->dataTable->setData($matches, $total);

        $parameters = [
            'page_route' => 'admin_match_route',
            'title' => $this->get('translator')->trans('label.matches')
        ];

        return $this->render($request->isXMLHttpRequest() ? 'dataTable/table.html.twig' : 'match/list.html.twig', $parameters);
    }

    /**
     *
     * @Route("/admin/matches/{id}/detail/", name="admin_detail_match_route", requirements={"id": "\d+"})
     * @Method({"GET"})
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function detailAction(Request $request, $id)
    {
        $match = $this->getEntity($id, 'Match');
        if($match instanceof Match) {
            return $this->render('match/detail.html.twig', [
                'match' => $match,
                'tab' => $request->get('tab', 0),
                'back_to_list' => $this->generateUrl('admin_match_route'),
                'title' => $this->get('translator')->trans('match.match') . ' ' . $id
            ]);
        }

        $this->get('session')->getFlashBag()->set('error', 'error.not_found');
        return $this->redirect($this->generateUrl('admin_match_route'));
    }

    /**
     * Add or edit match
     *
     * @Route("/admin/matches/{id}/", name="admin_add_edit_match_route",
     *     requirements={"id": "\d+"}
     * )
     * @Method({"GET","POST"})
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function addOrEditAction(Request $request, $id = 0)
    {
        $match = $id > 0 ? $this->getEntity($id, 'Match') : new Match();
        if($match instanceof Match) {
            if ($id > 0) {
                $cancel = $this->generateUrl('admin_detail_match_route', ['id' => $id]);
                if(!$match->getResult() instanceof Result){
                    $match->setResult(new Result());
                }
            } else {
                $cancel = $this->generateUrl('admin_match_route');
                $match->setScheduledAt(new \DateTime());
            }

            $form = $this->createForm(MatchType::class, $match);
            if ($request->isMethod('POST')) {
                $form->handleRequest($request);
                if ($form->isSubmitted()) {
                    if($form->get('save')->isClicked() || $form->get('saveAndAdd')->isClicked()){
                        try {
                            // result
                            $result = $match->getResult();
                            if($result instanceof Result && !$result->isNew()) {
                                $result->setUpdatedAt(new \DateTime());
                            }

                            $response = $this->submitModelForm($request, $match, $form, false);
                            if ($response == 'saveAndAdd') {
                                return $this->redirect($this->generateUrl('admin_add_edit_match_route'));
                            } elseif ($response == 'save') {
                                return $this->redirect($cancel);
                            } else {
                                //die('name: ' . $form->getData()->getVisitor());
                            }
                        } catch (\Exception $e) {
                            $this->get('session')->getFlashBag()->set('error', $e->getMessage());
                        }
                    } else {
                        $form = $this->createForm(MatchType::class, $match);
                    }
                }
            }

            $parameters = [
                'form_model' => $form->createView(),
                'cancel_url' => $cancel,
                'match' => $match,
                'title' => $id == 0 ? $this->get('translator')->trans('match.new') : $this->get('translator')->trans('match.match') . ' ' . $id,
            ];

            return $this->render('match/add_edit.html.twig', $parameters);
        }

        $this->get('session')->getFlashBag()->set('error', $this->get('translator')->trans('error.not_found'));
        return $this->redirect($this->generateUrl('admin_match_route'));
    }

    /**
     *
     * @Route("/admin/matches/{id}/delete/", name="admin_delete_match_route", requirements={"id": "\d+"})
     * @Method({"GET"})
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function deleteAction($id)
    {
        $match = $this->getEntity($id, 'Match');
        if ($match instanceof Match) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($match);
            $em->flush();

            $this->get('session')->getFlashBag()->set('success',
                $this->get('translator')->trans('success.deleted'));
        } else {
            $this->get('session')->getFlashBag()->set('error', 'error.not_found');
        }

        return $this->redirect($this->generateUrl('admin_match_route'));
    }


    /**
     * @param Match $match
     * @param Form $form
     */
    protected function validate(Match $match, Form $form){
        // required championship
        $championship = $match->getChampionship();
        if(!$championship instanceof Championship){
            $form->get('championship')->addError(
                new FormError($this->get('translator')->trans('error.required'))
            );
        }

        // required home and visitor teams
        $home = $match->getHome();
        $visitor = $match->getVisitor();
        if($home instanceof Team && $visitor instanceof Team){
            if($home->isEqual($visitor)) {
                $form->get('home')->addError(
                    new FormError($this->get('translator')->trans('error.same_team'))
                );
                $form->get('visitor')->addError(
                    new FormError($this->get('translator')->trans('error.same_team'))
                );
            }
        } else {
            if (!$home instanceof Team) {
                $form->get('home')->addError(
                    new FormError($this->get('translator')->trans('error.required'))
                );
            }
            if (!$visitor instanceof Team) {
                $form->get('visitor')->addError(
                    new FormError($this->get('translator')->trans('error.required'))
                );
            }
        }
    }


    /**
     * @param Request $request
     * @param array $params
     */
    protected function initDataTable(Request $request, array  $params = []){
        // data table
        $this->dataTable = new DataTable($request, 'admin_match_route', $params);

        // add check all
        $this->dataTable->addCheckAll();

        // add datatable menu
        $this->dataTable->addMenuLink(
            $this->generateUrl('admin_add_edit_match_route'),
            $this->get('translator')->trans('match.add')
        );
        if(!array_key_exists('champ', $params)) {
            if(array_key_exists('teams', $params)){
                $team = $this->getModel($params['team'], 'Team');
                $championships = [];
                if($team instanceof Team){
                    $championships = $team->getChampionships()->toArray();
                }
            } else {
                $championships = $this->getDoctrine()->getRepository('AppBundle:Championship')->findBy(
                    ['deleted' => false],
                    ['name' => 'ASC']
                );
            }
            if (count($championships) > 0) {
                $this->dataTable->addMenuSelect(
                    'championship',
                    EntityUtility::getArrayValues($championships),
                    $this->get('translator')->trans('match.championship')
                );
            }
        }
        $this->dataTable->addMenuSelect(
            'season',
            EntityUtility::getSeasons(2010),
            $this->get('translator')->trans('match.season')
        );
        $this->dataTable->addMenuDropDown(['delete'], $this->get('translator')->trans('admin.button.actions'));

        // add table columns
        $this->dataTable->addLinkColumn('name', false,
            $this->get('translator')->trans('match.match'),
            [
                'route' =>'admin_detail_match_route',
                'params' => ['id']
            ]
        );
        $this->dataTable->addDateTimeColumn('scheduledAt', true,
            $this->get('translator')->trans('match.time'),
            ['class' => 'width-30 hidden-540-down']
        );

        // ordered column
        $this->dataTable->setOrderedColumn('scheduledAt');
        $this->dataTable->setOrderedDirection('desc');

        // add table pagination
        $this->dataTable->addPagination( self::MAX_ITEM_PER_PAGE);
    }
}
