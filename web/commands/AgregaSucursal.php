<?php

include_once('GenericCommand.php');
include_once('../classes/domain/Proveedor.php');
include_once('../classes/domain/ProveedorSucursal.php');
include_once('../classes/domain/ProveedorSucursalServicio.php');
include_once('../classes/domain/ProveedorSucursalMarca.php');
include_once('../classes/domain/ProveedorSucursalCombustible.php');
include_once('../classes/domain/Servicio.php');
include_once('../classes/domain/Marca.php');
include_once('../classes/domain/Combustible.php');
include_once('../classes/domain/EstadoProveedor.php');
include_once('../classes/domain/BloqueoProveedor.php');
include_once('../classes/domain/Region.php');

class AgregaSucursal extends GenericCommand {
	function execute(){
		global $fc;
		
		$db = $fc->getLink();
		
		// recuerdo la clave de busqueda por si el usuario quisiera 'volver al listado'
		// * DUDOSO $this->addVar('search_keyword_proveedor', HTTP_Session::get('search_keyword_proveedor', ''));
		
		$m = "&nbsp;";

		$this->addVar("message", $m);

		// ayuda en pantalla
		$user_help_desk = 
			"Agrega una sucursal. Los campos marcados con * son obligatorios.<br>Accesos:<br><br>" .
			"Agrega Proveedor: Permite ingresar proveedores en forma manual o masiva";
		
		$this->addVar('user_help_desk', $user_help_desk);
		
		// regiones
		$regiones = Region::seek($db, array(), null, null, 0, 100000);
		
		$this->addVar('regiones', $regiones->data);
		
		// servicios
		$servicios = Servicio::seek($db, array(), null, null, 0, 100000);
		$this->addVar('servicios', $servicios->data);
		
		// marcas
		$param = array(
			'id_tipo_vehiculo'	=> 1,		
		);
		$marcas = Marca::seek($db, $param, null, null, 0, 100000);
		$this->addVar('marcas', $marcas->data);
		
		// combustibles
		$combustibles = Combustible::seek($db, array(), null, null, 0, 100000);
		$this->addVar('combustibles', $combustibles->data);
		
	    if (!isset($fc->request->direccion)) {
			// llamado desde 'EditaProveedor'
			
			// muestro controles limpios
			$v = '';

			$this->addVar('nombre', $v);
			$this->addVar('direccion', $v);
			$this->addVar('correo', $v);
			$this->addVar('telefono', $v);
			$this->addVar('latitud', $v);
			$this->addVar('longitud', $v);
			$this->addVar('valor_minimo', $v);
			$this->addVar('valor_maximo', $v);
			$this->addVar('detalle_html', $v);
			$this->addVar('url', $v);
						
			// grabo 'id_proveedor' en la sesion
			HTTP_session::set('id_proveedor', $fc->request->id_proveedor);
			
			// hago visible 'id_proveedor' al view
			$this->addVar('id_proveedor', $fc->request->id_proveedor);
		}
		else {
			// submit, agrega sucursal... grabamos cambios
			
			$exito = null;
			
			// obtengo de la sesion 'id_proveedor
			$id_proveedor = HTTP_session::get('id_proveedor');
			
			// hago visible 'id_proveedor' al view
			$this->addVar('id_proveedor', $id_proveedor);
			
			try {
			
				$bInTransaction = false;
				
				$proveedor_sucursal = new ProveedorSucursal();
				
				$proveedor_sucursal->id_proveedor = $id_proveedor;
				$proveedor_sucursal->id_comuna = $fc->request->comuna;
				$proveedor_sucursal->descripcion = $fc->request->descripcion;
				$proveedor_sucursal->direccion = $fc->request->direccion;
				$proveedor_sucursal->telefono1 = $fc->request->telefono1;
				$proveedor_sucursal->telefono2 = $fc->request->telefono2;
				$proveedor_sucursal->fax = $fc->request->fax;
				$proveedor_sucursal->correo = $fc->request->correo;
				$proveedor_sucursal->latitud = is_numeric($fc->request->latitud) ? $fc->request->latitud : null;
				$proveedor_sucursal->longitud = is_numeric($fc->request->longitud) ? $fc->request->longitud : null;
				$proveedor_sucursal->fecha_creacion = '1900-01-01 00:00:00';
				
		        $trace = debug_backtrace();
		        trigger_error(
		            "proveedor_sucursal: " . $proveedor_sucursal->toString(),
		            E_USER_NOTICE);
								
				$status_message = '';
				
				try {
					
					// inicio transaccion
					
					if (!$db->TransactionBegin()) {
						throw new Exception('Error al iniciar transaccion: ' . $db->Error(), $db->ErrorNumber(), null);
					}
					
					$bInTransaction = true;
					
			        /* eliminar */
					// servicios soportados por el proveedor
					foreach ($servicios->data as $servicio) {
				        
						$trace = debug_backtrace();
				        trigger_error(
				            "servicio " . var_export($servicio, true),
				            E_USER_NOTICE);
						
				        //if (isset($fc->request->chk_servicios[$servicio['id']])) {
				        if (in_array($servicio['id'], $fc->request->chk_servicios)) {
							
					        $trace = debug_backtrace();
					        trigger_error(
					            "servicio {$servicio['id']} presente",
					            E_USER_NOTICE);
					        
							
						}
					}
			        /* fin eliminar */
					
					$proveedor_sucursal->insert($db);
										
					// servicios soportados por el proveedor
					foreach ($servicios->data as $servicio) {
				        
						$trace = debug_backtrace();
				        trigger_error(
				            "servicio " . var_export($servicio, true),
				            E_USER_NOTICE);
						
				        //if (isset($fc->request->chk_servicios[$servicio['id']])) {
				        if (in_array($servicio['id'], $fc->request->chk_servicios)) {
							
							$pss = new ProveedorSucursalServicio();
							
							$pss->id_proveedor_sucursal = $proveedor_sucursal->id;
							$pss->id_servicio = $servicio['id'];
							$pss->id_tipo_vehiculo = 1;
							
							$pss->insert($db);
						}
					}
										
					// marcas soportadas por el proveedor
					if (isset($fc->request->multimarca)) {
						// multimarca
						$proveedor_sucursal->multimarca = 1;
					}
					else {
						foreach ($marcas->data as $marca) {
							//if (isset($fc->request->chk_marcas[$marca['id']])) {
							if (in_array($marca['id'], $fc->request->chk_marcas)) {
								
								$psm = new ProveedorSucursalMarca();
								
								$psm->id_proveedor_sucursal = $proveedor_sucursal->id;
								$psm->id_marca = $marca['id'];
								
								$psm->insert($db);
							}
						}
					}
					
					// combustibles soportados por el proveedor
					foreach ($combustibles->data as $combustible) {				        
						
						//if (isset($fc->request->chk_combustibles[$combustible['id']])) {
						if (in_array($combustible['id'], $fc->request->chk_combustibles)) {
							
							$psc = new ProveedorSucursalCombustible();
							
							$psc->id_proveedor_sucursal = $proveedor_sucursal->id;
							$psc->id_combustible = $combustible['id'];
							
							$psc->insert($db);
						}
					}
					
					// commit
					if (!$db->TransactionEnd()) {
						throw new Exception('Error al comitear transaccion: ' . $db->Error(), $db->ErrorNumber(), null);
					}
					
					// estatus exito
					$exito = true;
					
					$status_message = 'Sucursal agregada exitosamente';
					/*
					// checkboxes servicios
					$list_pss = ProveedorSucursalServicio::seekSpecial($db, $proveedor_sucursal->id);
					
					$this->addVar('list_pss', $list_pss);
								
					// checkboxws marcas
					$list_psm = ProveedorSucursalMarca::seekSpecial($db, $proveedor_sucursal->id);
					
					$this->addVar('list_psm', $list_psm);
		
					// checkboxws combustibles
					$list_psc = ProveedorSucursalCombustible::seekSpecial($db, $proveedor_sucursal->id);
					
					$this->addVar('list_psc', $list_psc);
					*/
				
				} catch (Exception $e) {
					// rollback
					if ($bInTransaction) {
						$db->TransactionRollback();
					}
					
					throw new Exception($e->getMessage(), $e->getCode(), $e->getPrevious());
				}
			} catch (Exception $e) {
				// estatus fracaso
				$exito = false;
				
				$status_message = 'Sucursal no pudo ser agregada. Raz&oacute;n: ' . $e->getMessage();
			}
				
			$this->addVar("exito", $exito);
			
			$this->addVar("status_message", $status_message);

			/* cargo en los controles valores pre submit */
			
			/* combos */
			
			// para setear en formulario opcion seleccionada
			$this->addVar('region', $fc->request->region);
			
			// envio al formulario las opciones en los combos que se cargan dinamicamente.... se cargan en los comandos ajax
			// opciones en combo de comunas
			$this->addVar('options_comunas', HTTP_Session::get('options_comunas', null), null);
			
			// para setear en formulario opcion seleccionada
			$this->addVar('comuna', $fc->request->comuna);
			// para setear en formulario opcion seleccionada
			$this->addVar('estado', $fc->request->estado);
			// para setear en formulario opcion seleccionada
			$this->addVar('bloqueo', $fc->request->bloqueo);
			
			/* checkboxes */
			
			// checkboxes servicios
			$this->addVar('chk_servicios', $fc->request->chk_servicios);
						
			// checkboxes marcas
			$this->addVar('chk_marcas', $fc->request->chk_marcas);
			
			// checkboxes combustibles
			$this->addVar('chk_combustibles', $fc->request->chk_combustibles);
			
			//$this->addVar('list_psc', $list_psc);
			
			/* textboxes */
									
			$fv=array();
			
			$fv[]='descripcion';
			$fv[]='direccion';
			$fv[]='telefono1';
			$fv[]='telefono2';
			$fv[]='fax';
			$fv[]='correo';
			$fv[]='latitud';
			$fv[]='longitud';
			
			$this->initFormVars($fv);
		}
				
		$this->processSuccess();

	}
}
?>