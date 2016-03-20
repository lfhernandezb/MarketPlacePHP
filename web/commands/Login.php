<?php

include_once('GenericCommand.php');
include_once('../classes/domain/UsuarioWeb.php');

class Login extends GenericCommand{
	function execute(){
		global $fc;
		
		$db = $fc->getLink();
		
		$username = $fc->request->usr;
		$password = $fc->request->pwd;
		
		//var_dump($_SERVER);
		
		$ar = null;
		
		if (in_array('destino', $fc->request->param)) {
			
			// recordamos el destino
			
			$ar = explode('destino=', $_SERVER['REQUEST_URI']);
			
			// $ar[1] tiene la forma 'index.php?do=comando&parameter1=value1&.....
			
			$this->addVar("target", urldecode($ar[1]));
		}				
		
		if (isset($username)) {
			// form submiteado
			
			$exito = null;
			
			$usuario = UsuarioWeb::getByUsername($db, "'" . $username . "'");
			
			//echo $usuario->contrasena . '<br>';
			
			echo md5($password) . '<br>';
			
			echo $usuario->toString() . '<br>';
			
			if (isset($usuario) && $usuario->contrasena == md5($password) && $usuario->borrado == 0) {
				if ($usuario->activo == 1) {
					// ok, login exitoso
					HTTP_session::set('auth', 'ok');
					HTTP_session::set('usuario', $usuario);
													
					// caimos aqui porque el usuario quiso hacer algo despues que la sesion expiro?
					if (is_array($ar)) {
						
						// redireccionamos al destino; el login se invoco con ?do=Login&destino=<destino>
						
						$ar2 = explode('do=', $ar[1]);
						
						$fc->response->redirect($fc->receivers['main']."?do={$ar2[1]}");
					}
					else {
						$fc->response->redirect($fc->receivers['main']."?do={$fc->defaultCommand}");
					}
				}
				else {
					// cuenta no activada... redirigimos....
					$fc->response->redirect($fc->receivers['main']."?do=ActivaCuenta&idUsuario=" . $usuario->id);
				}
			}
			else {
				// parametros de login incorrectos
				$exito = false;
				
				$status_message = "Usuario y/o contrase&ntilde;a incorrectos";
				
				$this->addVar("status_message", $status_message);
				
				$this->addVar("exito", $exito);
				
			}
		}
		
		if (is_null(HTTP_Session::get('auth', null))) {
			// login no exitoso o primera vaz en pagina
			$fv=array();
			
			$fc->request->pwd = '';
			
			$fv[0]="usr";
			$fv[1]="pwd";
			$this->initFormVars($fv);
			
			if (!isset($username)) {
        		// se despliga pantalla de login (no hay submit)... limpio mensaje de error
        		
				$m = "&nbsp;";
        		
        		$this->addVar("message", $m);        		
        		
			}
			
			$this->processSuccess();
			
		}

	}
}
?>