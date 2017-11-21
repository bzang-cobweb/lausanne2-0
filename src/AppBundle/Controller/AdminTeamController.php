<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Team;
use AppBundle\Lib\DataTable;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use AppBundle\Form\Type\TeamType;
use Symfony\Component\Form\Form;

class AdminTeamController extends AdminController
{

    protected $page = 'team';


    /**
     * @Route("/admin/teams/", name="admin_team_route")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function listAction(Request $request)
    {
        $this->action = 'list';

        // execute data table actions
        $this->dataTableAction($request, 'AppBundle:Team');

        // init data table elements
        $this->initDataTable($request);

        // get query params in order to filter data from db
        $qParams = $this->dataTable->getQueryParams();
        if(!$qParams['success']){
            die($qParams['reason']);
        }

        // data from db
        list($teams, $total) = $this->getDoctrine()->getRepository('AppBundle:Team')->findByLike(
            $qParams['data']['criteria'],
            $qParams['data']['orderBy'],
            $qParams['data']['limit'],
            $qParams['data']['offset']
        );

        // load data into data table
        $this->dataTable->setData($teams, $total);

        $parameters = [
            'page_route' => 'admin_team_route',
        ];

        return $this->render($request->isXMLHttpRequest() ? 'dataTable/table.html.twig' : 'team/list.html.twig', $parameters);
    }

    /**
     * Add or edit team
     *
     * @Route("/admin/teams/{id}/", name="admin_add_edit_team_route", requirements={"id": "\d+"})
     * @Method({"GET","POST"})
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function addOrEditAction(Request $request, $id = 0)
    {
        $team = $id > 0 ? $this->getModel($id, 'Team') : new Team();
        if($team instanceof Team) {
            if($id > 0){
                $cancel = $this->generateUrl('admin_detail_team_route', [
                    'id' => $id,
                    'tab' => $request->get('tab', 0)
                ]);
            } else {
                $cancel = $this->generateUrl('admin_team_route');
            }

            $form = $this->createForm(TeamType::class, $team);
            if ($request->isMethod('POST')) {
                try {
                    $response = $this->submitModelForm($request, $team, $form);
                    if ($response == 'saveAndAdd') {
                        return $this->redirect($this->generateUrl('admin_add_edit_team_route'));
                    } elseif ($response == 'save') {
                        return $this->redirect($cancel);
                    }
                } catch (UniqueConstraintViolationException $e) {
                    $message = $e->getMessage();
                    if (strpos($message, 'uniqueTeamTrigram') !== false) {
                        $form->get('trigram')->addError(
                            new FormError($this->get('translator')->trans('error.duplicated'))
                        );
                    }
                    if (strpos($message, 'uniqueTeamName') !== false) {
                        $form->get('name')->addError(
                            new FormError($this->get('translator')->trans('error.duplicated'))
                        );
                    }
                } catch (\Exception $e) {
                    $this->get('session')->getFlashBag()->set('error', $e->getMessage());
                }
            }

            $parameters = [
                'form_model' => $form->createView(),
                'title' => $id == 0 ? $this->get('translator')->trans('team.new') : $team,
                'cancel_url' => $cancel
            ];

            return $this->render('team/add_edit.html.twig', $parameters);
        }

        $this->get('session')->getFlashBag()->set('error', $this->get('translator')->trans('error.not_found'));
        return $this->redirect($this->generateUrl('admin_team_route'));
    }

    /**
     *
     * @Route("/cwadmin/teams/{id}/detail/", name="admin_detail_team_route", requirements={"id": "\d+"})
     * @Method({"GET"})
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function detailAction(Request $request, $id)
    {
        $team = $this->getModel($id, 'Team');
        if($team instanceof Team) {
            return $this->render('team/detail.html.twig', [
                'team' => $team,
                'tab' => $request->get('tab', 0),
                'back_to_list' => $this->generateUrl('admin_team_route'),
                'title' => $team
            ]);
        }

        $this->get('session')->getFlashBag()->set('error', 'error.not_found');
        return $this->redirect($this->generateUrl('admin_team_route'));
    }

    /**
     *
     * @Route("/cwadmin/teams/{id}/delete/", name="admin_delete_team_route", requirements={"id": "\d+"})
     * @Method({"GET"})
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function deleteAction($id)
    {
        $team = $this->getModel($id, 'Team');
        if($team instanceof Team) {
            $team->setDeleted(true);
            $team->setUpdatedAt(new \DateTime());

            $em = $this->getDoctrine()->getManager();
            $em->persist($team);
            $em->flush();

            $this->get('session')->getFlashBag()->set('success',
                $this->get('translator')->trans('success.deleted'));
            return $this->redirect($this->generateUrl('admin_team_route'));
        }

        $this->get('session')->getFlashBag()->set('error', 'error.not_found');
        return $this->redirect($this->generateUrl('admin_team_route'));
    }

    /**
     * @param array $ids
     * @param $entity
     * @return bool
     */
    protected function dataTableDeleteAction(array $ids, $entity){
        try{
            $delete = is_array($ids) ? $ids : [$ids];
            $em = $this->getDoctrine()->getManager();
            foreach ($delete as $id){
                $team = $this->getModel($id, 'Team');
                if($team instanceof Team){
                    $team->setDeleted(true);
                    $team->setUpdatedAt(new \DateTime());
                    $em->persist($team);
                    $em->flush();
                }
            }
            return true;
        } catch (\Exception $e){
            return false;
        }
    }

    /**
     * Add dataTable elements
     *
     * @param Request $request
     */
    protected function initDataTable(Request $request){
        // data table
        $this->dataTable = new DataTable($request, 'admin_team_route');

        // add check all
        $this->dataTable->addCheckAll();

        // add table menu
        $this->dataTable->addMenuLink(
            $this->generateUrl('admin_add_edit_team_route'),
            $this->get('translator')->trans('team.add')
        );
        $this->dataTable->addMenuDropDown(['delete'], $this->get('translator')->trans('admin.button.actions'));

        // add table search form
        $this->dataTable->addSearchForm(['name', 'trigram']);

        // add table columns
        $this->dataTable->addLinkColumn('name', true,
            $this->get('translator')->trans('team.name'),
            [
                'route' =>'admin_detail_team_route',
                'params' => ['id']
            ]
        );
        $this->dataTable->addTextColumn('trigram', true,
            $this->get('translator')->trans('team.alias'),
            ['class' => 'width-30 hidden-540-down']
        );
        $this->dataTable->addDateColumn('createdAt', true,
            $this->get('translator')->trans('label.since'),
            ['class' => 'width-30 hidden-762-down']
        );

        // ordered column
        $this->dataTable->setOrderedColumn('name');
        $this->dataTable->setOrderedDirection('asc');

        // add table pagination
        $this->dataTable->addPagination( self::MAX_ITEM_PER_PAGE);
    }
}
