<?php

/**
 * The receiver. Code to activate the eocene system
 * Please set the correct path to eocene and config files below
 * Version 1.0.0
 * Author: Deepak Dutta, http://www.eocene.net
 * Unrestricted license, subject to no modifcations to the line above.
 * Please include any modification history.
 * 10/01/2002 Initial creation.
*/

$isDebug=false;

$eocene="../classes/eocene";
$configFile="../config.xml";

$iniDelimiter=":";
ini_set("include_path", $eocene . $iniDelimiter . ini_get("include_path"));

include_once("FrontController.php");

$fc= new FrontController($configFile);

$fc->executeCommand();

//var_dump($fc);

echo $fc->response->content;

?>
