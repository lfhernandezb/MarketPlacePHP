<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty number_format modifier plugin
 *
 * Type:     modifier<br>
 * Name:     number_format<br>
 * Purpose:  format number
 */
function smarty_modifier_number_format($number, $decimals)
{
    return number_format($number, $decimals, ",", ".");
}

/* vim: set expandtab: */

?>
