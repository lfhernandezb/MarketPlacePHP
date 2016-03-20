<?php

include_once('GenericCommand.php');
include_once('../classes/domain/Comuna.php');
include_once('../classes/domain/Modelo.php');
include_once('../classes/domain/Usuario.php');
include_once('../classes/domain/Ciudad.php');
include_once('../classes/domain/Vehiculo.php');
include_once('../classes/domain/Modelo.php');
include_once('../classes/domain/Region.php');
include_once('../classes/domain/Campania.php');
include_once('../classes/domain/Parametro.php');
include_once('../classes/domain/Proveedor.php');
include_once('../classes/Util.php');


/***********************************************************************************************************
 * 
 * Ejecuta comandos ajax
 * 
 ***********************************************************************************************************/

class Ajax extends GenericCommand {
	function execute(){
		global $fc;
		
		$db = $fc->getLink();
		
		$req = $fc->request->req;
		
		if (isset($req)) {
			if ($req == 'getUsuario') {
				// se solicita el detalle de un usuario
				$id_usuario = $fc->request->id_usuario;
				
				$usuario = Usuario::getById($db, $id_usuario);
				
				$data =
					"{" .
					"	\"usuario\": {" .
					"        \"nombre\": \"" . htmlentities($usuario->nombre) . "\"," .
					"        \"correo\": \"" . $usuario->correo . "\"," .
					"        \"fecha_nacimiento\": \"" . $usuario->fecha_nacimiento . "\"," .
					"        \"hombre\": \"" . $usuario->hombre . "\"," .
					"        \"telefono\": \"" . $usuario->telefono . "\"," .
					"        \"fecha_vencimiento_licencia\": \"" . $usuario->fecha_vencimiento_licencia . "\"" .
					"    }" .
					"}";
				
				echo $data;
			}
			else if ($req == 'getVehiculo') {
				// se solicita el detalle de un vehiculo
				$id_vehiculo = $fc->request->id_vehiculo;
				$id_usuario = $fc->request->id_usuario;
				
				$vehiculo = Vehiculo::getById($db, $id_vehiculo, $id_usuario);
				
				$data =
					"{" .
					"	\"vehiculo\": {" .
					"        \"marca\": \"" . $vehiculo->marca . "\"," .
					"        \"modelo\": \"" . $vehiculo->modelo . "\"," .
					"        \"patente\": \"" . $vehiculo->patente . "\"," .
					"        \"anio\": \"" . $vehiculo->anio . "\"," .
					"        \"km\": \"" . $vehiculo->km . "\"," .
					"        \"id_usuario\": \"" . $vehiculo->id_usuario . "\"" .
					"    }" .
					"}";
				
				echo $data;
			}
			else if ($req == 'getUsuarioPagina') {
				
				$registros_por_pagina = 10;
				$numero_de_links = 25;
				
				$search_result = null;
				
				// se solicita la pagina 'page_num' del listado de usuarios en 'GestionaUsuarios'
				$page_num = $fc->request->page_num;
				
				Util::write_to_log("page_num " . $page_num);
				
				// obtengo desde donde se hizo la consulta para obtener los parametros
				
				$search_keyword = HTTP_session::get('search_keyword_usuario');
				
				if (isset($search_keyword)) {
					// busqueda por texto en esquina superior izquierda
					$search_result = Usuario::seekSpecial($db, $search_keyword, 'u.id_usuario', 'ASC', $page_num * $registros_por_pagina, $registros_por_pagina, false);
			
					if (!is_array($search_result->data)) {
						
					}
					
					
				}
				else {
					
					$search_keyword = HTTP_session::get('search_keyword_usuario_alt');
									
					if (isset($search_keyword)) {
						
						$parameters = array();						
						
						if (isset($search_keyword['activo']) && isset($search_keyword['dias'])) {
							$activo = $search_keyword['activo'];
							$dias = $search_keyword['dias'];
							$parameters['activo'] = $dias;
							$this->addVar('activo', $activo);
							$this->addVar('dias', $dias);
						}
						else if (isset($search_keyword['inactivo']) && isset($search_keyword['dias'])) {
							$inactivo = $search_keyword['inactivo'];
							$dias = $search_keyword['dias'];
							$parameters['inactivo'] = $dias;
							$this->addVar('inactivo', $inactivo);
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
						
						//$parameters['identificado'] = null;
						
						$search_result = Usuario::seek($db, $parameters, 'u.id_usuario', 'ASC', $page_num * $registros_por_pagina, $registros_por_pagina, false);

						if (!is_array($search_result->data)) {
							
						}
					}
					else {
						throw new Exception('No se pudo determinar el criterio de busqueda', -1);
					}
				}
				
				$data = array();
				
				$data['tbody'] = '';
				$data['pagination'] = '';
				
				foreach ($search_result->data as $usuario) {
					/*
					ob_start();
					var_dump($usuario);
					$result = ob_get_clean();
					
					Util::write_to_log("search_keyword " . $result);
					*/
					$data['tbody'] .=
						"    		    	<tr>\n" .
						"	                    <td></td>\n" .
						"	                    <td></td>\n" .
						"	                    <td>" . htmlentities($usuario['nombre']) . "</td>\n" .
						"	                    <td>{$usuario['correo']}</td>\n" .
						"\n" .	            
						"      	            	<td>No</td>\n" .
						"\n" .								
						"       				<td>\n" .
						"\n" .						
						"							<a href=\"#\" onClick=\"detalleUsuario({$usuario['id']}); return false\"><img src=\"images/detail.png\" alt=\"Detalle\" title=\"Detalle\" border=\"0\" /></a>\n" .
						"\n" .
						"							<a href=\"?do=VerVehiculos&id={$usuario['id']}\"><img src=\"images/car.png\" alt=\"\" title=\"Ver Veh&iacute;culos\" border=0 width=16 height=16 /></a>\n" .
						"\n" .	
						"							<a href=\"?do=VerLogs&id={$usuario['id']}\"><img src=\"images/log.png\" alt=\"\" title=\"Ver Logs\" border=0 width=16 height=16 /></a>\n" .
						"\n" .
						"							<!--a href=\"{$usuario['id']}\" class=\"confirm_delete\"><img src=\"images/trash.png\" alt=\"Elimina Usuario\" title=\"Elimina Usuario\" border=\"0\" /></a-->\n" .
						"\n" .
						"			            </td>\n" .
						"	                </tr>\n";				
				}
				
				HTTP_session::set('pagina_usuario', $page_num);
				
				// paginacion
				
				$page       = HTTP_session::get('pagina_usuario') + 1;
				$total      = HTTP_session::get('total_registros_usuario');
				$limit      = HTTP_session::get('registros_por_pagina_usuario');
				$links      = 15;
				
			    $last       = ceil( $total / $limit );
			 
			    $start      = ( ( $page + $links ) < $last ) ? $page : (($links - $last > 0) ? 1 : $last - $links);
			    $end        = ( ( $page + $links ) < $last ) ? $page + $links : $last;
			 
			    $html       = "<a class=\"previous_link\" href=\"javascript:previous();\"><< prev</a>";
			 
			    if ( $start > 1 ) {
			        $html   .= "<a class=\"page_link\" href=\"javascript:go_to_page(0)\" longdesc=\"0\">1</a>";
			        $html   .= "<a class=\"page_link\"><span>...</span></a>";
			    }
			 
			    for ( $i = $start ; $i <= $end; $i++ ) {
			        $html   .= "<a class=\"page_link\" href=\"javascript:go_to_page(" . ($i - 1) . ")\" longdesc=\"" . ($i - 1) . "\">" . $i . "</a>";
			    }
			 
			    if ( $end < $last ) {
			        $html   .= "<a class=\"page_link\"><span>...</span></a>";
			        $html   .= "<a class=\"page_link\" href=\"javascript:go_to_page(" . ($last - 1) . ")\" longdesc=\"" . ($last - 1) . "\">" . $last . "</a>";
			    }
			 
			    $html       .= "<a class=\"next_link\" href=\"javascript:next();\">next >></a>";

			    HTTP_session::set('html_paginacion', $html);
			    
			    $data['pagination'] = $html;
			    
			    // fin paginacion
				
				echo json_encode($data);
			}
			else if ($req == 'getVehiculoPagina') {
				
				$registros_por_pagina = 10;
				$numero_de_links = 15;
				
				$search_result = null;
				
				// se solicita la pagina 'page_num' del listado de vehiculos en 'GestionaUsuarios'
				$link_num = $fc->request->link_num;
				
				Util::write_to_log("link_num " . $link_num);
				
				$exito = null;
				
				if ($fc->request->must_refresh == '1') {
					
					$search_keyword = $fc->request->search_keyword;
					
					// guardo el valor de busqueda para utilizarlo en "ir a pagina" en links de paginacion
					HTTP_session::set('search_keyword_vehiculo', $fc->request->search_keyword);
				}
				else {
					$search_keyword = HTTP_session::get('search_keyword_vehiculo');
				}
				
				// obtengo el total de registros
				$search_result = Vehiculo::seekSpecial($db, $search_keyword, 'v.id_vehiculo', 'ASC', 0, 10000, false);
				
				// var_dump($search_result);
				
				$this->addVar('search_result', $search_result);
				
				$row_number = $db->RowCount() === false ? 0 : $db->RowCount();
				
				$this->addVar('row_number', $row_number);
				
				if ($row_number > 0) {
				
					$exito = true;
					
					$this->addVar('exito', $exito);
	
					
					// obtengo el set limitado de datos
					$search_result = Vehiculo::seekSpecial($db, $search_keyword, 'v.id_vehiculo', 'ASC', $link_num * $registros_por_pagina, $registros_por_pagina, false);
					
					$data = array();
					
					$data['tbody'] = '';
					$data['pagination'] = '';
				
					foreach ($search_result->data as $vehiculo) {
						/*
						ob_start();
						var_dump($vehiculo);
						$result = ob_get_clean();
						
						Util::write_to_log("search_keyword " . $result);
						*/
						$data['tbody'] .=
							"    		    	<tr>\n" .
							"	                    <td>{$vehiculo['marca']}</td>\n" .
							"	                    <td>{$vehiculo['modelo']}</td>\n" .
							"	                    <td>{$vehiculo['patente']}</td>\n" .
							"	                    <td>{$vehiculo['anio']}</td>\n" .
							"	                    <td>{$vehiculo['km']}</td>\n" .
							"\n" .								
							"       				<td>\n" .
							"\n" .						
							"							<a href=\"#\" onClick=\"detalleVehiculo({$vehiculo['id']}, {$vehiculo['id_usuario']}); return false\"><img src=\"images/detail.png\" alt=\"Detalle\" title=\"Detalle\" border=\"0\" /></a>\n" .
							"\n" .
							"			            </td>\n" .
							"	                </tr>\n";				
					}
					
					HTTP_session::set('pagina_vehiculo', $link_num);
					
					// paginacion
					
					$page       = $link_num + 1;
					$total      = $row_number;
					$limit      = $registros_por_pagina;
					$links      = $numero_de_links;
					
				    $last       = ceil( $total / $limit );
				 
				    $start      = ( ( $page + $links ) < $last ) ? $page : (($links - $last > 0) ? 1 : $last - $links);
				    $end        = ( ( $page + $links ) < $last ) ? $page + $links : $last;
				 
				    $html       = "<a class=\"previous_link\" href=\"javascript:previous();\"><< prev</a>";
				 
				    if ( $start > 1 ) {
				        $html   .= "<a class=\"page_link\" href=\"javascript:go_to_link(0, 0, null)\" longdesc=\"0\">1</a>";
				        $html   .= "<a class=\"page_link\"><span>...</span></a>";
				    }
				 
				    for ( $i = $start ; $i <= $end; $i++ ) {
				        $html   .= "<a class=\"page_link\" href=\"javascript:go_to_link(" . ($i - 1) . ", 0, null)\" longdesc=\"" . ($i - 1) . "\">" . $i . "</a>";
				    }
				 
				    if ( $end < $last ) {
				        $html   .= "<a class=\"page_link\"><span>...</span></a>";
				        $html   .= "<a class=\"page_link\" href=\"javascript:go_to_link(" . ($last - 1) . ", 0, null)\" longdesc=\"" . ($last - 1) . "\">" . $last . "</a>";
				    }
				 
				    $html       .= "<a class=\"next_link\" href=\"javascript:next();\">next >></a>";
	
				    HTTP_session::set('html_paginacion', $html);
				    
				    $data['pagination'] = $html;
				    
				    // fin paginacion
					
					echo json_encode($data);
				}
			}
			else if ($req == 'getProveedor') {
				// se solicita el detalle de un proveedor
				$id_proveedor = $fc->request->id_proveedor;
				
				$proveedor = Proveedor::getById($db, $id_proveedor);
				
				$data =
					"{" .
					"	\"proveedor\": {" .
					"        \"nombre\": \"" . htmlentities($proveedor->nombre) . "\"," .
					"        \"direccion\": \"" . $proveedor->direccion . "\"," .
					"        \"correo\": \"" . $proveedor->correo . "\"," .
					"        \"telefono\": \"" . $proveedor->telefono . "\"," .
					"        \"latitud\": \"" . $proveedor->latitud . "\"," .
					"        \"longitud\": \"" . $proveedor->longitud . "\"," .
					"        \"valor_minimo\": \"" . $proveedor->valor_minimo . "\"," .
					"        \"valor_maximo\": \"" . $proveedor->valor_maximo . "\"," .
					"        \"calificacion\": \"" . $proveedor->calificacion . "\"," .
					"        \"url\": \"" . $proveedor->url . "\"" .
					"    }" .
					"}";
				
				echo $data;
			}
			else if ($req == 'deleteProveedor') {
				// se solicita el eliminar un proveedor
				$id_proveedor = $fc->request->id_proveedor;
				
				$proveedor = Proveedor::getById($db, $id_proveedor);
				
				$resp = '0';
				
				if (!empty($proveedor)) {
					
					$proveedor->delete($db);
					
					$resp = '1';					
				}
				
				echo "{\"respuesta\": \"$resp\"}";
			}
			else if ($req == 'getParametro') {
				// se solicita el detalle de un proveedor
				$id_parametro = $fc->request->id_parametro;
				
				$parametro = Parametro::getById($db, $id_parametro);
				
				$data =
					"{" .
					"	\"parametro\": {" .
					"        \"llave\": \"" . $parametro->llave . "\"," .
					"        \"valor\": \"" . $parametro->valor . "\"" .
					"    }" .
					"}";
				
				echo $data;
			}
			else if ($req == 'getCampania') {
				// se solicita el detalle de un proveedor
				$id_campania = $fc->request->id_campania;
				
				$campania = Campania::getById($db, $id_campania);
				
				$data =
					"{" .
					"	\"campania\": {" .
					"        \"descripcion\": \"" . htmlentities($campania->descripcion) . "\"," .
					"        \"activa\": \"" . $campania->activa . "\"," .
					"        \"condicion\": \"" . $campania->condicion . "\"," .
					"        \"detalle\": \"" . mysql_real_escape_string(htmlentities($campania->detalle)) . "\"," .
					"        \"fecha_inicio\": \"" . $campania->fecha_inicio . "\"," .
					"        \"fecha_fin\": \"" . $campania->fecha_fin . "\"," .
					"        \"periodicidad\": \"" . $campania->periodicidad . "\"," .
					"        \"numero_impresiones\": \"" . $campania->numero_impresiones . "\"," .
					"        \"fecha_modificacion\": \"" . $campania->fecha_modificacion . "\"" .
					"    }" .
					"}";
				
				echo $data;
			}
			else if ($req == 'deleteCampania') {
				// se solicita el eliminar una campania
				$id_campania = $fc->request->id_campania;
				
				$campania = Campania::getById($db, $id_campania);
				
				$resp = '0';
				
				if (!empty($campania)) {
					
					$campania->delete($db);
					
					$resp = '1';					
				}
				
				echo "{\"respuesta\": \"$resp\"}";
			}
			else if ($req == 'getCiudades') {
				// se solicitan las ciudades pertenecientes a una determinada region; desde AgregaRepuesto
				$id_region = $fc->request->id_region;
				
				$parameters = array(
					'id_region' => "$id_region",
				);
				
				$ar = Ciudad::seek($db, $parameters, 'descripcion', 'ASC', null, null);
				
				$data = "{\"ciudades\": [";
				
				$i = 0;
				foreach ($ar as $ciudad) {
					$data .= "{\"id\": \"{$ciudad['id']}\", \"descripcion\": \"" . htmlentities($ciudad['descripcion']) . "\"}";
					$i++;
					if ($i < count($ar)) {
						$data .= ', ';
					}
				}
				
				$data .= "]}";
				
				HTTP_session::set('options_ciudades', $data);
				
				echo $data;
			}
			else if ($req == 'getComunas') {
				// se solicitan las ciudades pertenecientes a una determinada region; desde AgregaRepuesto
				$id_region = $fc->request->id_region;
				
				$parameters = array(
					'id_region' => "$id_region",
				);
				
				$ar = Comuna::seek($db, $parameters, 'descripcion', 'ASC', null, null);
				
				$data = "{\"comunas\": [";
				
				$i = 0;
				foreach ($ar->data as $comuna) {
					$data .= "{\"id\": \"{$comuna['id']}\", \"descripcion\": \"" . htmlentities($comuna['descripcion']) . "\"}";
					$i++;
					if ($i < count($ar->data)) {
						$data .= ', ';
					}
				}
				
				$data .= "]}";
				
				HTTP_session::set('options_comunas', $data);
				
				echo $data;
			}
			else if ($req == 'getRadioEstaciones') {
				// se solicitan las R/E pertenecientes a una determinada ciudad; desde AgregaRepuesto
				$id_ciudad = $fc->request->id_ciudad;
				
				$parameters = array(
					'id_ciudad' => "$id_ciudad",
				);
				
				$ar = RadioEstacion::seek($db, $parameters, 'descripcion', 'ASC', null, null);
				
				$data = "{\"res\": [";
				
				$i = 0;
				foreach ($ar as $re) {
					$data .= "{\"id\": \"{$re['id']}\", \"descripcion\": \"" . htmlentities($re['descripcion']) . "\"}";
					$i++;
					if ($i < count($ar)) {
						$data .= ', ';
					}
				}
				
				$data .= "]}";
				
				HTTP_session::set('options_res', $data);
				
				echo $data;
			}
			else if ($req == 'getModelos') {
				// se solicitan los modelos pertenecientes a un determinado fabricante; desde AgregaRepuesto
				$id_fabricante = $fc->request->id_fabricante;
				
				$parameters = array(
					'id_fabricante' => "$id_fabricante",
					'valido'		=> '',
				);
				
				$ar = Modelo::seek($db, $parameters, 'm.pid, m.descripcion', 'ASC', null, null);
				
				$data = "{\"modelos\": [";
				
				$i = 0;
				foreach ($ar as $modelo) {
					$data .= "{\"id\": \"{$modelo['id']}\", \"descripcion\": \"" . htmlentities($modelo['descripcion']). "\", \"pid\": \"" . htmlentities($modelo['pid']) . "\"}";
					$i++;
					if ($i < count($ar)) {
						$data .= ', ';
					}
				}
				
				$data .= "]}";
				
				HTTP_session::set('options_modelos', $data);
				
				echo $data;
			}
			else if ($req == 'getEncargados') {
				// se solicitan los encargados pertenecientes a una determinada region; desde AgregaRepuesto
				$id_region = $fc->request->id_region;
				
				$parameters = array(
					'id_region' => "$id_region",
				);
				
				$ar = Encargado::seek($db, $parameters, 'e.descripcion', 'ASC', null, null);
				
				$data = "{\"encargados\": [";
				
				$i = 0;
				foreach ($ar as $encargado) {
					$data .= "{\"id\": \"{$encargado['id']}\", \"descripcion\": \"" . htmlentities($encargado['descripcion']). "\"}";
					$i++;
					if ($i < count($ar)) {
						$data .= ', ';
					}
				}
				
				$data .= "]}";
				
				HTTP_session::set('options_encargados', $data);
				
				echo $data;
			}
			else if ($req == 'getMovimiento') {
				// se solicita el detalle de un movimiento
				$id_repuesto_movimiento = $fc->request->id_repuesto_movimiento;
				
				$rm = RepuestoMovimiento::getById($db, $id_repuesto_movimiento);
				
				$repuesto = Repuesto::getById($db, $rm->id_repuesto);
				
				$tipo = TipoRepuestoMovimiento::getByID($db, $rm->id_tipo_repuesto_movimiento);
				
				if ($tipo->descripcion == 'egreso') {
					$motivo_movimiento = MotivoMovimiento::getByIdRM($db, $rm->id);
					
					$motivo = Motivo::getByID($db, $motivo_movimiento->id_motivo);
				}
				
				$usuario = Usuario::getByID($db, $rm->id_usuario);
				
				$data =
					"{" .
					"	\"tipo_repuesto_movimiento\": {" .
					"		\"descripcion\": \"" . htmlentities($tipo->descripcion) . "\"" .
					"	}," . 
					"	\"repuesto_movimiento\": {" .
					"		\"cantidad\": \"" . $rm->cantidad . "\"," .
					"		\"fecha\": \"" . $rm->fecha . "\"" .
					"	},";
				
				if ($tipo->descripcion == 'egreso') {
					$data .=
					"	\"motivo_movimiento\": {" .
					"		\"motivo\": \"" . $motivo->descripcion . "\"," .
					"		\"valor\": \"" . $motivo_movimiento->valor . "\"" .
					"	},"; 
				}
				
				$data .=
					"	\"usuario\": {" .
					"		\"apellidos\": \"" . htmlentities($usuario->apellidos) . "\"," .
					"		\"nombre\": \"" . htmlentities($usuario->nombre) . "\"" .
					"	}," . 
					"	\"repuesto\": {" .
					"        \"plataforma\": \"" . $repuesto->plataforma . "\"," .
					"        \"region\": \"" . htmlentities($repuesto->region) . "\"," .
					"        \"ciudad\": \"" . htmlentities($repuesto->ciudad) . "\"," .
					"        \"radio_estacion\": \"" . htmlentities($repuesto->radio_estacion) . "\"," .
					"        \"fabricante\": \"" . $repuesto->fabricante . "\"," .
					"        \"modelo\": \"" . htmlentities($repuesto->modelo) . "\"," .
					"        \"sap\": \"" . $repuesto->sap . "\"," .
					"        \"serial\": \"" . $repuesto->serial . "\"," .
					"        \"nombre\": \"" . htmlentities($repuesto->nombre) . "\"," .
					"        \"posicion\": \"" . htmlentities($repuesto->posicion) . "\"," .
					"        \"ip\": \"" . $repuesto->ip . "\"," .
					"        \"consola_activa\": \"" . $repuesto->consola_activa . "\"," .
					"        \"consola_standby\": \"" . $repuesto->consola_standby . "\"," .
					"        \"ubicacion\": \"" . htmlentities($repuesto->ubicacion) . "\"," .
					"        \"sla\": \"" . htmlentities($repuesto->sla) . "\"";
				
				if ($tipo->descripcion == 'egreso') {
					$data .=
					"," .
					"        \"posicion\": \"" . htmlentities($repuesto->posicion) . "\"," .
					"        \"ip\": \"" . $repuesto->ip . "\"," .
					"        \"consola_activa\": \"" . $repuesto->consola_activa . "\"," .
					"        \"consola_standby\": \"" . $repuesto->consola_standby . "\"," .
					"        \"ubicacion\": \"" . htmlentities($repuesto->ubicacion) . "\"";
				}
				
				$data .=
					"    }" .
					"}";
				
				echo $data;
			}
			else if ($req == 'getRepuesto') {
				// se solicita el detalle de un repuesto
				$id_repuesto = $fc->request->id_repuesto;
				
				$repuesto = Repuesto::getById($db, $id_repuesto);
				
				$data =
					"{" .
					"	\"repuesto\": {" .
					"        \"plataforma\": \"" . $repuesto->plataforma . "\"," .
					"        \"region\": \"" . htmlentities($repuesto->region) . "\"," .
					"        \"ciudad\": \"" . htmlentities($repuesto->ciudad) . "\"," .
					"        \"radio_estacion\": \"" . htmlentities($repuesto->radio_estacion) . "\"," .
					"        \"fabricante\": \"" . $repuesto->fabricante . "\"," .
					"        \"modelo\": \"" . htmlentities($repuesto->modelo) . "\"," .
					"        \"sap\": \"" . $repuesto->sap . "\"," .
					"        \"serial\": \"" . $repuesto->serial . "\"," .
					"        \"nombre\": \"" . htmlentities($repuesto->nombre) . "\"," .
					"        \"posicion\": \"" . htmlentities($repuesto->posicion) . "\"," .
					"        \"ip\": \"" . $repuesto->ip . "\"," .
					"        \"consola_activa\": \"" . $repuesto->consola_activa . "\"," .
					"        \"consola_standby\": \"" . $repuesto->consola_standby . "\"," .
					"        \"ubicacion\": \"" . htmlentities($repuesto->ubicacion) . "\"," .
					"        \"sla\": \"" . htmlentities($repuesto->sla) . "\"," .
					"        \"encargado\": \"" . htmlentities($repuesto->encargado) . "\"," .
					"        \"cantidad\": \"" . htmlentities($repuesto->cantidad) . "\"" .
					"    }" .
					"}";
				
				echo $data;
			}
			else if ($req == 'getRegion') {
				// se solicita el detalle de una region
				$id_region = $fc->request->id;
				
				$region = Region::getById($db, $id_region);
				
				$data =
					"{" .
					"	\"region\": {" .
					"        \"descripcion\": \"" . htmlentities($region->descripcion) . "\"," .
					"        \"nombre\": \"" . htmlentities($region->nombre) . "\"" .
					"    }" .
					"}";
				
				echo $data;
			}
			else if ($req == 'delRepuesto') {
				// se solicita eliminar un repuesto (borrado en 1)
				$id_repuesto = $fc->request->id_repuesto;
				
				$repuesto = Repuesto::getById($db, $id_repuesto);
				
				$resp = '0';
				
				if (!empty($repuesto)) {
					
					$repuesto->borrado = 1;
					
					$repuesto->update($db);
					
					$resp = '1';					
				}
				
				echo "{\"respuesta\": \"$resp\"}";
								
			}
			else if ($req == 'delUsuario') {
				// se solicita eliminar un usuario (borrado en 1)
				$id_usuario = $fc->request->id_usuario;
				
				$usuario = Usuario::getByID($db, $id_usuario);
				
				$resp = '0';
				
				if (!empty($usuario)) {
					
					$usuario->borrado = 1;
					
					$usuario->update($db);
					
					$resp = '1';					
				}
				
				echo "{\"respuesta\": \"$resp\"}";
								
			}
			else if ($req == 'delZona') {
				// se solicita eliminar una zona
				$id_zona = $fc->request->id;
				
				$resp = '0';
				
				try {
					
					try {
	
						if (!$db->TransactionBegin()) {
							throw new Exception('Error al iniciar transaccion: ' . $db->Error(), $db->ErrorNumber(), null);
						}
						
						ZonaRegion::delete($db, $id_zona);
						
						$zona = Zona::getByID($db, $id_zona);
						
						$zona->delete($db);
						
						// commit
						
						if (!$db->TransactionEnd()) {
							throw new Exception('Error al comitear transaccion: ' . $db->Error(), $db->ErrorNumber(), null);
						}
						
						$resp = '1';
						
					} catch (Exception $e) {
						// rollback
						if ($bInTransaction) {
							$db->TransactionRollback();
						}
						
						throw new Exception($e->getMessage(), $e->getCode(), $e->getPrevious());
					}
				
				} catch (Exception $e) {
				
				}
				
				echo "{\"respuesta\": \"$resp\"}";
								
			}
			else if ($req == 'resetPassword') {
				// se solicita eliminar un usuario (borrado en 1)
				$id_usuario = $fc->request->id_usuario;
				
				$usuario = Usuario::getByID($db, $id_usuario);
				
				$resp = '0';
				
				if (!empty($usuario)) {
					
					$usuario->activo = 0;
					
					$password = mt_rand(0, 9999);
					
					$usuario->contrasena = md5($password);					
					
					$usuario->update($db);
					
					$headers = null;
					
					$ar_aux = split('\?', $_SERVER["HTTP_REFERER"]);
					
					if ($fc->appSettings['html_email'] == 'yes') {
						// correo html
						// correo de notificacion
						// To send HTML mail, the Content-type header must be set
						$headers = '';
						
						$headers .= 'MIME-Version: 1.0' . "\r\n";
						$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
						
						// Additional headers
						$headers .= "To: \"{$usuario->nombre} {$usuario->apellidos}\" <{$usuario->email}>\r\n";
						$headers .= "From: SiGREP <sigrep>\r\n";
						// $headers .= 'Cc: birthdayarchive@example.com' . "\r\n";
						$headers .= "Bcc: <lfhernandez@dsoft.cl>\r\n";					
						
						$content_template = htmlentities(file_get_contents('../templates/correo_reseteo_usuario.html'));
						
						$mail_body = html_entity_decode(sprintf($content_template, 
							$usuario->nombre, 
							$ar_aux[0] . "?do=ActivaCuenta&idUsuario={$usuario->id}", 
							$usuario->nombre_usuario, 
							$password));
						
						//echo "<br>$mail_body<br>";
					}
					else {
						// correo en texto plano
						$mail_body = sprintf("Estimado %s,\r\nLa contrasena de su cuenta ha sido reseteada.\r\nFavor ingrese al sistema en la URL %s con la cuenta %s y contrasena %s\r\n\r\nSaludos,\r\nEl Administrador", 
							$usuario->nombre, 
							$ar_aux[0] . "?do=ActivaCuenta&idUsuario={$usuario->id}", 
							$usuario->nombre_usuario, 
							$password);
					}
	
					mail($usuario->email, "SiGREP, Contrasena restablecida", $mail_body, $headers);
					
					
					
					$resp = '1';					
				}
				
				echo "{\"respuesta\": \"$resp\"}";
								
			}
			else if ($req == 'UsernameExistente') {
				// se solicita validar la existencia de algun usuario con el username indicado
				
				$usuario = Usuario::getByUsername($db, $fc->request->username);
				
				$resp = 0;
				
				if (isset($usuario)) {
					$resp = 1;					
				}
				
				echo "{\"respuesta\": \"$resp\"}";
			}
			else if ($req == 'EmailExistente') {
				// se solicita validar la existencia de algun usuario con el email indicado
				
				$usuario = Usuario::getByEmail($db, $fc->request->email);
				
				$resp = '0';
				
				if (isset($usuario)) {
					$resp = '1';					
				}
				
				echo "{\"respuesta\": \"$resp\"}";
			}
			else if ($req == 'ciudadExistente') {
				// se solicita validar la existencia de algun usuario con el email indicado
				
				$ciudad = Ciudad::getByDescripcion($db, $fc->request->param);
				
				$resp = '0';
				
				if (isset($ciudad)) {
					$resp = '1';					
				}
				
				echo "{\"respuesta\": \"$resp\"}";
			}
			else if ($req == 'reExistente') {
				// se solicita validar la existencia de algun usuario con el email indicado
				
				$re = RadioEstacion::getByDescripcion($db, $fc->request->param);
				
				$resp = '0';
				
				if (isset($re)) {
					$resp = '1';					
				}
				
				echo "{\"respuesta\": \"$resp\"}";
			}
			else if ($req == 'fabricanteExistente') {
				// se solicita validar la existencia de algun usuario con el email indicado
				
				$re = Fabricante::getByDescripcion($db, $fc->request->param);
				
				$resp = '0';
				
				if (isset($re)) {
					$resp = '1';					
				}
				
				echo "{\"respuesta\": \"$resp\"}";
			}
			else if ($req == 'modeloExistente') {
				// se solicita validar la existencia de algun usuario con el email indicado
				
				$re = Modelo::getByDescripcion($db, $fc->request->param);
				
				$resp = '0';
				
				if (isset($re)) {
					$resp = '1';					
				}
				
				echo "{\"respuesta\": \"$resp\"}";
			}
			else if ($req == 'sapExistente') {
				// se solicita validar la existencia de algun usuario con el email indicado
				
				$re = Modelo::getBySAP($db, $fc->request->param);
				
				$resp = '0';
				
				if (isset($re)) {
					$resp = '1';					
				}
				
				echo "{\"respuesta\": \"$resp\"}";
			}
			else if ($req == 'getModeloPorSAP') {
				// se solicita obtener el modelo de un equipo por su codigo SAP
				
				$re = Modelo::getBySAP($db, $fc->request->sap);
				
				$resp = '0';
				
				if (isset($re)) {
					echo "{\"id_modelo\": \"{$re->id}\", \"id_fabricante\": \"{$re->id_fabricante}\", \"descripcion\": \"{$re->descripcion}\", \"pid\": \"{$re->pid}\", \"sap\": \"{$re->sap}\"}";					
				}
				else {
					echo "{\"id_modelo\": \"0\", \"id_fabricante\": \"0\", \"descripcion\": \"0\", \"pid\": \"0\", \"sap\": \"0\"}";
				}
				
			}
			else if ($req == 'getModeloPorID') {
				// se solicita obtener el modelo de un equipo por su ID
				
				$re = Modelo::getById($db, $fc->request->id_modelo);
				
				$resp = '0';
				
				if (isset($re)) {
					echo "{\"id_modelo\": \"{$re->id}\", \"id_fabricante\": \"{$re->id_fabricante}\", \"descripcion\": \"{$re->descripcion}\", \"pid\": \"{$re->pid}\", \"sap\": \"{$re->sap}\"}";					
				}
				else {
					echo "{\"id_modelo\": \"0\", \"id_fabricante\": \"0\", \"descripcion\": \"0\", \"pid\": \"0\", \"sap\": \"0\"}";
				}
				
			}
			else if ($req == 'descripcionExistente') {
				// se solicita validar la existencia de algun usuario con el email indicado
				
				$re = Modelo::getByDescripcion($db, $fc->request->param);
				
				$resp = '0';
				
				if (isset($re)) {
					$resp = '1';					
				}
				
				echo "{\"respuesta\": \"$resp\"}";
			}
			else if ($req == 'serialExistente') {
				// se solicita validar la existencia de algun usuario con el email indicado
				
				$parameters = array(
					'id_modelo' => $fc->request->idModelo,
					'serial' => $fc->request->serial,
					'no borrado' => '',
				);
				
				$re = Repuesto::seek($db, $parameters, null, null, 0, 10000);
				
				$resp = '0';
				
				if (!empty($re)) {
					$resp = '1';					
				}
				
				echo "{\"respuesta\": \"$resp\"}";
			}
			else if ($req == 'encargadoExistente') {
				// se solicita validar la existencia de algun usuario con el email indicado
				
				$parameters = array(
					'descripcion' => $fc->request->param,
				);
				
				$re = Encargado::seek($db, $parameters, null, null, 0, 10000);
				
				$resp = '0';
				
				if (!empty($re)) {
					$resp = '1';					
				}
				
				echo "{\"respuesta\": \"$resp\"}";
			}
			else if ($req == 'plataformaExistente') {
				// se solicita validar la existencia de algun usuario con el email indicado
				
				$parameters = array(
					'descripcion' => $fc->request->param,
				);
				
				$re = Plataforma::seek($db, $parameters, null, null, 0, 10000);
				
				$resp = '0';
				
				if (!empty($re)) {
					$resp = '1';					
				}
				
				echo "{\"respuesta\": \"$resp\"}";
			}
			else if ($req == 'regionDescripcionExistente') {
				// se solicita validar la existencia de una region con la descripcion indicada e id distindo al enviado
				
				$parameters = array(
					'descripcion' => "'{$fc->request->descripcion}'",
					'id distinto' => $fc->request->id,
				);
				
				$rg = Region::seek($db, $parameters, null, null, 0, 10000);
				
				$resp = '0';
				
				if (!empty($rg)) {
					$resp = '1';					
				}
				
				echo "{\"respuesta\": \"$resp\"}";
			}
			else if ($req == 'regionNombreExistente') {
				// se solicita validar la existencia de una region con el nombre indicado e id distindo al enviado
				
				$parameters = array(
					'nombre' => "'{$fc->request->nombre}'",
					'id distinto' => $fc->request->id,
				);
				
				$rg = Region::seek($db, $parameters, null, null, 0, 10000);
				
				$resp = '0';
				
				if (!empty($rg)) {
					$resp = '1';					
				}
				
				echo "{\"respuesta\": \"$resp\"}";
			}
			else if ($req == 'zonaExistente') {
				// se solicita validar la existencia de una region con el nombre indicado e id distindo al enviado
				
				$parameters = array(
					'descripcion' => "'{$fc->request->descripcion}'",
				);
				
				$z = Zona::seek($db, $parameters, null, null, 0, 10000);
				
				$resp = '0';
				
				if (!empty($z)) {
					$resp = '1';					
				}
				
				echo "{\"respuesta\": \"$resp\"}";
			}
			else if ($req == 'modificaRegion') {
				// se solicita modificar region
				
				$resp = null;
				
				$rg = new Region();
				
				$rg->id = $fc->request->id;
				$rg->descripcion = $fc->request->descripcion;
				$rg->nombre = $fc->request->nombre;
				
				try {
					$rg->update($db);

					$resp = '0';
				} catch (Exception $e) {
					$resp = '1';
				}
				
				echo "{\"respuesta\": \"$resp\"}";
			}
			else if ($req == 'getDetalleExistenciasPorRegion') {
				// se solicitan el detalle de existencias por region; llamado desde ReporteExistenciasPorRegion
				$id_region = $fc->request->id_region;
				$id_plataforma = $fc->request->id_plataforma;
				$id_fabricante = $fc->request->id_fabricante;
				$id_modelo = $fc->request->id_modelo;
				
				$items = Repuesto::getStockByRegionDetail($db, $id_region, $id_plataforma, $id_fabricante, $id_modelo);
				
				$data = "{\"items\": [";
				
				$i = 0;
				foreach ($items as $item) {
					$data .= "{\"ciudad\": \"{$item['ciudad']}\", \"re\": \"" . htmlentities($item['re']) . "\", \"stock\": \"" . htmlentities($item['stock']) . "\"}";
					$i++;
					if ($i < count($items)) {
						$data .= ', ';
					}
				}
				
				$data .= "]}";
				
				echo $data;
			}
		}
	}
}
?>
