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
include_once('../classes/domain/Comuna.php');

class EditaSucursal extends GenericCommand {
	function execute(){
		global $fc;
		
		$db = $fc->getLink();
		
		// recuerdo la clave de busqueda por si el usuario quisiera 'volver al listado'
		// * DUDOSO $this->addVar('search_keyword_proveedor', HTTP_Session::get('search_keyword_proveedor', ''));
		
		$m = "&nbsp;";

		$this->addVar("message", $m);

		// ayuda en pantalla
		$user_help_desk = 
			"Edita una sucursal<br>" .
			"Los campos marcados con * son obligatorios.<br>";
		
		$this->addVar('user_help_desk', $user_help_desk);
		
		// regiones
		$regiones = Region::seek($db, array(), null, null, 0, 100000);
		
		$this->addVar('regiones', $regiones->data);
		
		// servicios
		$servicios = Servicio::seek($db, array(), null, null, 0, 100000);
		$this->addVar('servicios', $servicios->data);
		
		// marcas
		$marcas = Marca::seek($db, array('id_tipo_vehiculo'	=> 1), null, null, 0, 100000);
		$this->addVar('marcas', $marcas->data);
		
		// combustibles
		$combustibles = Combustible::seek($db, array(), null, null, 0, 100000);
		$this->addVar('combustibles', $combustibles->data);
		
		// la sucursal a ser editado
		$proveedor_sucursal = null;
		
		if (isset($fc->request->id_proveedor_sucursal)) {
			// llamado desde:
			// 'GestionaProveedores' EditaSucursal
			// 'EditaProveedor' EditaSucursal
			
			// muestro los datos de la sucursal
			$proveedor_sucursal = ProveedorSucursal::getById($db, $fc->request->id_proveedor_sucursal);
			
			$region = Comuna::getById($db, $proveedor_sucursal->id_comuna)->getRegion($db);
			
	        $trace = debug_backtrace();
	        trigger_error(
	            "proveedor_sucursal: " . $proveedor_sucursal->toString(),
	            E_USER_NOTICE);
			
			$this->addVar('proveedor_sucursal', $proveedor_sucursal);

			$this->addVar('descripcion', $proveedor_sucursal->descripcion);
			$this->addVar('direccion', $proveedor_sucursal->direccion);
			$this->addVar('region', $region->id);
			
			// comunas
			$comunas = Comuna::seek($db, array('id_region' => $region->id), null, null, 0, 100000);
			
			$this->addVar('comunas', $comunas->data);			
			
			$this->addVar('comuna', $proveedor_sucursal->id_comuna);
			
			$this->addVar('telefono1', $proveedor_sucursal->telefono1);
			$this->addVar('telefono2', $proveedor_sucursal->telefono2);
			$this->addVar('fax', $proveedor_sucursal->fax);
			$this->addVar('correo', $proveedor_sucursal->correo);
			$this->addVar('latitud', $proveedor_sucursal->latitud);
			$this->addVar('longitud', $proveedor_sucursal->longitud);
						
			// servicios soportados por la sucursal ********** ok 20160315
			$chk_servicios = array();
			
			foreach ($servicios->data as $servicio) {
				$parameters = array(
					'id_proveedor_sucursal' => $fc->request->id_proveedor_sucursal,
					'id_servicio' => $servicio['id']
				);
				if (count(ProveedorSucursalServicio::seek($db, $parameters, null, null, 0, 1)->data) > 0) {
					$chk_servicios[] = $servicio['id'];
				}
			}
			
	        $trace = debug_backtrace();
	        trigger_error(
	            "chk_servicios: " . var_export($chk_servicios, true),
	            E_USER_NOTICE);
			
	        $this->addVar('chk_servicios', $chk_servicios);
			
			// marcas atendidas por la sucursal
			$chk_marcas = array();
			
			if ($proveedor_sucursal->multimarca) {
				$this->addVar('multimarca', $proveedor_sucursal->multimarca);
			}
			else {
				foreach ($marcas->data as $marca) {
					$parameters = array(
						'id_proveedor_sucursal' => $fc->request->id_proveedor_sucursal,
						'id_marca' => $marca['id']
					);
					if (count(ProveedorSucursalMarca::seek($db, $parameters, null, null, 0, 1)->data) > 0) {
						$chk_marcas[] = $marca['id'];
					}
				}
			}
				
			$this->addVar('chk_marcas', $chk_marcas);
			
			// combustibles soportados por la sucursal
			$chk_combustibles = array();
			
			foreach ($combustibles->data as $combustible) {
				$parameters = array(
					'id_proveedor_sucursal' => $fc->request->id_proveedor_sucursal,
					'id_combustible' => $combustible['id']
				);
				if (count(ProveedorSucursalCombustible::seek($db, $parameters, null, null, 0, 1)->data) > 0) {
					$chk_combustibles[] = $combustible['id'];
				}
			}
			
	        $trace = debug_backtrace();
	        trigger_error(
	            "chk_combustibles: " . var_export($chk_combustibles, true),
	            E_USER_NOTICE);
			
	        $this->addVar('chk_combustibles', $chk_combustibles);
						
			// hago visible 'id_proveedor' al view, para volver a la pagina anterior
			$this->addVar('id_proveedor', $proveedor_sucursal->id_proveedor);
			
			// grabo 'id_proveedor_sucursal' en la sesion
			HTTP_session::set('id_proveedor_sucursal', $fc->request->id_proveedor_sucursal);
			
		}
		else if (isset($fc->request->descripcion)) {
			// submit, edita sucursal... grabamos cambios
			
			$exito = null;
			
			$id_proveedor_sucursal = HTTP_session::get('id_proveedor_sucursal');
			
			try {
			
				$bInTransaction = false;
				
				$proveedor_sucursal = ProveedorSucursal::getById($db, $id_proveedor_sucursal);
				
				// servicios soportados por la sucursal
				$servicios_sucursal = $proveedor_sucursal->getServicios($db);
				
				// marcas soportadas por la sucursal
				$marcas_sucursal = $proveedor_sucursal->getMarcas($db);
				
				// combustibles soportados por la sucursal
				$combustibles_sucursal = $proveedor_sucursal->getCombustibles($db);
								
				$status_message = '';
				
				try {
					
					// inicio transaccion
					
					if (!$db->TransactionBegin()) {
						throw new Exception('Error al iniciar transaccion: ' . $db->Error(), $db->ErrorNumber(), null);
					}
					
					$bInTransaction = true;
					
			        $trace = debug_backtrace();
			        trigger_error(
			            "proveedor_sucursal: " . $proveedor_sucursal->toString(),
			            E_USER_NOTICE);
			            
					$proveedor_sucursal->id_comuna = $fc->request->comuna;
					
					// comunas
					$comunas = Comuna::seek($db, array('id_region' => $fc->request->region), null, null, 0, 100000);
					
					$proveedor_sucursal->descripcion = $fc->request->descripcion;
					$proveedor_sucursal->direccion = $fc->request->direccion;
					$proveedor_sucursal->telefono1 = $fc->request->telefono1;
					$proveedor_sucursal->telefono2 = $fc->request->telefono2;
					$proveedor_sucursal->fax = $fc->request->fax;
					$proveedor_sucursal->correo = $fc->request->correo;
					$proveedor_sucursal->latitud = is_numeric($fc->request->latitud) ? $fc->request->latitud : null;
					$proveedor_sucursal->longitud = is_numeric($fc->request->longitud) ? $fc->request->longitud : null;
					//$proveedor_sucursal->fecha_creacion = '1900-01-01 00:00:00';
					
			        $trace = debug_backtrace();
			        trigger_error(
			            "proveedor_sucursal: " . $proveedor_sucursal->toString(),
			            E_USER_NOTICE);
					

					$proveedor_sucursal->update($db);
					
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
															
					// servicios soportados por el proveedor
					foreach ($servicios->data as $servicio) {
				        
						$trace = debug_backtrace();
				        trigger_error(
				            "servicio " . var_export($servicio, true),
				            E_USER_NOTICE);
						
				        //if (isset($fc->request->chk_servicios[$servicio['id']])) {
				        if (in_array($servicio['id'], $fc->request->chk_servicios)) {
				        	
				        	if (!in_array($servicio['id'], $servicios_sucursal->data)) {
								$pss = new ProveedorSucursalServicio();
								
								$pss->id_proveedor_sucursal = $id_proveedor_sucursal;
								$pss->id_servicio = $servicio['id'];
								$pss->id_tipo_vehiculo = 1;
								
								$pss->insert($db);
				        	}							
						}
						else {
							
			        		$parameters = array(
				        		'id_proveedor_sucursal' => $id_proveedor_sucursal,
				        		'id_servicio' => $servicio['id'],
				        		'id_tipo_vehiculo' => '1'
			        		);
			        		
							$ar_pss = ProveedorSucursalServicio::seek($db, $parameters, null, null, 0, 1);
							
							if (count($ar_pss->data) > 0) {
								$pss = $ar_pss->data[0];
								
								//$pss->delete($db);
								ProveedorSucursalServicio::getById($db, $pss['id'])->delete($db);	
							}
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
								
								if (!in_array($marca['id'], $marcas_sucursal->data)) {
									$psm = new ProveedorSucursalMarca();
									
									$psm->id_proveedor_sucursal = $id_proveedor_sucursal;
									$psm->id_marca = $marca['id'];
									
									$psm->insert($db);
								}
							}
							else {
							
								$parameters = array(
					        		'id_proveedor_sucursal' => $id_proveedor_sucursal,
					        		'id_marca' => $marca['id']
				        		);

				        		$ar_psm = ProveedorSucursalMarca::seek($db, $parameters, null, null, 0, 1);

				        		if (count($ar_psm->data) > 0) {
				        			$psm = $ar_psm->data[0];
				        				
				        			//$psm->delete($db);
				        			ProveedorSucursalMarca::getById($db, $psm['id'])->delete($db);
				        		}
							}
						}
					}
					
					// combustibles soportados por el proveedor
					foreach ($combustibles->data as $combustible) {				        
						
						//if (isset($fc->request->chk_combustibles[$combustible['id']])) {
				        if (in_array($combustible['id'], $fc->request->chk_combustibles)) {
				        	
				        	if (!in_array($combustible['id'], $combustibles_sucursal->data)) {
								$pss = new ProveedorSucursalCombustible();
								
								$pss->id_proveedor_sucursal = $id_proveedor_sucursal;
								$pss->id_combustible = $combustible['id'];
								$pss->id_tipo_vehiculo = 1;
								
								$pss->insert($db);
				        	}							
						}
						else {
							
			        		$parameters = array(
				        		'id_proveedor_sucursal' => $id_proveedor_sucursal,
				        		'id_combustible' => $combustible['id']
			        		);
			        		
							$ar_psc = ProveedorSucursalCombustible::seek($db, $parameters, null, null, 0, 1);
							
							if (count($ar_psc->data) > 0) {
								$psc = $ar_psc->data[0];
								
								//$psc->delete($db);
								ProveedorSucursalCombustible::getById($db, $psc['id'])->delete($db);
							}
						}
					}
					
					// commit
					if (!$db->TransactionEnd()) {
						throw new Exception('Error al comitear transaccion: ' . $db->Error(), $db->ErrorNumber(), null);
					}
					
					// estatus exito
					$exito = true;
					
					$status_message = 'Proveedor agregado exitosamente';
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
				
				$status_message = 'Proveedor no pudo ser agregado. Raz&oacute;n: ' . $e->getMessage();
			}
				
			$this->addVar("exito", $exito);
			
			$this->addVar("status_message", $status_message);

			/* cargo en los controles valores pre submit */
			
			/* combos */
			
			// para setear en formulario opcion seleccionada
			$this->addVar('region', $fc->request->region);
			
			// comunas
			$comunas = Comuna::seek($db, array('id_region' => $fc->request->region), null, null, 0, 100000);
			
			$this->addVar('comunas', $comunas);
			
			// para setear en formulario opcion seleccionada
			$this->addVar('comuna', $fc->request->comuna);
			
			/* checkboxes */
			
			// checkboxes servicios
			$this->addVar('chk_servicios', $fc->request->chk_servicios);
						
			// checkboxes marcas
			$this->addVar('chk_marcas', $fc->request->chk_marcas);
			
			// checkboxes combustibles
			$this->addVar('chk_combustibles', $fc->request->chk_combustibles);
			
			//$this->addVar('list_psc', $list_psc);
			
			/* textboxes */
			
			// hago visible 'id_proveedor' al view, para volver a la pagina anterior
			$this->addVar('id_proveedor', $proveedor_sucursal->id_proveedor);
									
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