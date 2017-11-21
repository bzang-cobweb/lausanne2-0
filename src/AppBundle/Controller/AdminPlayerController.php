<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Model;
use AppBundle\Entity\Player;
use AppBundle\Entity\Team;
use AppBundle\Form\Type\SearchWordType;
use AppBundle\Lib\DataTable;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use AppBundle\Form\Type\PlayerType;
use Symfony\Component\Form\Form;

class AdminPlayerController extends AdminController
{

    protected $page = 'player';


    /**
     * @Route("/admin/players/", name="admin_player_route")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function listAction(Request $request)
    {
        $this->action = 'list';

        // execute data table actions
        $this->dataTableAction($request, 'AppBundle:Player');

        // init data table elements
        $this->initDataTable($request);

        // get query params in order to filter data from db
        $qParams = $this->dataTable->getQueryParams([
            'name' => ['firstname', 'lastname', 'trigram']
        ]);
        if(!$qParams['success']){
            die($qParams['reason']);
        }

        list($players, $total) = $this->getDoctrine()->getRepository('AppBundle:Player')->findByLike(
            $qParams['data']['criteria'],
            $qParams['data']['orderBy'],
            $qParams['data']['limit'],
            $qParams['data']['offset']
        );

        // load data into data table
        $this->dataTable->setData($players, $total);

        $parameters = [
            'page_route' => 'admin_player_route',
        ];

        return $this->render($request->isXMLHttpRequest() ? 'dataTable/table.html.twig' : 'player/list.html.twig', $parameters);
    }

    /**
     * Add or edit player
     *
     * @Route("/admin/players/{id}/", name="admin_add_edit_player_route", requirements={"id": "\d+"})
     * @Method({"GET","POST"})
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function addOrEditAction(Request $request, $id = 0)
    {
        $player = $id > 0 ? $this->getModel($id, 'Player') : new Player();
        if($player instanceof Player) {
            if($id > 0){
                $cancel = $this->generateUrl('admin_detail_player_route', [
                    'id' => $id,
                    'tab' => $request->get('tab', 0),
                ]);
            } else {
                $cancel = $this->generateUrl('admin_player_route');
            }

            $form = $this->createForm(PlayerType::class, $player);
            if ($request->isMethod('POST')) {
                try {
                    $response = $this->submitModelForm($request, $player, $form);
                    if ($response == 'saveAndAdd') {
                        return $this->redirect($this->generateUrl('admin_add_edit_player_route'));
                    } elseif ($response == 'save') {
                        return $this->redirect($cancel);
                    }
                } catch (UniqueConstraintViolationException $e) {
                    $form->get('trigram')->addError(
                        new FormError($this->get('translator')->trans('error.duplicated'))
                    );
                } catch (\Exception $e) {
                    $this->get('session')->getFlashBag()->set('error', $e->getMessage());
                }
            }

            $parameters = [
                'form_model' => $form->createView(),
                'title' => $id == 0 ? $this->get('translator')->trans('player.new') : $player,
                'cancel_url' => $cancel
            ];

            return $this->render('player/add_edit.html.twig', $parameters);
        }

        $this->get('session')->getFlashBag()->set('error', $this->get('translator')->trans('error.not_found'));
        return $this->redirect($this->generateUrl('admin_player_route'));
    }

    /**
     *
     * @Route("/cwadmin/players/{id}/detail/", name="admin_detail_player_route", requirements={"id": "\d+"})
     * @Method({"GET"})
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function detailAction(Request $request, $id)
    {
        $player = $this->getModel($id, 'Player');
        if($player instanceof Player) {
            return $this->render('player/detail.html.twig', [
                'player' => $player,
                'tab' => $request->get('tab', 0),
                'back_to_list' => $this->generateUrl('admin_player_route'),
                'title' => $player
            ]);
        }

        $this->get('session')->getFlashBag()->set('error', $this->get('translator')->trans('error.not_found'));
        return $this->redirect($this->generateUrl('admin_player_route'));
    }

    /**
     *
     * @Route("/cwadmin/players/{id}/delete/", name="admin_delete_player_route", requirements={"id": "\d+"})
     * @Method({"GET"})
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function deleteAction($id)
    {
        $player = $this->getModel($id, 'Player');
        if($player instanceof Player) {
            $player->setDeleted(true);
            $player->setUpdatedAt(new \DateTime());

            $em = $this->getDoctrine()->getManager();
            $em->persist($player);
            $em->flush();

            $this->get('session')->getFlashBag()->set(
                'success',
                $this->get('translator')->trans('success.deleted')
            );
            return $this->redirect($this->generateUrl('admin_player_route'));
        }

        $this->get('session')->getFlashBag()->set('error', $this->get('translator')->trans('error.not_found'));

        return $this->redirect($this->generateUrl('admin_player_route'));
    }

    /**
     * Add dataTable elements
     *
     * @param Request $request
     */
    protected function initDataTable(Request $request){
        // data table
        $this->dataTable = new DataTable($request, 'admin_player_route');

        // add check all
        $this->dataTable->addCheckAll();

        // add table menu
        $this->dataTable->addMenuLink(
            $this->generateUrl('admin_add_edit_player_route'),
            $this->get('translator')->trans('player.add')
        );
        $this->dataTable->addMenuDropDown(['delete'], $this->get('translator')->trans('admin.button.actions'));

        // add table search form
        $this->dataTable->addSearchForm(['firstname', 'lastname', 'trigram']);

        // add table columns
        $this->dataTable->addLinkColumn('name', true,
            $this->get('translator')->trans('player.name'),
            [
                'route' =>'admin_detail_player_route',
                'params' => ['id']
            ]
        );
        $this->dataTable->addTextColumn('trigram', true,
            $this->get('translator')->trans('player.alias'),
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
