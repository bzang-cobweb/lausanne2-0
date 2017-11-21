<?php
/**
 * Created by PhpStorm.
 * User: bzang
 * Date: 04/02/16
 * Time: 22:10
 */

namespace AppBundle\Utility;


class HtmlUtility
{
    /**
     * @param $url
     * @param $label
     * @param array $options
     * @return string
     */
    public function link($url, $label, array $options = array())
    {
        $class = isset($options['class']) ? ' class="'. $options['class'] .'"' : '';
        return '<a href="' . $url . '"' . $class . '>' . $label . '</a>';
    }

}