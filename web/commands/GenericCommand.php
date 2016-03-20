<?php

include_once('../classes/mysql.class.php');
include_once('../classes/Session.php');
include_once('../classes/domain/UsuarioWeb.php');

class GenericCommand extends BaseCommand
{
	public $usuario;
	
	// constructor
	public function __construct() {
		global $fc;
		global $isDebug;
		
		$this->initBase();
		
		$db = $fc->getLink();
		
		if ($isDebug) {
			echo '<br>_GET<br>';
			
			var_dump($_GET);
			
			echo '<br>';
			
			echo '<br>Request<br>';
			
			var_dump($fc->request);
			
			echo '<br>';

			echo '<br>_FILES<br>';
			
			var_dump($_FILES);
			
			echo '<br>';

			echo '<br>_SERVER<br>';
			
			var_dump($_SERVER);
			
			echo '<br>';
		}
		// Database::initialize($fc->getLink());

        // chequeo si el usuario esta logueado mediante el uso se una sesion
        
        HTTP_Session::start();
        
        HTTP_Session::setIdle(60 * 60);
        
        if (HTTP_Session::isIdle()) {
            HTTP_Session::destroy();
        }
        
        HTTP_Session::updateIdle();
        
        //echo '<br>_SERVER<br>';
        
        //var_dump($_SERVER);
        
        //echo '<br>_GET<br>';
        
        //var_dump($_GET);
        
        //echo '<br>_POST<br>';
        
        //var_dump($_POST);
        
        //echo '<br>param<br>';
        
        //var_dump($fc->request->param);
        
        //echo '<br>';
        
        if (is_null(HTTP_Session::get('auth', null))) {
        	
        	// usuario debe hacer login antes de ejecutar este comando
        	if ($fc->commandClass == 'Ajax') {
				$msg = "Sesion expirada!";
				$code= 401; // the response your browser will have
				// these next 2 lines will output an error to the browser, this is the key
				header("HTTP/1.0 $code $msg");
        	}
        	else if ($fc->commandClass != 'Login' && $fc->commandClass != 'Logout' && $fc->commandClass != 'ActivaCuenta') {
        		        		
                // guardo el destino deseado para ejecutarlo despues de login
                $dest = 'index.php';
                $p = "?";
                foreach($fc->request->param as $k => $v) {
                	if (is_int($k)) {
                		$dest.= $p . $v . "=" . $fc->request->{$v};
                	}
                	else {
                    	$dest.= $p . $k . "=" . $v;
                	}
                	
                    $p = "&";
                }
                
				trigger_error(
                    'dest ' . $dest,
                    E_USER_NOTICE);                
                
                $fc->response->redirect($fc->receivers['main'] . '?do=Login&destino=' . urlencode($dest));
                
        	}
        	else {
        		// usuario hizo submit en login, continuamos...
        	}
        }
        else {
        	// usuario logueado
        	$usuario = HTTP_Session::get('usuario', null);
		
			$this->addVar('usuario_aplicacion', $usuario);
			
			$this->usuario = UsuarioWeb::getByID($db, $usuario->id);
			
			$puedeAgregar = false;
			
			if ($this->usuario->tieneAcceso($db, 'agregar')) {
				$puedeAgregar = true;
			}
	
			$this->addVar('puedeAgregar', $puedeAgregar);			
        	        	
        }
		
	}
}

?>