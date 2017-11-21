<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Championship;
use AppBundle\Entity\News;
use AppBundle\Form\Type\SearchWordType;
use AppBundle\Lib\DataTable;
use AppBundle\Utility\EntityUtility;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Form\Type\NewsType;
use Symfony\Component\Form\Form;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class AdminNewsController extends AdminController
{

    protected $page = 'news';


    /**
     * @Route("/admin/news/", name="admin_news_route")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function listAction(Request $request)
    {
        $this->action = 'list';

        // execute data table actions
        $this->dataTableAction($request, 'AppBundle:News');

        $championshipId = $request->get('champ', 0);

        // init data table elements
        $this->initDataTable($request, $championshipId);

        // get query params in order to filter data from db
        $qParams = $this->dataTable->getQueryParams();
        if(!$qParams['success']){
            die($qParams['reason']);
        }

        if($championshipId > 0){
            $qParams['data']['criteria']['championship'] = $championshipId;
        }

        list($news, $total) = $this->getDoctrine()->getRepository('AppBundle:News')->findByLike(
            $qParams['data']['criteria'],
            $qParams['data']['orderBy'],
            $qParams['data']['limit'],
            $qParams['data']['offset']
        );

        // load data into data table
        $this->dataTable->setData($news, $total);

        $parameters = [
            'page_route' => 'admin_news_route',
        ];

        return $this->render($request->isXMLHttpRequest() ? 'dataTable/table.html.twig' : 'news/list.html.twig', $parameters);
    }

    /**
     * Add or edit news
     *
     * @Route("/admin/news/{id}/", name="admin_add_edit_news_route", requirements={"id": "\d+"})
     * @Method({"GET","POST"})
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function addOrEditAction(Request $request, $id = 0)
    {
        $news = $id > 0 ? $this->getModel($id, 'News') : new News();
        if($news instanceof News) {
            if($id > 0){
                $cancel = $this->generateUrl('admin_detail_news_route', [
                    'id' => $id,
                    'tab' => $request->get('tab', 0),
                ]);
            } else {
                $cancel = $this->generateUrl('admin_news_route');
            }

            $form = $this->createForm(NewsType::class, $news);
            if ($request->isMethod('POST')) {
                try {
                    $news->setAuthor($this->getUser());
                    $response = $this->submitModelForm($request, $news, $form);
                    if ($response == 'saveAndAdd') {
                        return $this->redirect($this->generateUrl('admin_add_edit_news_route'));
                    } elseif ($response == 'save') {
                        return $this->redirect($cancel);
                    }
                } catch (\Exception $e) {
                    $this->get('session')->getFlashBag()->set('error', $e->getMessage());
                }
            }

            $parameters = [
                'form_model' => $form->createView(),
                'title' => $id == 0 ? $this->get('translator')->trans('news.new') : $this->get('translator')->trans('label.news') . ' ' . $id,
                'cancel_url' => $cancel
            ];

            return $this->render('news/add_edit.html.twig', $parameters);
        }

        $this->get('session')->getFlashBag()->set('error', $this->get('translator')->trans('error.not_found'));
        return $this->redirect($this->generateUrl('admin_news_route'));
    }

    /**
     *
     * @Route("/cwadmin/news/{id}/detail/", name="admin_detail_news_route", requirements={"id": "\d+"})
     * @Method({"GET"})
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function detailAction(Request $request, $id)
    {
        $news = $this->getModel($id, 'News');
        if($news instanceof News) {
            return $this->render('news/detail.html.twig', [
                'news' => $news,
                'tab' => $request->get('tab', 0),
                'back_to_list' => $this->generateUrl('admin_news_route'),
                'title' => $this->get('translator')->trans('label.news') . ' ' . $id
            ]);
        }

        $this->get('session')->getFlashBag()->set('error', $this->get('translator')->trans('error.not_found'));
        return $this->redirect($this->generateUrl('admin_news_route'));
    }

    /**
     * @param Request $request
     * @param int $championshipId
     */
    protected function initDataTable(Request $request, $championshipId = 0){
        // data table
        if($championshipId > 0){
            $this->dataTable = new DataTable($request, 'admin_news_route', [
                'champ' => $championshipId
            ]);
        } else {
            $this->dataTable = new DataTable($request, 'admin_news_route');
        }

        // add check all
        $this->dataTable->addCheckAll();

        // add table menu
        $this->dataTable->addMenuLink(
            $this->generateUrl('admin_add_edit_news_route'),
            $this->get('translator')->trans('news.add')
        );

        if($championshipId == 0) {
            $championships = $this->getDoctrine()->getRepository('AppBundle:Championship')->findBy(
                ['deleted' => false],
                ['name' => 'ASC']
            );
            if (count($championships) > 0) {
                $this->dataTable->addMenuSelect(
                    'championship',
                    EntityUtility::getArrayValues($championships),
                    $this->get('translator')->trans('match.championship')
                );
            }
        }

        $this->dataTable->addMenuDropDown(['delete'], $this->get('translator')->trans('admin.button.actions'));

        // add table search form
        $this->dataTable->addSearchForm(['title', 'teaser']);

        // add table columns
        $this->dataTable->addLinkColumn('title', true,
            $this->get('translator')->trans('label.title'),
            [
                'route' =>'admin_detail_news_route',
                'params' => ['id']
            ]
        );
        $this->dataTable->addDateColumn('createdAt', true,
            $this->get('translator')->trans('label.since'),
            ['class' => 'width-30']
        );
        $this->dataTable->addDateColumn('updatedAt', true,
            $this->get('translator')->trans('label.updated'),
            ['class' => 'width-30 hidden-762-down']
        );

        // ordered column
        $this->dataTable->setOrderedColumn('createdAt');
        $this->dataTable->setOrderedDirection('desc');

        // add table pagination
        $this->dataTable->addPagination();
    }

    /**
     * @param News $news
     * @param Form $form
     */
    protected function validate(News $news, Form $form){
        // required championship
        if(!$news->getChampionship() instanceof Championship){
            $form->get('championship')->addError(
                new FormError($this->get('translator')->trans('error.required'))
            );
        }
    }
}
