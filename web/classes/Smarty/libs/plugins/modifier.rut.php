<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty rut modifier plugin
 *
 * Type:     modifier<br>
 * Name:     rut<br>
 * Purpose:  Formate un RUT con puntos y gui�n.
 * @author   Marcelo M�ller <mmuller at borealis dot cl>
 * @param string
 * @return string
 */
function smarty_modifier_rut($string)
{
    
	if($string == "") {
		return "";
	}

	$dv = substr($string, -1);
 	$rut = number_format(substr($string, 0, strlen($string) - 1) , 0, "" , ".");
 	
 	return $rut . "-" . $dv;   
 	
}

?>