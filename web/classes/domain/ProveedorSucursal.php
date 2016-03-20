<?php

/**
 * 
 */

include_once('mysql.class.php');

include_once 'ProveedorSucursalServicio.php';
include_once 'ProveedorSucursalMarca.php';
include_once 'ProveedorSucursalCombustible.php';

/**
 * @author Luis Hernandez
 *
 */

class ProveedorSucursal
{
    private $_id;
    private $_id_proveedor;
    private $_id_comuna;
    private $_descripcion;
    private $_direccion;
    private $_telefono1;
    private $_telefono2;
    private $_fax;
    private $_correo;
    private $_fotos;
    private $_fecha_creacion;
    private $_multimarca;
    private $_latitud;
    private $_longitud;

    private static $_str_sql = "
  SELECT
  pr.id_proveedor_sucursal AS id,
  pr.id_proveedor AS id_proveedor,
  pr.id_comuna AS id_comuna,
  pr.descripcion AS descripcion,
  pr.direccion AS direccion,
  pr.telefono1 AS telefono1,
  pr.telefono2 AS telefono2,
  pr.fax AS fax,
  pr.correo AS correo,
  pr.fotos AS fotos,
  DATE_FORMAT(pr.fecha_creacion, '%Y-%m-%d %H:%i:%s') AS fecha_creacion,
  0+pr.multimarca AS multimarca,
  pr.latitud AS latitud,
  pr.longitud AS longitud
  FROM proveedor_sucursal pr";

	private static $_str_sql_special = "
  SELECT DISTINCT(
  ps.id_proveedor_sucursal) AS id,
  pr.id_proveedor,
  ps.descripcion,
  ps.direccion,
  c.descripcion AS comuna,
  r.descripcion AS region
  FROM proveedor_sucursal ps
  JOIN proveedor pr ON pr.id_proveedor = ps.id_proveedor
  JOIN comuna c ON c.id_comuna = ps.id_comuna
  JOIN region r ON r.id_region = c.id_region
  LEFT JOIN proveedor_sucursal_marca psm ON psm.id_proveedor_sucursal = ps.id_proveedor_sucursal
  LEFT JOIN marca m ON m.id_marca = psm.id_marca
  LEFT JOIN proveedor_sucursal_combustible psc ON psc.id_proveedor_sucursal = ps.id_proveedor_sucursal
  LEFT JOIN combustible co ON co.id_combustible = psc.id_combustible
  LEFT JOIN proveedor_sucursal_servicio pss ON pss.id_proveedor_sucursal = ps.id_proveedor_sucursal
  LEFT JOIN servicio s ON s.id_servicio = pss.id_servicio";
    
	public function __construct() {
        $this->_id = null;
        $this->_id_proveedor = null;
        $this->_id_comuna = null;
        $this->_descripcion = null;
        $this->_direccion = null;
        $this->_telefono1 = null;
        $this->_telefono2 = null;
        $this->_fax = null;
        $this->_correo = null;
        $this->_fotos = null;
        $this->_fecha_creacion = null;
        $this->_multimarca = null;
        $this->_latitud = null;
        $this->_longitud = null;

    }

    public function __set($name, $value) {

        //echo "Setting '$name' to '$value'\n";
        switch ($name) {
            case "id" :
                $this->_id = $value;
                break;
            case "id_proveedor" :
                $this->_id_proveedor = $value;
                break;
            case "id_comuna" :
                $this->_id_comuna = $value;
                break;
            case "descripcion" :
                $this->_descripcion = $value;
                break;
            case "direccion" :
                $this->_direccion = $value;
                break;
            case "telefono1" :
                $this->_telefono1 = $value;
                break;
            case "telefono2" :
                $this->_telefono2 = $value;
                break;
            case "fax" :
                $this->_fax = $value;
                break;
            case "correo" :
                $this->_correo = $value;
                break;
            case "fotos" :
                $this->_fotos = $value;
                break;
            case "fecha_creacion" :
                $this->_fecha_creacion = $value;
                break;
            case "multimarca" :
                $this->_multimarca = $value;
                break;
            case "latitud" :
                $this->_latitud = $value;
                break;
            case "longitud" :
                $this->_longitud = $value;
                break;
            default:
                $trace = debug_backtrace();
                trigger_error(
                    'Undefined property via __set(): ' . $name .
                    ' in ' . $trace[0]['file'] .
                    ' on line ' . $trace[0]['line'],
                    E_USER_NOTICE);
        }
    }

    public function __get($name) {

        //echo "Getting '$name'\n";
        switch ($name) {
            case "id" :
                return $this->_id;
            case "id_proveedor" :
                return $this->_id_proveedor;
            case "id_comuna" :
                return $this->_id_comuna;
            case "descripcion" :
                return $this->_descripcion;
            case "direccion" :
                return $this->_direccion;
            case "telefono1" :
                return $this->_telefono1;
            case "telefono2" :
                return $this->_telefono2;
            case "fax" :
                return $this->_fax;
            case "correo" :
                return $this->_correo;
            case "fotos" :
                return $this->_fotos;
            case "fecha_creacion" :
                return $this->_fecha_creacion;
            case "multimarca" :
                return $this->_multimarca;
            case "latitud" :
                return $this->_latitud;
            case "longitud" :
                return $this->_longitud;
            default:
                $trace = debug_backtrace();
                trigger_error(
                    'Undefined property via __get(): ' . $name .
                    ' in ' . $trace[0]['file'] .
                    ' on line ' . $trace[0]['line'],
                    E_USER_NOTICE);
        }
    }

    public static function fromArray($p_ar) {
        $ret = new ProveedorSucursal();

        $ret->_id = $p_ar[0]['id'];
        $ret->_id_proveedor = $p_ar[0]['id_proveedor'];
        $ret->_id_comuna = $p_ar[0]['id_comuna'];
        $ret->_descripcion = $p_ar[0]['descripcion'];
        $ret->_direccion = $p_ar[0]['direccion'];
        $ret->_telefono1 = $p_ar[0]['telefono1'];
        $ret->_telefono2 = $p_ar[0]['telefono2'];
        $ret->_fax = $p_ar[0]['fax'];
        $ret->_correo = $p_ar[0]['correo'];
        $ret->_fotos = $p_ar[0]['fotos'];
        $ret->_fecha_creacion = $p_ar[0]['fecha_creacion'];
        $ret->_multimarca = $p_ar[0]['multimarca'];
        $ret->_latitud = $p_ar[0]['latitud'];
        $ret->_longitud = $p_ar[0]['longitud'];

        return $ret;
    }

    public static function getByParameter($p_db, $p_key, $p_value) {
        $ret = null;
        
        $str_sql = self::$_str_sql .
            "  WHERE pr." . $p_key . " = " . $p_value .
            "  LIMIT 0, 1";
        
        //echo '<br>' . $str_sql . '<br>';
        
        $ar = $p_db->QueryArray($str_sql, MYSQL_ASSOC);

        if (is_array($ar)) {
            $ret = self::fromArray($ar);
         }
        else if ($p_db->RowCount() != 0) {
            throw new Exception('Error al obtener registro: ' . $p_db->Error(), $p_db->ErrorNumber(), null);
        }

        return $ret;

    }

    public static function getById($p_db, $p_id) {
        return self::getByParameter($p_db, "id_proveedor_sucursal", $p_id);
    }
    
    public function getServicios($p_db) {
    	
    	$parameters = array(
    		'id_proveedor_sucursal'	=>	$this->_id
    	);
    	
    	return ProveedorSucursalServicio::seek($p_db, $parameters, null, null, 0, 10000);
    }
    
    public function getMarcas($p_db) {
    	
    	$parameters = array(
    		'id_proveedor_sucursal'	=>	$this->_id
    	);
    	
    	return ProveedorSucursalMarca::seek($p_db, $parameters, null, null, 0, 10000);
    }
    
    public function getCombustibles($p_db) {
    	
    	$parameters = array(
    		'id_proveedor_sucursal'	=>	$this->_id
    	);
    	
    	return ProveedorSucursalCombustible::seek($p_db, $parameters, null, null, 0, 10000);
    }
    
    public static function seek($p_db, $p_parameters, $p_order, $p_direction, $p_offset, $p_limit, $p_get_total = false) {

        $result         = new stdClass();
        $array_clauses  = array();

        $str_sql = self::$_str_sql_special;

        foreach($p_parameters as $key => $value) {
            if ($key == 'id_proveedor_sucursal') {
                $array_clauses[] = "pr.id_proveedor_sucursal = $value";
            }
            else if ($key == 'id_proveedor') {
                $array_clauses[] = "pr.id_proveedor = $value";
            }
            else if ($key == 'id_comuna') {
                $array_clauses[] = "pr.id_comuna = $value";
            }
            else {
                throw new Exception('Parametro no soportado: ' . $key, null, null);
            }
        }

        $bFirstTime = false;

        foreach($array_clauses as $clause) {
            if (!$bFirstTime) {
                 $bFirstTime = true;
                 $str_sql .= ' WHERE ';
            }
            else {
                 $str_sql .= ' AND ';
            }
            $str_sql .= $clause;
        }
        

        if ($p_get_total) {

            $rs = $p_db->Query($str_sql);

            $num_rows = mysql_num_rows($rs);

            $result->total  = $num_rows;
        }

        if (isset($p_order) && isset($p_direction)) {
            $str_sql .= " ORDER BY $p_order $p_direction";
        }
        
        if (isset($p_offset) && isset($p_limit)) {
            $str_sql .= "  LIMIT $p_offset, $p_limit";
        }

        //echo "<br>" . str_sql . "<br>";

        $data = $p_db->QueryArray($str_sql, MYSQL_ASSOC);

        if (!is_array($data)) {
            $data = null;

            if ($p_db->RowCount() != 0) {
                throw new Exception('Error al obtener registro: ' . $p_db->Error(), $p_db->ErrorNumber(), null);
            }
        }

        $result->data   = $data;

        return $result;
    }

    public function update($p_db) {

        $str_sql =
            "  UPDATE proveedor_sucursal" .
            "  SET" .
            "  descripcion = " . (isset($this->_descripcion) ? "'{$this->_descripcion}'" : 'null') . "," .
            "  direccion = " . (isset($this->_direccion) ? "'{$this->_direccion}'" : 'null') . "," .
            "  telefono1 = " . (isset($this->_telefono1) ? "'{$this->_telefono1}'" : 'null') . "," .
            "  telefono2 = " . (isset($this->_telefono2) ? "'{$this->_telefono2}'" : 'null') . "," .
            "  fax = " . (isset($this->_fax) ? "'{$this->_fax}'" : 'null') . "," .
            "  correo = " . (isset($this->_correo) ? "'{$this->_correo}'" : 'null') . "," .
            "  fotos = " . (isset($this->_fotos) ? "'{$this->_fotos}'" : 'null') . "," .
            "  fecha_creacion = " . (isset($this->_fecha_creacion) ? "STR_TO_DATE('{$this->_fecha_creacion}', '%Y-%m-%d %H:%i:%s')" : 'null') . "," .
            "  multimarca = " . (isset($this->_multimarca) ? "b'{$this->_multimarca}'" : 'null') . "," .
            "  latitud = " . (isset($this->_latitud) ? "{$this->_latitud}" : 'null') . "," .
            "  longitud = " . (isset($this->_longitud) ? "{$this->_longitud}" : 'null') .
            "  WHERE" .
            "  id_proveedor_sucursal = {$this->_id}";

        //echo '<br>' . $str_sql . '<br>';

        if ($p_db->Query($str_sql) === false) {
            throw new Exception('Error al actualizar registro: ' . $p_db->Error(), $p_db->ErrorNumber(), null);
        }
    }

    public function insert($p_db) {

        $str_sql =
            "  INSERT INTO proveedor_sucursal" .
            "  (" .
            "  id_proveedor, " .
            "  id_comuna, " .
            "  descripcion, " .
            "  direccion, " .
            "  telefono1, " .
            "  telefono2, " .
            "  fax, " .
            "  correo, " .
            "  fotos, " .
            "  fecha_creacion, " .
            "  latitud, " .
            "  longitud)" .
            "  VALUES" .
            "  (" .
            "  " . (isset($this->_id_proveedor) ? "{$this->_id_proveedor}" : 'null') . "," .
            "  " . (isset($this->_id_comuna) ? "{$this->_id_comuna}" : 'null') . "," .
            "  " . (isset($this->_descripcion) ? "'{$this->_descripcion}'" : 'null') . "," .
            "  " . (isset($this->_direccion) ? "'{$this->_direccion}'" : 'null') . "," .
            "  " . (isset($this->_telefono1) ? "'{$this->_telefono1}'" : 'null') . "," .
            "  " . (isset($this->_telefono2) ? "'{$this->_telefono2}'" : 'null') . "," .
            "  " . (isset($this->_fax) ? "'{$this->_fax}'" : 'null') . "," .
            "  " . (isset($this->_correo) ? "'{$this->_correo}'" : 'null') . "," .
            "  " . (isset($this->_fotos) ? "'{$this->_fotos}'" : 'null') . "," .
            "  " . (isset($this->_fecha_creacion) ? "STR_TO_DATE('{$this->_fecha_creacion}', '%Y-%m-%d %H:%i:%s')" : 'null') . "," .
            "  " . (isset($this->_latitud) ? "{$this->_latitud}" : 'null') . "," .
            "  " . (isset($this->_longitud) ? "{$this->_longitud}" : 'null') .
            "  )";

        //echo '<br>' . $str_sql . '<br>';

        if ($p_db->Query($str_sql) === false) {
            throw new Exception('Error al insertar registro: ' . $p_db->Error(), $p_db->ErrorNumber(), null);
        }


        $ar_id = $p_db->QueryArray('SELECT LAST_INSERT_ID()');

        $this->_id = $ar_id[0][0];
    }

    public function delete($p_db) {


        $str_sql =
            "  DELETE FROM  proveedor_sucursal" .
            "  WHERE" .
            "  id_proveedor_sucursal = {$this->_id}";

        //echo '<br>' . $str_sql . '<br>';

        if ($p_db->Query($str_sql) === false) {
            throw new Exception('Error al borrar registro: ' . $p_db->Error(), $p_db->ErrorNumber(), null);
        }
    }


    public function load($p_db) {
        $obj = self::getById($p_db, $this->_id);

        if ($obj != null) {

            $this->_id = $obj->_id;
            $this->_id_proveedor = $obj->_id_proveedor;
            $this->_id_comuna = $obj->_id_comuna;
            $this->_descripcion = $obj->_descripcion;
            $this->_direccion = $obj->_direccion;
            $this->_telefono1 = $obj->_telefono1;
            $this->_telefono2 = $obj->_telefono2;
            $this->_fax = $obj->_fax;
            $this->_correo = $obj->_correo;
            $this->_fotos = $obj->_fotos;
            $this->_fecha_creacion = $obj->_fecha_creacion;
            $this->_multimarca = $obj->_multimarca;
            $this->_latitud = $obj->_latitud;
            $this->_longitud = $obj->_longitud;
        }
    }

    public function save($p_db) {
        $obj = self::getById($p_db, $this->_id);

        if ($obj != null) {

            update($p_db);
        }
        else {
            insert($p_db);
        }
    }

    public function toString() {
        return "ProveedorSucursal [" .
               "    _id = " . (isset($this->_id) ? "{$this->_id}" : 'null') . "," .
               "    _id_proveedor = " . (isset($this->_id_proveedor) ? "{$this->_id_proveedor}" : 'null') . "," .
               "    _id_comuna = " . (isset($this->_id_comuna) ? "{$this->_id_comuna}" : 'null') . "," .
               "    _descripcion = " . (isset($this->_descripcion) ? "'{$this->_descripcion}'" : 'null') . "," .
               "    _direccion = " . (isset($this->_direccion) ? "'{$this->_direccion}'" : 'null') . "," .
               "    _telefono1 = " . (isset($this->_telefono1) ? "'{$this->_telefono1}'" : 'null') . "," .
               "    _telefono2 = " . (isset($this->_telefono2) ? "'{$this->_telefono2}'" : 'null') . "," .
               "    _fax = " . (isset($this->_fax) ? "'{$this->_fax}'" : 'null') . "," .
               "    _correo = " . (isset($this->_correo) ? "'{$this->_correo}'" : 'null') . "," .
               "    _fotos = " . (isset($this->_fotos) ? "'{$this->_fotos}'" : 'null') . "," .
               "    _fecha_creacion = " . (isset($this->_fecha_creacion) ? "'{$this->_fecha_creacion}'" : 'null') . "," .
               "    _multimarca = " . (isset($this->_multimarca) ? "b'{$this->_multimarca}'" : 'null') . "," .
               "    _latitud = " . (isset($this->_latitud) ? "{$this->_latitud}" : 'null') . "," .
               "    _longitud = " . (isset($this->_longitud) ? "{$this->_longitud}" : 'null') .
               "]";
    }

}
