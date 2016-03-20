<?php

/**
 * 
 */

include_once('mysql.class.php');
include_once('ProveedorSucursal.php');

/**
 * @author Luis Hernandez
 *
 */

class Proveedor
{
    private $_id;
    private $_id_estado_proveedor;
    private $_id_bloqueo_proveedor;
    private $_rut;
    private $_razon_social;
    private $_nombre_fantasia;
    private $_direccion_facturacion;
    private $_web;
    private $_texto_html;
    private $_fecha_creacion;

    private static $_str_sql = "
  SELECT
  pr.id_proveedor AS id,
  pr.id_estado_proveedor AS id_estado_proveedor,
  pr.id_bloqueo_proveedor AS id_bloqueo_proveedor,
  pr.rut AS rut,
  pr.razon_social AS razon_social,
  pr.nombre_fantasia AS nombre_fantasia,
  pr.direccion_facturacion AS direccion_facturacion,
  pr.web AS web,
  pr.texto_html AS texto_html,
  DATE_FORMAT(pr.fecha_creacion, '%Y-%m-%d %H:%i:%s') AS fecha_creacion
  FROM proveedor pr";

	private static $_str_sql_special = "
  SELECT DISTINCT(
  pr.id_proveedor) AS id,
  pr.nombre_fantasia,
  ps.descripcion AS sucursal,
  ps.direccion,
  ps.id_proveedor_sucursal,
  c.descripcion AS comuna,
  r.descripcion AS region,
  r.descripcion_breve AS region_breve
  FROM proveedor pr
  JOIN proveedor_sucursal ps ON ps.id_proveedor = pr.id_proveedor
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
        $this->_id_estado_proveedor = null;
        $this->_id_bloqueo_proveedor = null;
        $this->_rut = null;
        $this->_razon_social = null;
        $this->_nombre_fantasia = null;
        $this->_direccion_facturacion = null;
        $this->_web = null;
        $this->_texto_html = null;
        $this->_fecha_creacion = null;

    }

    public function __set($name, $value) {

        //echo "Setting '$name' to '$value'\n";
        switch ($name) {
            case "id" :
                $this->_id = $value;
                break;
            case "id_estado_proveedor" :
                $this->_id_estado_proveedor = $value;
                break;
            case "id_bloqueo_proveedor" :
                $this->_id_bloqueo_proveedor = $value;
                break;
            case "rut" :
                $this->_rut = $value;
                break;
            case "razon_social" :
                $this->_razon_social = $value;
                break;
            case "nombre_fantasia" :
                $this->_nombre_fantasia = $value;
                break;
            case "direccion_facturacion" :
                $this->_direccion_facturacion = $value;
                break;
            case "web" :
                $this->_web = $value;
                break;
            case "texto_html" :
                $this->_texto_html = $value;
                break;
            case "fecha_creacion" :
                $this->_fecha_creacion = $value;
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
            case "id_estado_proveedor" :
                return $this->_id_estado_proveedor;
            case "id_bloqueo_proveedor" :
                return $this->_id_bloqueo_proveedor;
            case "rut" :
                return $this->_rut;
            case "razon_social" :
                return $this->_razon_social;
            case "nombre_fantasia" :
                return $this->_nombre_fantasia;
            case "direccion_facturacion" :
                return $this->_direccion_facturacion;
            case "web" :
                return $this->_web;
            case "texto_html" :
                return $this->_texto_html;
            case "fecha_creacion" :
                return $this->_fecha_creacion;
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
        $ret = new Proveedor();

        $ret->_id = $p_ar[0]['id'];
        $ret->_id_estado_proveedor = $p_ar[0]['id_estado_proveedor'];
        $ret->_id_bloqueo_proveedor = $p_ar[0]['id_bloqueo_proveedor'];
        $ret->_rut = $p_ar[0]['rut'];
        $ret->_razon_social = $p_ar[0]['razon_social'];
        $ret->_nombre_fantasia = $p_ar[0]['nombre_fantasia'];
        $ret->_direccion_facturacion = $p_ar[0]['direccion_facturacion'];
        $ret->_web = $p_ar[0]['web'];
        $ret->_texto_html = $p_ar[0]['texto_html'];
        $ret->_fecha_creacion = $p_ar[0]['fecha_creacion'];

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
        return self::getByParameter($p_db, "id_proveedor", $p_id);
    }
    
    public function getSucursales($p_db) {
    	
    	$parameters = array(
    		'id_proveedor'	=>	$this->_id
    	);
    	
    	return ProveedorSucursal::seek($p_db, $parameters, 'r.id_region', 'ASC', 0, 10000);
    }
    
	public static function seekSpecial($p_db, $p_id_usuario, $p_order, $p_direction, $p_offset, $p_limit, $p_get_total = false) {
		
		$result         = new stdClass();
		
	$str_sql = self::$_str_sql_special . "
  WHERE
  pr.nombre_fantasia like '%$p_pattern%' OR
  ps.descripcion like '%$p_pattern%' OR
  c.descripcion like '%$p_pattern%' OR
  r.descripcion like '%$p_pattern%' OR
  s.descripcion like '%$p_pattern%'
	";
				
		//echo '<br>' . $str_sql . '<br>';
		
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
    
	public static function seek($p_db, $p_parameters, $p_order, $p_direction, $p_offset, $p_limit, $p_get_total = false) {

        $result         = new stdClass();
        $array_clauses  = array();

        $str_sql = self::$_str_sql_special;
        

        foreach($p_parameters as $key => $value) {
            if ($key == 'id_proveedor') {
                $array_clauses[] = "pr.id_proveedor = $value";
            }
            else if ($key == 'id_marca') {
                $array_clauses[] = "psm.id_marca = $value";
            }
            else if ($key == 'id_servicio') {
                $array_clauses[] = "pss.id_servicio = $value";
            }
            else if ($key == 'id_combustible') {
                $array_clauses[] = "psc.id_combustible = $value";
            }
            else if ($key == 'id_comuna') {
                $array_clauses[] = "c.id_comuna = $value";
            }
            else if ($key == 'id_region') {
                $array_clauses[] = "r.id_region = $value";
            }
            else if ($key == 'id_bloqueo_proveedor') {
                $array_clauses[] = "pr.id_bloqueo_proveedor = $value";
            }
            else if ($key == 'id_estado_proveedor') {
                $array_clauses[] = "pr.id_estado_proveedor = $value";
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
            "  UPDATE proveedor" .
            "  SET" .
            "  rut = " . (isset($this->_rut) ? "'{$this->_rut}'" : 'null') . "," .
            "  razon_social = " . (isset($this->_razon_social) ? "'{$this->_razon_social}'" : 'null') . "," .
            "  nombre_fantasia = " . (isset($this->_nombre_fantasia) ? "'{$this->_nombre_fantasia}'" : 'null') . "," .
            "  direccion_facturacion = " . (isset($this->_direccion_facturacion) ? "'{$this->_direccion_facturacion}'" : 'null') . "," .
            "  web = " . (isset($this->_web) ? "'{$this->_web}'" : 'null') . "," .
            "  texto_html = " . (isset($this->_texto_html) ? "'{$this->_texto_html}'" : 'null') . "," .
            "  fecha_creacion = " . (isset($this->_fecha_creacion) ? "STR_TO_DATE('{$this->_fecha_creacion}', '%Y-%m-%d %H:%i:%s')" : 'null') .
            "  WHERE" .
            "  id_proveedor = {$this->_id}";

        //echo '<br>' . $str_sql . '<br>';

        if ($p_db->Query($str_sql) === false) {
            throw new Exception('Error al actualizar registro: ' . $p_db->Error(), $p_db->ErrorNumber(), null);
        }
    }

    public function insert($p_db) {

        $str_sql =
            "  INSERT INTO proveedor" .
            "  (" .
            "  id_estado_proveedor, " .
            "  id_bloqueo_proveedor, " .
            "  rut, " .
            "  razon_social, " .
            "  nombre_fantasia, " .
            "  direccion_facturacion, " .
            "  web, " .
            "  texto_html, " .
            "  fecha_creacion)" .
            "  VALUES" .
            "  (" .
            "  " . (isset($this->_id_estado_proveedor) ? "{$this->_id_estado_proveedor}" : 'null') . "," .
            "  " . (isset($this->_id_bloqueo_proveedor) ? "{$this->_id_bloqueo_proveedor}" : 'null') . "," .
            "  " . (isset($this->_rut) ? "'{$this->_rut}'" : 'null') . "," .
            "  " . (isset($this->_razon_social) ? "'{$this->_razon_social}'" : 'null') . "," .
            "  " . (isset($this->_nombre_fantasia) ? "'{$this->_nombre_fantasia}'" : 'null') . "," .
            "  " . (isset($this->_direccion_facturacion) ? "'{$this->_direccion_facturacion}'" : 'null') . "," .
            "  " . (isset($this->_web) ? "'{$this->_web}'" : 'null') . "," .
            "  " . (isset($this->_texto_html) ? "'{$this->_texto_html}'" : 'null') . "," .
            "  " . (isset($this->_fecha_creacion) ? "STR_TO_DATE('{$this->_fecha_creacion}', '%Y-%m-%d %H:%i:%s')" : 'null') .
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
            "  DELETE FROM  proveedor" .
            "  WHERE" .
            "  id_proveedor = {$this->_id}";

        //echo '<br>' . $str_sql . '<br>';

        if ($p_db->Query($str_sql) === false) {
            throw new Exception('Error al borrar registro: ' . $p_db->Error(), $p_db->ErrorNumber(), null);
        }
    }


    public function load($p_db) {
        $obj = self::getById($p_db, $this->_id);

        if ($obj != null) {

            $this->_id = $obj->_id;
            $this->_id_estado_proveedor = $obj->_id_estado_proveedor;
            $this->_id_bloqueo_proveedor = $obj->_id_bloqueo_proveedor;
            $this->_rut = $obj->_rut;
            $this->_razon_social = $obj->_razon_social;
            $this->_nombre_fantasia = $obj->_nombre_fantasia;
            $this->_direccion_facturacion = $obj->_direccion_facturacion;
            $this->_web = $obj->_web;
            $this->_texto_html = $obj->_texto_html;
            $this->_fecha_creacion = $obj->_fecha_creacion;
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
        return "Proveedor [" .
               "    _id = " . (isset($this->_id) ? "{$this->_id}" : 'null') . "," .
               "    _id_estado_proveedor = " . (isset($this->_id_estado_proveedor) ? "{$this->_id_estado_proveedor}" : 'null') . "," .
               "    _id_bloqueo_proveedor = " . (isset($this->_id_bloqueo_proveedor) ? "{$this->_id_bloqueo_proveedor}" : 'null') . "," .
               "    _rut = " . (isset($this->_rut) ? "'{$this->_rut}'" : 'null') . "," .
               "    _razon_social = " . (isset($this->_razon_social) ? "'{$this->_razon_social}'" : 'null') . "," .
               "    _nombre_fantasia = " . (isset($this->_nombre_fantasia) ? "'{$this->_nombre_fantasia}'" : 'null') . "," .
               "    _direccion_facturacion = " . (isset($this->_direccion_facturacion) ? "'{$this->_direccion_facturacion}'" : 'null') . "," .
               "    _web = " . (isset($this->_web) ? "'{$this->_web}'" : 'null') . "," .
               "    _texto_html = " . (isset($this->_texto_html) ? "'{$this->_texto_html}'" : 'null') . "," .
               "    _fecha_creacion = " . (isset($this->_fecha_creacion) ? "'{$this->_fecha_creacion}'" : 'null') .
               "]";
    }

}
