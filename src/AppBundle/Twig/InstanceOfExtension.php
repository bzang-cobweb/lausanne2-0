<?php
/**
 * Created by PhpStorm.
 * User: bzang
 * Date: 04/02/16
 * Time: 19:30
 */

namespace AppBundle\Twig;


use Symfony\Bundle\TwigBundle\DependencyInjection\TwigExtension;

class InstanceOfExtension extends TwigExtension
{

    public function getTests() {
        return array(
            new \Twig_SimpleTest('instanceof', array($this, 'isInstanceOf')),
        );
    }

    public function isInstanceOf($var, $instance) {
        $reflexionClass = new \ReflectionClass($instance);
        return $reflexionClass->isInstance($var);
    }
}