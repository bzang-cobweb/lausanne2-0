<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Championship;
use AppBundle\Entity\Match;
use AppBundle\Entity\Player;
use AppBundle\Entity\Team;
use AppBundle\Lib\DataTable;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use AppBundle\Form\Type\MatchType;
use Symfony\Component\Form\Form;

class AdminTeamPlayerController extends AdminController
{
    /**
     * @Route("/admin/teams/{id}/players/", name="admin_tp_player_route", requirements={"id": "\d+"})
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function playerAction(Request $request, $id)
    {
        $team = $this->getModel($id, 'Team');
        if(!$team instanceof Team){
            $this->get('session')->getFlashBag()->set('error', 'error.not_found');
            if($request->isXMLHttpRequest()){
                return;
            }
            return $this->redirect($this->generateUrl('admin_team_route'));
        }

        $this->page = 'team';
        $this->action = 'player';

        // execute data table actions
        $this->dataTableAction($request, 'AppBundle:TeamPlayer');

        // init data table elements
        $this->initDataTable($request, $id);

        // get query params in order to filter data from db
        $qParams = $this->dataTable->getQueryParams([
            'playerName' => ['p.firstname', 'p.lastname']
        ]);
        if(!$qParams['success']){
            die($qParams['reason']);
        }
        $qParams['data']['criteria']['team'] = $id;

        list($players, $total) = $this->getDoctrine()->getRepository('AppBundle:TeamPlayer')->findByLike(
            $qParams['data']['criteria'],
            $qParams['data']['orderBy'],
            $qParams['data']['limit'],
            $qParams['data']['offset']
        );

        // load data into data table
        $this->dataTable->setData($players, $total);

        $parameters = [
            'page_route' => 'admin_tp_player_route',
        ];

        return $this->render($request->isXMLHttpRequest() ? 'dataTable/table.html.twig' : 'team/players.html.twig', $parameters);
    }

    /**
     * @Route("/admin/players/{id}/teams/", name="admin_tp_team_route", requirements={"id": "\d+"})
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function championshipAction(Request $request, $id)
    {
        $player = $this->getModel($id, 'Player');
        if(!$player instanceof Player){
            $this->get('session')->getFlashBag()->set('error', 'error.not_found');
            if($request->isXMLHttpRequest()){
                return;
            }
            return $this->redirect($this->generateUrl('admin_player_route'));
        }

        $this->page = 'player';
        $this->action = 'team';

        // execute data table actions
        $this->dataTableAction($request, 'AppBundle:TeamPlayer');

        // init data table elements
        $this->initDataTable($request, $id);

        // get query params in order to filter data from db
        $qParams = $this->dataTable->getQueryParams([
            'teamName' => ['t.name']
        ]);
        if(!$qParams['success']){
            die($qParams['reason']);
        }
        $qParams['data']['criteria']['player'] = $id;

        list($teams, $total) = $this->getDoctrine()->getRepository('AppBundle:TeamPlayer')->findByLike(
            $qParams['data']['criteria'],
            $qParams['data']['orderBy'],
            $qParams['data']['limit'],
            $qParams['data']['offset']
        );

        // load data into data table
        $this->dataTable->setData($teams, $total);

        $parameters = [
            'page_route' => 'admin_ct_championship_route',
        ];

        return $this->render($request->isXMLHttpRequest() ? 'dataTable/table.html.twig' : 'team/championships.html.twig', $parameters);
    }

    /**
     * @param Request $request
     * @param int $id
     */
    protected function initDataTable(Request $request, $id){
        $route = $this->action == 'team' ? 'admin_tp_team_route' : 'admin_tp_player_route';
        // data table
        $this->dataTable = new DataTable($request, $route, ['id' => $id]);

        // add check all
        $this->dataTable->addCheckAll();

        // add table menu
        $this->dataTable->addMenuDropDown(['delete'], $this->get('translator')->trans('admin.button.actions'));

        // add table search form
        if($this->action == 'team'){
            $this->dataTable->addSearchForm(['t.name', 't.trigram']);
        } else {
            $this->dataTable->addSearchForm(['p.firstname', 'p.lastname', 'p.trigram']);
        }


        // add table columns
        $route = $this->action == 'team' ? 'admin_detail_team_route' : 'admin_detail_player_route';
        $this->dataTable->addLinkColumn($this->action . 'Name', true,
            $this->get('translator')->trans('label.name'),
            [
                'route' => $route,
                'params' => ['id' => $this->action]
            ]
        );

        $this->dataTable->addDateTimeColumn('createdAt', true,
            $this->get('translator')->trans('label.since'),
            ['class' => 'width-30 hidden-540-down']
        );

        // ordered column
        $this->dataTable->setOrderedColumn($this->action . 'Name');
        $this->dataTable->setOrderedDirection('asc');

        // add table pagination
        $this->dataTable->addPagination( self::MAX_ITEM_PER_PAGE);
    }
}
