<?php

include_once('GenericCommand.php');

class Logout extends GenericCommand{
	function execute(){
		global $fc;
		
		HTTP_Session::clear();
		
		$fc->response->redirect($fc->receivers['main']."?do={$fc->defaultCommand}");
	}
}
?>