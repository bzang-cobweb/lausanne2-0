<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Championship;
use AppBundle\Lib\DataTable;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use AppBundle\Form\Type\ChampionshipType;
use Symfony\Component\Form\Form;

class AdminChampionshipController extends AdminController
{

    protected $page = 'championship';


    /**
     * @Route("/admin/championships/", name="admin_championship_route")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function listAction(Request $request)
    {
        $this->action = 'list';

        // execute data table actions
        $this->dataTableAction($request, 'AppBundle:Championship');

        // init data table elements
        $this->initDataTable($request);

        // get query params in order to filter data from db
        $qParams = $this->dataTable->getQueryParams();
        if(!$qParams['success']){
            die($qParams['reason']);
        }

        list($championships, $total) = $this->getDoctrine()->getRepository('AppBundle:Championship')->findByLike(
            $qParams['data']['criteria'],
            $qParams['data']['orderBy'],
            $qParams['data']['limit'],
            $qParams['data']['offset']
        );

        // load data into data table
        $this->dataTable->setData($championships, $total);

        $parameters = [
            'page_route' => 'admin_championship_route',
        ];

        return $this->render($request->isXMLHttpRequest() ? 'dataTable/table.html.twig' : 'championship/list.html.twig', $parameters);
    }

    /**
     *
     * @Route("/cwadmin/championships/{id}/detail/", name="admin_detail_championship_route", requirements={"id": "\d+"})
     * @Method({"GET"})
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function detailAction(Request $request, $id)
    {
        $championship = $this->getModel($id, 'Championship');
        if($championship instanceof Championship) {
            return $this->render('championship/detail.html.twig', [
                'championship' => $championship,
                'tab' => $request->get('tab', 0),
                'back_to_list' => $this->generateUrl('admin_championship_route'),
                'title' => $championship
            ]);
        }

        $this->get('session')->getFlashBag()->set('error', 'error.not_found');
        return $this->redirect($this->generateUrl('admin_championship_route'));
    }

    /**
     * Add or edit championship
     *
     * @Route("/admin/championships/{id}/", name="admin_add_edit_championship_route", requirements={"id": "\d+"})
     * @Method({"GET","POST"})
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function addOrEditAction(Request $request, $id = 0)
    {
        $championship = $id > 0 ? $this->getModel($id, 'Championship') : new Championship();
        if($championship instanceof Championship) {
            if ($id > 0) {
                $cancel = $this->generateUrl('admin_detail_championship_route', [
                    'id' => $id,
                    'tab' => $request->get('tab', 0)
                ]);
            } else {
                $cancel = $this->generateUrl('admin_championship_route');
            }

            $form = $this->createForm(ChampionshipType::class, $championship);
            if ($request->isMethod('POST')) {
                try {
                    $response = $this->submitModelForm($request, $championship, $form);
                    if ($response == 'saveAndAdd') {
                        return $this->redirect($this->generateUrl('admin_add_edit_championship_route'));
                    } elseif ($response == 'save') {
                        return $this->redirect($cancel);
                    }
                } catch (UniqueConstraintViolationException $e) {
                    $form->get('name')->addError(
                        new FormError($this->get('translator')->trans('error.duplicated'))
                    );
                } catch (\Exception $e) {
                    $this->get('session')->getFlashBag()->set('error', $e->getMessage());
                }
            }

            $parameters = [
                'form_model' => $form->createView(),
                'title' => $id == 0 ? $this->get('translator')->trans('championship.new') : $championship,
                'cancel_url' => $cancel
            ];

            return $this->render('championship/add_edit.html.twig', $parameters);
        }

        $this->get('session')->getFlashBag()->set('error', $this->get('translator')->trans('error.not_found'));
        return $this->redirect($this->generateUrl('admin_championship_route'));
    }

    /**
     *
     * @Route("/cwadmin/championships/{id}/delete/", name="admin_delete_championship_route", requirements={"id": "\d+"})
     * @Method({"GET"})
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function deleteAction($id)
    {
        $championship = $this->getModel($id, 'Championship');
        if ($championship instanceof Championship) {
            $championship->setDeleted(true);
            $championship->setUpdatedAt(new \DateTime());

            $em = $this->getDoctrine()->getManager();
            $em->persist($championship);
            $em->flush();

            $this->get('session')->getFlashBag()->set('success',
                $this->get('translator')->trans('success.deleted'));
        }

        $this->get('session')->getFlashBag()->set('error', 'error.not_found');
        return $this->redirect($this->generateUrl('admin_championship_route'));
    }


    /**
     * Add dataTable elements
     *
     * @param Request $request
     */
    protected function initDataTable(Request $request){
        // data table
        $this->dataTable = new DataTable($request, 'admin_championship_route');

        // add check all
        $this->dataTable->addCheckAll();

        // add table menu
        $this->dataTable->addMenuLink(
            $this->generateUrl('admin_add_edit_championship_route'),
            $this->get('translator')->trans('championship.add')
        );
        $this->dataTable->addMenuDropDown(['delete'], $this->get('translator')->trans('admin.button.actions'));

        // add table search form
        $this->dataTable->addSearchForm(['name']);

        // add table columns
        $this->dataTable->addLinkColumn('name', true,
            $this->get('translator')->trans('championship.name'),
            [
                'route' =>'admin_detail_championship_route',
                'params' => ['id']
            ]
        );
        $this->dataTable->addDateColumn('createdAt', true,
            $this->get('translator')->trans('label.since'),
            ['class' => 'width-30 hidden-540-down']
        );

        // ordered column
        $this->dataTable->setOrderedColumn('name');
        $this->dataTable->setOrderedDirection('asc');

        // add table pagination
        $this->dataTable->addPagination( self::MAX_ITEM_PER_PAGE);
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
                $championship = $this->getModel($id, 'Championship');
                if($championship instanceof Championship){
                    $championship->setDeleted(true);
                    $championship->setUpdatedAt(new \DateTime());
                    $em->persist($championship);
                    $em->flush();
                }
            }
            return true;
        } catch (\Exception $e){
            return false;
        }
    }
}
