<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Championship;
use AppBundle\Entity\Season;
use AppBundle\Entity\Setting;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\Model;
use AppBundle\Entity\User;


abstract class FrontController extends Controller
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

    /** @var  string */
    protected $title;

    /** @var  string */
    protected $metaKeys;

    /** @var  string */
    protected $metaDesc;


    const MAX_ITEM_PER_PAGE = 10;


    /**
     * @param string $view
     * @param array $parameters
     * @param Response|null $response
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function render($view, array $parameters = [], Response $response = null){
        $defaults = array(
            'page' => $this->page,
            'theme' => $this->theme,
            'template_dir' => $this->theme . '/frontend/',
            'color' => 'red'
        );

        $color = $this->getDoctrine()->getRepository('AppBundle:Setting')->findOneBy([
            'name' => 'website_color'
        ]);
        if($color instanceof Setting){
            $defaults['color'] = $color->getValue();
        }

        if(!$this->ajax){
            $defaults['meta']['title'] = $this->title ? $this->title : $this->get('translator')->trans('meta.title');
            $defaults['meta']['keys'] = $this->get('translator')->trans('meta.keys');
            if($this->metaKeys){
                $defaults['meta']['keys'] .= ', ' . $this->metaKeys;
            }
            $defaults['meta']['desc'] = $this->get('translator')->trans('meta.desc');
            if($this->metaDesc){
                $defaults['meta']['desc'] .= ', ' . $this->metaDesc;
            }

            $defaults['main_menu']['championships'] = $this->getChampionships();
            $defaults['main_menu']['season'] = $this->getCurrentSeason();
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
     * @return string
     */
    protected function getCurrentSeason(){
        if((int)date('m') > 5){
            return date('Y') . '-' . date('Y', strtotime('+1 year'));
        }
        return date('Y', strtotime('-1 year')) . '-' . date('Y');
    }

    /**
     * @param $championship
     * @return array
     */
    protected function getSeasons($championship){
        if(!$championship instanceof Championship) {
            $championship = $this->getDoctrine()->getRepository('AppBundle:Championship')->findOneBy([
                'id' => $championship,
                'deleted' => false
            ]);

            if (!$championship instanceof Championship) {
                return [];
            }
        }

        $month = (int)$championship->getCreatedAt()->format('m');
        if($month > 5){
            $year = (int)$championship->getCreatedAt()->format('Y');
        } else {
            $year = (int)$championship->getCreatedAt()->format('Y') - 1;
        }

        $currentMonth = (int)date('m');
        if($currentMonth > 5){
            $currentYear = (int)date('Y');
        } else {
            $currentYear = (int)date('Y') - 1;
        }

        $seasons = [];
        while($year <= $currentYear){
            $seasons[] = $year . '-' . ++$year;
            if($year == $currentYear && $currentMonth <= 5){
                break;
            }
        }

        return array_reverse($seasons);
    }

    /**
     * @return array
     */
    protected function getChampionships(){
        return $this->getDoctrine()->getRepository('AppBundle:Championship')->findBy(
            [
                'deleted' => false,
            ],
            [
                'highlighted' => 'DESC',
                'updatedAt' => 'DESC',
                'createdAt' => 'DESC',
                'id' => 'DESC',
            ]
        );
    }
}
