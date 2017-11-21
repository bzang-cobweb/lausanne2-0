<?php
/**
 * Created by PhpStorm.
 * User: bzang
 * Date: 04/02/16
 * Time: 22:10
 */

namespace AppBundle\Utility;


use AppBundle\Entity\Entity;

class EntityUtility
{
    /**
     * @param int $year
     * @return array
     */
    public static function getSeasons($year = 0)
    {

        if($year == 0) {
            $seasons = ['-' => 0];
            $year = (int)date('Y', strtotime('+ 1 year'));
            for ($i = 1; $i < 5; $i++) {
                $choice = ($year - $i) . '-' . ($year - ($i - 1));
                $seasons[$choice] = $choice;
            }
        } else {
            $seasons = ['-'];
            $next = (int)date('Y', strtotime('+ 1 year'));
            for ($i = $next; $i > $year; $i--) {
                $choice = ($i - 1) . '-' . ($i) ;
                $seasons[$choice] = $choice;
            }
        }
        return $seasons;
    }

    /**
     * @param array $entities
     * @param string $property
     * @return array
     */
    public static function getArrayValues(array $entities, $property = '')
    {
        $array = ['-'];
        foreach ($entities as $entity) {
            if($entity instanceof Entity){
                $method = $property == '' ? '__toString' : 'get' . ucfirst($property);
                if(method_exists($entity, $method)){
                    $array[$entity->getId()] = $entity->$method();
                } else {
                    $array[$entity->getId()] = $entity->getId();
                }
            }
        }
        return $array;
    }
}