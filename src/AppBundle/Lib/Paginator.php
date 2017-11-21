<?php
/**
 * Created by PhpStorm.
 * User: bzang
 * Date: 04/02/16
 * Time: 22:10
 */

namespace AppBundle\Lib;


class Paginator
{
    /**
     * @var float
     */
    private $totalPages;

    /**
     * @var
     */
    private $page;

    /**
     * @var
     */
    private $maxItemPerPage;


    /**
     * Paginator constructor.
     * @param $page
     * @param $totalcount
     * @param $maxItemPerPage
     */
    public function __construct($page, $totalcount, $maxItemPerPage = 10)
    {
        $this->rpp = $maxItemPerPage;
        $this->page = $page;

        $this->totalPages = $this->setTotalPages($totalcount, $maxItemPerPage);
    }

    /**
     * @param $totalcount
     * @param $maxItemPerPage
     * @return float
     */
    private function setTotalPages($totalcount, $maxItemPerPage)
    {
        // In case we did not provide a number for $maxItemPerPage
        if ($maxItemPerPage == 0){
            $maxItemPerPage = 20;
        }

        $this->totalPages = ceil($totalcount / $maxItemPerPage);
        return $this->totalPages;
    }

    /**
     * @return float
     */
    public function getTotalPages()
    {
        return $this->totalPages;
    }

    /**
     * @return array
     */
    public function getPagesList()
    {
        $pageCount = 5;
        // Less than total 5 pages
        if ($this->totalPages <= $pageCount){
            $r = array();
            for($i = 1; $i <= $this->totalPages; $i++){
                $r[] = $i;
            }
            return $r;
        }

        if($this->page <= 3)
            return array(1,2,3,4,5);

        $i = $pageCount;
        $r = array();
        $half = floor($pageCount / 2);
        if ($this->page + $half > $this->totalPages){
            while ($i >= 1){
                $r[] = $this->totalPages - $i + 1;
                $i--;
            }
            return $r;
        } else {
            while ($i >= 1){
                $r[] = $this->page - $i + $half + 1;
                $i--;
            }
            return $r;
        }
    }

    public function getPages(){
        $pages = $this->getPagesList();

        $entries = array();
        $entries[] = $this->previous();

        foreach($pages as $page){
            $entries[] = $this->entry($page);
        }

        $entries[] = $this->next();

        return $entries;
    }

    public function entry($number){
        $page = array(
            'label' => $number
        );

        if($this->page == $number){
            $page['active'] = $number;
        } else {
            $page['number'] = $number;
        }

        return $page;
    }

    public function previous($label = '&laquo;'){
        $page = array(
            'label' => $label
        );

        if($this->page > 1){
            $page['number'] = $this->page - 1;
        }

        return $page;
    }

    public function next($label = '&raquo;'){
        $page = array(
            'label' => $label
        );

        if($this->page < $this->totalPages){
            $page['number'] = $this->page + 1;
        }

        return $page;
    }

    public function getPage(){
        return $this->page;
    }

    public function getMaxItemPerPage(){
        return $this->getMaxItemPerPage();
    }
}