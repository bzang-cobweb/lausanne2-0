<?php
/**
 * Created by PhpStorm.
 * User: bzang
 * Date: 04/02/16
 * Time: 22:10
 */

namespace AppBundle\Utility;


class StringUtility
{
    /**
     * Deduce pseudo (first character of name and the first 2 characters of lastname)
     *
     * @param $firstName
     * @param $lastName
     * @return string
     */
    public function pseudo($firstName, $lastName)
    {
        $firstName = $this->clean($firstName);
        $lastName = $this->clean($lastName);
        return substr(strtolower(trim(strip_tags($firstName))), 0, 1) . substr(strtolower(trim(strip_tags($lastName))), 0, 2);
    }


    /**
     * Replace specials characters
     *
     * @param string $string
     * @return string
     */
    public function clean($string){
        $unwanted = array('Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
            'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U',
            'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c',
            'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o',
            'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y' );
        return strtr( $string, $unwanted );
    }


    /**
     * @param string $string
     * @return string
     */
    public function camelcase($string){
        $str = str_replace('_', ' ', $string);
        $str = ucwords(strtolower($str));
        return str_replace(' ', '', $str);
    }
}