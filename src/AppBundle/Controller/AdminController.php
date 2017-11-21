<?php

namespace AppBundle\Controller;

use AppBundle\Lib\DataTable;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use AppBundle\Entity\Model;
use AppBundle\Entity\User;
use AppBundle\Entity\Player;
use AppBundle\Entity\Team;
use Symfony\Component\Form\Form;


abstract class AdminController extends Controller
{

    /** @var  string  */
    protected $page = '';

    /** @var  string */
    protected $action;

    /** @var string  */
    protected $theme = 'default';

    /** @var   */
    protected $dataTable;

    /** @var bool  */
    protected $ajax = false;

    /** @var array  */
    protected $menu = [];


    const MAX_ITEM_PER_PAGE = 10;






    /**
     * @param string $view
     * @param array $parameters
     * @param Response|null $response
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function render($view, array $parameters = array(), Response $response = null){
        $defaults = array(
            'admin' => $this->getUser(),
            'page' => $this->page,
            'theme' => $this->theme,
            'template_dir' => $this->theme . '/backend/',
        );

        if($this->dataTable instanceof DataTable){
            $defaults['dataTable'] = $this->dataTable;
        }

        if(!$this->ajax){
            $this->loadMenu();
            $defaults['menu'] = $this->menu;
        }

        return parent::render(
            $defaults['template_dir']  . $view,
            array_merge(
                $parameters,
                $defaults
            ),
            $response
        );
    }

    /**
     * @param Request $request
     * @param string $entity
     */
    protected function dataTableAction(Request $request, $entity){
        $action = $request->get('table_group_action', '');
        if($action) {
            $method = 'dataTable' . ucfirst($action) . 'Action';
            if (method_exists($this, $method)) {
                $data = $request->get('table_selected_ids', '');
                if($data) {
                    $ids = json_decode(stripslashes($data));
                    if (is_array($ids) && count($ids) > 0) {
                        if($this->$method(array_map('intval', $ids), $entity)){
                            //$this->get('session')->getFlashBag()->set('success', $this->get('translator')->trans('action.success_' . $action));
                        } else {
                            //$this->get('session')->getFlashBag()->set('error', $this->get('translator')->trans('action.error_' . $action));
                        }
                    }
                }
            }
        }
    }

    /**
     * @param array $ids
     * @param $entity
     * @return bool
     */
    protected function dataTableDeleteAction(array $ids, $entity){
        try{
            return $this->getDoctrine()->getRepository($entity)->delete($ids);
        } catch (\Exception $e){
            return false;
        }
    }

    /**
     * @param Request $request
     * @param Model $model
     * @param Form $form
     * @param bool $handle
     * @return string
     */
    protected function submitModelForm(Request $request, Model $model, Form $form, $handle = true){
        $response = '';

        if($model->getId() > 0){
            $model->setUpdatedAt(new \DateTime());
        }

        if($handle) {
            $form->handleRequest($request);
        }

        if(method_exists($this, 'validate')){
            $this->validate($model, $form);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($model);
            $em->flush();

            $this->get('session')->getFlashBag()->set('success', $this->get('translator')->trans('success.saved'));

            if($form->get('save')->isClicked()){
                $response = 'save';
            } else {
                $response = 'saveAndAdd';
            }
        }

        return $response;
    }

    /**
     * @param $id
     * @param $className
     * @return object
     */
    protected function getModel($id, $className){
        $model = null;
        if($id > 0){
            $model = $this->getDoctrine()->getRepository('AppBundle:' . $className)->findOneBy(
                ['id' => $id, 'deleted' => false]
            );
        }

        return $model;
    }

    /**
     * @param $id
     * @param $className
     * @return object
     */
    protected function getEntity($id, $className){
        $entity = null;
        if($id > 0){
            $entity = $this->getDoctrine()->getRepository('AppBundle:' . $className)->find($id);
        }

        return $entity;
    }

    /**
     *
     */
    protected function loadMenu(){
        $this->menu['main'][0] = [
            'match' => [
                'route' => 'admin_add_edit_match_route',
                'label' => 'match.add',
                'class' => 'btn btn-md btn-default m-4'
            ]
        ];
        $this->menu['main'][1] = [
            'home' => [
                'route' => 'admin_home_route',
                'label' => 'label.home',
                'class' => $this->page == 'home' || $this->page == '' ? 'active' : '',
                'icon' => 'home',
            ],
            'match' => [
                'route' => 'admin_match_route',
                'label' => 'label.matches',
                'class' => $this->page == 'match' ? 'active' : '',
                'icon' => 'futbol-o',
            ],
            'news' => [
                'route' => 'admin_news_route',
                'label' => 'label.news',
                'class' => $this->page == 'news' ? 'active' : '',
                'icon' => 'newspaper-o',
            ],
            'championship' => [
                'route' => 'admin_championship_route',
                'label' => 'label.championships',
                'class' => $this->page == 'championship' ? 'active' : '',
                'icon' => 'trophy',
            ],
            'team' => [
                'route' => 'admin_team_route',
                'label' => 'label.teams',
                'class' => $this->page == 'team' ? 'active' : '',
                'icon' => 'group',
            ],
            'player' => [
                'route' => 'admin_player_route',
                'label' => 'label.players',
                'class' => $this->page == 'player' ? 'active' : '',
                'icon' => 'male',
            ]
        ];

        $this->menu['sub'][] = [
            'profile' => [
                'route' => 'admin_profile_route',
                'label' => 'label.my_account',
                'class' => $this->page == 'profile' ? 'active' : '',
                'icon' => 'cog',
            ],
            'sign-out' => [
                'route' => 'admin_logout_route',
                'label' => 'button.sign_out',
                'icon' => 'sign-out',
            ],
        ];
    }
}
