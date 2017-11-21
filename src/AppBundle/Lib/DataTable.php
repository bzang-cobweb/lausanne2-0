<?php
/**
 * Created by PhpStorm.
 * User: bzang
 * Date: 04/02/16
 * Time: 22:10
 */

namespace AppBundle\Lib;


use AppBundle\Entity\Entity;
use AppBundle\Entity\Model;
use Symfony\Component\HttpFoundation\Request;

class DataTable
{
    /** @var array $data */
    protected $data = [];

    /** @var array $table */
    protected $table = [];

    /** @var array $router */
    protected $router;

    /** @var Request $request */
    protected $request;


    /**
     * DataTable constructor.
     * @param Request $request
     * @param $route
     * @param array $params
     */
    public function __construct(Request $request, $route, array $params = [])
    {
        $this->request = $request;
        $this->router = [
            'route' => $route,
            'params' => $params
        ];
    }

    /**
     * add checkbox column in order to select table rows
     *
     */
    public function addCheckAll(){
        $this->table['checkAll'] = true;
    }

    /**
     * @param string $key
     * @param boolean $sortable
     * @param string $label
     * @param array $router
     * @param array $params
     */
    public function addLinkColumn($key, $sortable, $label, $router = [], $params = []){
        $this->table['fields'][$key] = [
            'sortable' => $sortable,
            'type' => 'link',
            'label' => $label,
            'router' => $router,
            'params' => $params
        ];
    }

    /**
     * @param string $key
     * @param boolean $sortable
     * @param string $label
     * @param array $params
     */
    public function addTextColumn($key, $sortable, $label, $params = []){
        $this->table['fields'][$key] = [
            'sortable' => $sortable,
            'type' => 'text',
            'label' => $label,
            'params' => $params
        ];

    }

    /**
     * @param $key
     * @param $sortable
     * @param $label
     * @param array $params
     */
    public function addDateColumn($key, $sortable, $label, $params = []){
        $this->table['fields'][$key] = [
            'sortable' => $sortable,
            'type' => 'date',
            'label' => $label,
            'params' => $params
        ];
    }

    public function addDateTimeColumn($key, $sortable, $label, $params = []){
        $this->table['fields'][$key] = [
            'sortable' => $sortable,
            'type' => 'datetime',
            'label' => $label,
            'params' => $params
        ];
    }

    /**
     * @param $key
     * @param string $title
     * @param array $params
     */
    public function addCheckboxColumn($key, $title = '', $params = []){
        $this->table['fields'][$key]['params'] = $params;
        $this->table['fields'][$key]['type'] = 'checkbox';
        $this->table['fields'][$key]['title'] = $title;
    }

    /**
     * @param string $label
     * @param string $link
     */
    public function addMenuLink($link, $label = '')
    {
        $this->table['menu'][] = [
            'type' => 'link',
            'link' => $link,
            'label' => $label,
        ];
    }

    /**
     * @param string $label
     * @param array $actions
     */
    public function addMenuDropDown(array $actions, $label = '')
    {
        $this->table['menu'][] = [
            'type' => 'dropdown',
            'label' => $label,
            'actions' => $actions
        ];
    }

    /**
     * @param string $key
     * @param array $items
     * @param string $label
     */
    public function addMenuSelect($key, array $items, $label = '', $default = '')
    {
        $this->table['menu'][] = [
            'type' => 'select',
            'key' => $key,
            'items' => $items,
            'label' => $label,
            'selected' => $this->request->get($key, $default)
        ];
    }

    /**
     * @param array $searchFields
     */
    public function addSearchForm(array $searchFields){
        $this->table['search'] = [
            'type' => 'search',
            'fields' => $searchFields,
            'value' => $this->request->get('search', '')
        ];
    }

    /**
     * @param int $maxItemPerPage
     */
    public function addPagination($maxItemPerPage = 10){
        $this->table['pagination']['page'] = $this->request->get('page', 1);
        $this->table['pagination']['limit'] = $maxItemPerPage;
    }

    /**
     * @param $name
     */
    public function setOrderedColumn($name){
        $this->table['orderedColumn'] = $this->request->get('order', $name);
    }

    /**
     * @param $value
     */
    public function setOrderedDirection($value){
        $this->table['orderedDirection'] = $this->request->get('dir', $value);
        if (!in_array($this->table['orderedDirection'], ['asc', 'desc'])) {
            $this->table['orderedDirection'] = 'asc';
        }
    }

    /**
     * @param $route
     * @param $params
     */
    public function setRouter($route, $params){
        $this->router = [
            'route' => $route,
            'params' => $params
        ];
    }


    /**
     * @param array $orderMapping
     * @return array
     */
    public function getQueryParams(array $orderMapping = []){
        $filter = [];
        if(isset($this->table['orderedColumn'])){
            $column = $this->table['orderedColumn'];
            if($this->isSortableColumn($column)) {
                $dir = isset($this->table['orderedDirection']) ? $this->table['orderedDirection'] : 'asc';
                if (!in_array($dir, ['asc', 'desc'])) {
                    return [
                        'success' => false,
                        'reason' => 'Order direction [' . $filter['dir'] . '] must be [asc, desc]'
                    ];
                }

                if(array_key_exists($column, $orderMapping)){
                    foreach ($orderMapping[$column] as $field){
                        $filter['orderBy'][$field] = strtoupper($dir);
                    }
                } else {
                    $filter['orderBy'][$column] =  strtoupper($dir);
                }
            } else {
                return [
                    'success' => false,
                    'reason' => 'Column [' . $this->table['orderedColumn'] . '] is not sortable'
                ];
            }
        }

        if(isset($this->table['search']['value']) && $this->table['search']['value']){
            $filter['criteria']['search'] = [
                'fields' => $this->table['search']['fields'],
                'text' => $this->table['search']['value']
            ];
        }

        if(isset($this->table['menu'])){
            $menus = $this->table['menu'];
            if(is_array($menus)){
                foreach ($menus as $menu){
                    if(isset($menu['type']) && in_array($menu['type'], ['select'])){
                        $value = $menu['selected'];
                        if($value){
                            $filter['criteria'][$menu['key']] = $value;
                        }
                    }
                }
            }
        }

        if(isset($this->table['pagination'])){
            $filter['limit'] = $this->table['pagination']['limit'];
            $filter['page'] = $this->table['pagination']['page'];
            $filter['offset'] = $filter['page'] > 1 ? ($filter['page'] - 1) * $filter['limit'] : 0;
        }

        $filter['criteria']['deleted'] = false;

        return [
            'success' => true,
            'data' => $filter
        ];
    }

    /**
     * @param $columnName
     * @return bool
     */
    protected function isSortableColumn($columnName){
        if(isset($this->table['fields']) && is_array($this->table['fields'])){
            foreach ($this->table['fields'] as $key => $params){
                if($key == $columnName){
                    return isset($params['sortable']) && $params['sortable'];
                }
            }
        }

        return false;
    }


    /**
     * @param $total
     */
    public function setPaginationTotalItems($total){
        $paginator = new Paginator(
            $this->table['pagination']['page'],
            $total,
            $this->table['pagination']['limit']
        );

        $pages = $paginator->getPages();
        foreach($pages as &$page){
            if(is_array($page) && isset($page['number'])){
                $params = ['page' => $page['number']];
                if(isset($this->table['orderedColumn'])){
                    $params['order'] = $this->table['orderedColumn'];
                }
                if(isset($this->table['orderedDirection'])){
                    $params['dir'] = $this->table['orderedDirection'];
                }
                $words = isset($this->table['search']['value']) ? $this->table['search']['value'] : '';
                if($words){
                    $params['search'] = $words;
                }
                $page = [
                    'label' => $page['label'],
                    'number' => $page['number'],
                    'router' => [
                        'route' => $this->router['route'],
                        'params' => array_merge($params, $this->router['params'])
                    ]
                ];
            }
        }

        $this->table['pagination']['pages'] = $pages;
    }

    public function getTable(){
        return $this->table;
    }

    public function getTotalNumberOfFields(){
        $total = isset($this->table['checkAll']) && $this->table['checkAll'] ? 1 : 0;
        if(isset($this->table['fields'])){
            $total += count($this->table['fields']);
        }
        return $total;
    }

    public function getData(){
        return $this->data;
    }

    public function getRouter(){
        return $this->router;
    }

    /**
     * @param array $data
     * @param int $total total items without pagination
     */
    public function setData(array $data, $total){
        foreach ($data as $model){
            if($model instanceof Entity){
                $this->data[] = $this->getModelData($model);
            } elseif (is_array($model)) {
                $this->data[] = $model;
            }
        }

        if(isset($this->table['pagination'])){
            $this->setPaginationTotalItems($total);
        }
    }

    /**
     * @param Entity $model
     * @return array
     */
    protected function getModelData(Entity $model){
        $record = [];
        foreach ($this->table['fields'] as $field => $options){
            $record[$field] = [
                'type' => $options['type'],
                'value' => $this->getModelPropertyValue($model, $field),
                'params' => $options['params']
            ];
            if($options['type'] == 'link'){
                if(isset($options['router']['route'])){
                    $record[$field]['router']['route'] = $options['router']['route'];
                    if(isset($options['router']['params'])){
                        $record[$field]['router']['params'] = $this->getModelRouterParams($model, $options['router']['params']);
                    } else {
                        $record[$field]['router']['params'] = [];
                    }
                }
            }
        }
        if(count($record) > 0){
            $record['id'] = $model->getId();
        }

        return $record;
    }

    /**
     * @param Model $model
     * @param array $params
     * @return array
     */
    protected function getModelRouterParams(Model $model, array $params){
        $values = [];
        foreach ($params as $key => $property){
            if(is_int($key)){
                $values[$property] = $this->getModelPropertyValue($model, $property);
            } else {
                $values[$key] = $this->getModelPropertyValue($model, $property);
            }
        }
        return $values;
    }

    /**
     * @param Model $model
     * @param string $property
     * @return string
     */
    protected function getModelPropertyValue(Model $model, $property){
        $value = [];
        if($property && $property != '__toString'){
            $method = 'get' . ucfirst($property);
            if(method_exists($model, $method)){
                $value = $model->$method();
                if($value instanceof Entity){
                    $value = $value->getId();
                }
            }
        }

        return $value;
    }
}