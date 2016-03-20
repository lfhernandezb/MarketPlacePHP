<?php

include_once('GenericCommand.php');
include_once('../classes/domain/Proveedor.php');
include_once('../classes/domain/Marca.php');
include_once('../classes/domain/Region.php');
include_once('../classes/domain/Comuna.php');
include_once('../classes/domain/Servicio.php');
include_once('../classes/domain/Combustible.php');

class GestionaProveedores extends GenericCommand{
	function execute(){
		global $fc;
		
		$db = $fc->getLink();
		
		// ayuda en pantalla
		$user_help_desk = 
			"Busca proveedores por nombre,direcci&oacute;n, correo o tel&eacute;fono.<br>" .
			"Acciones:<br><br>" .
			"<img src=\"images/detail.png\" border=0 width=16 height=16 />&nbsp;Detalle Proveedor.<br>" .
			"<img src=\"images/edit.png\" border=0 width=16 height=16 />&nbsp;Edita Proveedor.<br>" .
			"<img src=\"images/help.png\" border=0 width=16 height=16 />&nbsp;Consultas donde fue incluido el proveedor.<br>" .
			"<img src=\"images/trash.png\" border=0 width=16 height=16 />&nbsp;Elimina Proveedor.<br>";
			
		$this->addVar('user_help_desk', $user_help_desk);
		
		// para llenar combos estaticos
		$param = array(
			'id_tipo_vehiculo'	=> 1,		
		);
		
		$marcas = Marca::seek($db, $param, null, null, 0, 10000);
		
		$this->addVar('marcas', $marcas->data);
		
		$servicios = Servicio::seek($db, array(), null, null, 0, 10000);
		
		$this->addVar('servicios', $servicios->data);
		
		$combustibles = Combustible::seek($db, array(), null, null, 0, 10000);
		
		$this->addVar('combustibles', $combustibles->data);
		
		$regiones = Region::seek($db, array(), null, null, 0, 10000);
		
		$this->addVar('regiones', $regiones->data);
		
		$fv=array();
		
		if (empty($_POST)) {
			// limpio variables
			HTTP_session::set('search_keyword_proveedor', null);
			
			HTTP_session::set('search_keyword_proveedor_alt', null);
			
		}
		else if (isset($fc->request->search_keyword_proveedor)) {
			// submit desde busqueda rapida, a la izquierda
			
			$exito = null;
			
			// guardo el valor de busqueda para utilizarlo en "volver al listado" en "edita proveedor"
			
			HTTP_session::set('search_keyword_proveedor', $fc->request->search_keyword_proveedor);
			
			// elimino el valor del form alternativo, para saber como se hizo la busqueda al volver a esta pantalla
			
			HTTP_session::set('search_keyword_proveedor_alt', null);
			
			$search_keyword = $fc->request->search_keyword_proveedor;
			
			$search_result = Proveedor::seekSpecial($db, $search_keyword, 'r.orden', 'ASC', 0, 10000);
			
			// var_dump($search_result);
			
			$this->addVar('search_result', $search_result->data);
			
			$row_number = $db->RowCount() === false ? 0 : $db->RowCount();
			
			$this->addVar('row_number', $row_number);
			
			$exito = true;
			
			$this->addVar('exito', $exito);
			
			$fv[0]="search_keyword_proveedor";
			
			$this->initFormVars($fv);
			
		}
		else if (isset($fc->request->marca)) {
			// submit desde form con controles
			
			$exito = null;
			
			$id_marca = $fc->request->marca;
			
			$id_servicio = $fc->request->servicio;
			
			$id_combustible = $fc->request->combustible;
			
			$id_region = $fc->request->region;
			
			$id_comuna = $fc->request->comuna;
			
			$parameters = array();
			
			// guardo el valor de busqueda para utilizarlo en "volver al listado" en "utiliza repuesto"
			
			HTTP_session::set('search_keyword_proveedor_alt', array(
				'id_marca'			=> $id_marca,
				'id_servicio'		=> $id_servicio,
				'id_combustible'	=> $id_combustible,
				'id_region'			=> $id_region,
				'id_comuna'			=> $id_comuna,
			));
						
			// elimino el valor del form alternativo, para saber como se hizo la busqueda al volver a esta pantalla
			
			HTTP_session::set('search_keyword_proveedor', null);
			
			if (isset($id_marca) && $id_marca != "") {
				$parameters['id_marca'] = $id_marca;
				
				// para setear en formulario opcion seleccionada
				$this->addVar('marca', $id_marca);
			}
			
			if (isset($id_servicio) && $id_servicio != "") {
				$parameters['id_servicio'] = $id_servicio;
				
				// para setear en formulario opcion seleccionada
				$this->addVar('servicio', $id_servicio);
			}
						
			if (isset($id_combustible) && $id_combustible != "") {
				$parameters['id_combustible'] = $id_combustible;
				
				// para setear en formulario opcion seleccionada
				$this->addVar('combustible', $id_combustible);
			}
			
			if (isset($id_region) && $id_region != "") {
				$parameters['id_region'] = $id_region;
				
				// para setear en formulario opcion seleccionada
				$this->addVar('region', $id_region);
			}
			
			if (isset($id_comuna) && $id_comuna != "") {
				$parameters['id_comuna'] = $id_comuna;
				
				// para setear en formulario opcion seleccionada
				$this->addVar('comuna', $id_comuna);
			}
			
			$search_result = Proveedor::seek($db, $parameters, 'pr.nombre_fantasia', 'ASC', 0, 10000);
			
			// var_dump($search_result);
			
			$this->addVar('search_result', $search_result->data);
			
			$row_number = $db->RowCount() === false ? 0 : $db->RowCount();
			
			$this->addVar('row_number', $row_number);
			
			$exito = true;
			
			$this->addVar('exito', $exito);
			
			// envio al formulario las opciones en los combos que se cargan dinamicamente.... se cargan en los comandos ajax
			// opciones en combo de comunas
			$this->addVar('options_comunas', HTTP_Session::get('options_comunas', null), null);
			
			$fv[] = 'marca';
			
			$this->initFormVars($fv);
			
			
		}
		else if (isset($fc->request->search)) {
			// submit desde AgregaProveedor o EditaProveedor (volver al listado)
			
			$exito = null;
			
			// recupero el valor de busqueda
			
			$search_keyword = HTTP_session::get('search_keyword_proveedor');
			
			// elimino el valor del form alternativo, para saber como se hizo la busqueda al volver a esta pantalla
			
			HTTP_session::set('search_keyword_proveedor_alt', null);
			
			$search_result = Proveedor::seekSpecial($db, $search_keyword);
			
			// var_dump($search_result);
			
			$this->addVar('search_result', $search_result);
			
			$row_number = $db->RowCount() === false ? 0 : $db->RowCount();
			
			$this->addVar('row_number', $row_number);
			
			$exito = true;
			
			$this->addVar('exito', $exito);
			
			// para escribir en textbox el valor de la busqueda
			
			$this->addVar('search_keyword_proveedor', $search_keyword);
								
		}
		else if (isset($fc->request->search_alt)) {
			// submit desde AgregaProveedor o EditaProveedor (volver al listado)
			
			$this->addVar('dias', $dias);
			$exito = null;
			$activo = null;
			$dias = null;
			$auto = null;
			$km = null;
			
			$dias = $fc->request->dias;
			
			$parameters = array();
			
			// recupero el valor de busqueda
			
			$search_keyword = HTTP_session::get('search_keyword_proveedor_alt');
			
			// elimino el valor del form alternativo, para saber como se hizo la busqueda al volver a esta pantalla
			
			HTTP_session::set('search_keyword_proveedor', null);
			
			if (isset($search_keyword['activo']) && isset($search_keyword['dias'])) {
				$activo = $search_keyword['activo'];
				$dias = $search_keyword['dias'];
				$parameters['activo'] = $dias;
				$this->addVar('activo', $activo);
				$this->addVar('dias', $dias);
			}
			
			$auto = $search_keyword['auto'];
			
			if (isset($auto)) {
				$auto = $search_keyword['auto'];
				$parameters['auto'] = '';
				$this->addVar('auto', $auto);
			}
			
			$km = $search_keyword['km'];
			
			if (isset($km)) {
				$km = $search_keyword['km'];
				$parameters['km'] = '';
				$this->addVar('km', $km);
			}
			
			$parameters['no borrado'] = null;
			
			$parameters['identificado'] = null;
			
			$search_result = Proveedor::seek($db, $parameters, 'p.fecha_modificacion', 'ASC', 0, 10000);
			
			// var_dump($search_result);
			
			$this->addVar('search_result', $search_result);
			
			$row_number = $db->RowCount() === false ? 0 : $db->RowCount();
			
			$this->addVar('row_number', $row_number);
			
			$exito = true;
			
			$this->addVar('exito', $exito);
			/*
			// opciones en combo de ciudades
			$this->addVar('options_ciudades', HTTP_Session::get('options_ciudades', null), null);
			
			// opciones en combo de radio estaciones
			$this->addVar('options_res', HTTP_Session::get('options_res', null), null);
		
			// opciones en combo de modelos
			$this->addVar('options_modelos', HTTP_Session::get('options_modelos', null), null);
			*/
			/*
			// para que el text box mantenga su valor post submit
			$this->addVar('dias', $dias);
			
			// para que los checkboxes mantengan su estado post submit
			$this->addVar('activo_checado', $activo_checado);
			$this->addVar('auto_checado', $auto_checado);
			$this->addVar('km_checado', $km_checado);
			*/
						
		}
		
		$this->processSuccess();
	}
}
?>