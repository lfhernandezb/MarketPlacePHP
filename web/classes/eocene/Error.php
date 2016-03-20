<?php
/**
 * Class Error
 * Version 1.0.0
 * Author: Deepak Dutta, http://www.eocene.net
 * Unrestricted license, subject to no modifcations to the line above.
 * Please include any modification history.
 * 10/01/2002 Initial creation.
 * Error class writes any error and abort the request.
 * Modify it according to your needs.
 *
 * PUBLIC METHODS
 *	Error(&$errorMessage)
*/
class Error{

	function Error(&$errorMessage){
		global $fc;

		print "EOCENE error in ".$_SERVER['REQUEST_URI'].":<br><br> ";
		print $errorMessage;		
		exit();
	}
}
?>
