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

class EditaProveedor extends GenericCommand {
	function execute(){
		global $fc;
		
		$db = $fc->getLink();
		
		// recuerdo la clave de busqueda por si el usuario quisiera 'volver al listado'
		// * DUDOSO $this->addVar('search_keyword_proveedor', HTTP_Session::get('search_keyword_proveedor', ''));
		
		$m = "&nbsp;";

		$this->addVar("message", $m);

		// ayuda en pantalla
		$user_help_desk = 
			"Edita un proveedor, el cual es una entidad que posee una o m&aacute;s <br>" .
			"sucursales. Los campos marcados con * son obligatorios.<br>";
		
		$this->addVar('user_help_desk', $user_help_desk);
		
		// estado proveedor
		$estados = EstadoProveedor::seek($db, array(), null, null, 0, 100000);
		
		$this->addVar('estados', $estados->data);
		
		// bloqueo proveedor
		$bloqueos = BloqueoProveedor::seek($db, array(), null, null, 0, 100000);
				
		$this->addVar('bloqueos', $bloqueos->data);
				
		// el proveedor a ser editado
		$proveedor = null;
		
		if (isset($fc->request->id_proveedor)) {
			// llamado desde:
			// 'GestionaProveedores' EditaProveedor
			// 'AgregaSucursal' Volver a EditaProveedor
			// 'EditaSucursal' Volver a EditaProveedor
			
			// grabo 'id_proveedor' en la sesion
			HTTP_session::set('id_proveedor', $fc->request->id_proveedor);
			
			// hago visible 'id_proveedor' al view
			$this->addVar('id_proveedor', $fc->request->id_proveedor);
			
			// muestro los datos del proveedor
			$proveedor = Proveedor::getById($db, $fc->request->id_proveedor);
			
			$this->addVar('proveedor', $proveedor);

			$this->addVar('razon_social', $proveedor->razon_social);
			$this->addVar('nombre_fantasia', $proveedor->nombre_fantasia);
			$this->addVar('rut', $proveedor->rut);
			$this->addVar('direccion_facturacion', $proveedor->direccion_facturacion);
			$this->addVar('web', $proveedor->web);
			$this->addVar('texto_html', $proveedor->texto_html);
			$this->addVar('estado', $proveedor->id_estado_proveedor);
			$this->addVar('bloqueo', $proveedor->id_bloqueo_proveedor);
			
			// cargo las sucursales
			$sucursales = $proveedor->getSucursales($db);
			
			$this->addVar('sucursales', $sucursales->data);
		}
		else if (isset($fc->request->descripcion)) {
			// submit, edita proveedor... grabamos cambios
			
			$exito = null;
			
			try {
			
				$bInTransaction = false;
								
				// obtengo de la sesion 'id_proveedor
				$id_proveedor = HTTP_session::get('id_proveedor');
				
				// hago visible 'id_proveedor' al view
				$this->addVar('id_proveedor', $fc->request->id_proveedor);
				
				$proveedor = Proveedor::getById($db, $id_proveedor);

				$proveedor->razon_social = $fc->request->razon_social;
				$proveedor->nombre_fantasia = $fc->request->nombre_fantasia;
				$proveedor->rut = $fc->request->rut;
				$proveedor->direccion_facturacion = $fc->request->direccion_facturacion;
				$proveedor->web = $fc->request->web;
				//$proveedor->texto_html = $fc->request->texto_html;
				$proveedor->id_estado_proveedor = $fc->request->estado;
				$proveedor->id_bloqueo_proveedor = $fc->request->bloqueo;
				//$proveedor->fecha_creacion = '1900-01-01 00:00:00';
								
				$status_message = '';
				
				try {
					
					// inicio transaccion
					
					if (!$db->TransactionBegin()) {
						throw new Exception('Error al iniciar transaccion: ' . $db->Error(), $db->ErrorNumber(), null);
					}
					
					$bInTransaction = true;
					
			        $trace = debug_backtrace();
			        trigger_error(
			            "proveedor: " . $proveedor->toString(),
			            E_USER_NOTICE);
			            
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
					
			        $proveedor->update($db);
										
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
			
			/* textboxes */
									
			$fv=array();
			
			// para el correcto despliegue
			$fc->request->texto_html = htmlentities($fc->request->texto_html, ENT_QUOTES);
			
			$fv[]='razon_social';
			$fv[]='nombre_fantasia';
			$fv[]='rut';
			$fv[]='direccion_facturacion';
			$fv[]='web';
			$fv[]='texto_html';
			
			$this->initFormVars($fv);
		}
				
		$this->processSuccess();

	}
}
?>