<?php

/**
 * 
 */

include_once('mysql.class.php');

/**
 * @author Luis Hernandez
 *
 */

class Cotizacion
{
    private $_id;
    private $_id_servicio;
    private $_id_comuna;
    private $_id_vehiculo;
    private $_descripcion;
    private $_fecha_creacion;
    private $_fecha_modificacion;

    private static $_str_sql = "
  SELECT
  co.id_cotizacion AS id,
  co.id_servicio AS id_servicio,
  co.id_comuna AS id_comuna,
  co.id_vehiculo AS id_vehiculo,
  co.descripcion AS descripcion,
  DATE_FORMAT(co.fecha_creacion, '%Y-%m-%d %H:%i:%s') AS fecha_creacion,
  DATE_FORMAT(co.fecha_modificacion, '%Y-%m-%d %H:%i:%s') AS fecha_modificacion
  FROM cotizacion co";

    public function __construct() {
        $this->_id = null;
        $this->_id_servicio = null;
        $this->_id_comuna = null;
        $this->_id_vehiculo = null;
        $this->_descripcion = null;
        $this->_fecha_creacion = null;
        $this->_fecha_modificacion = null;

    }

    public function __set($name, $value) {

        //echo "Setting '$name' to '$value'\n";
        switch ($name) {
            case "id" :
                $this->_id = $value;
                break;
            case "id_servicio" :
                $this->_id_servicio = $value;
                break;
            case "id_comuna" :
                $this->_id_comuna = $value;
                break;
            case "id_vehiculo" :
                $this->_id_vehiculo = $value;
                break;
            case "descripcion" :
                $this->_descripcion = $value;
                break;
            case "fecha_creacion" :
                $this->_fecha_creacion = $value;
                break;
            case "fecha_modificacion" :
                $this->_fecha_modificacion = $value;
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
            case "id_servicio" :
                return $this->_id_servicio;
            case "id_comuna" :
                return $this->_id_comuna;
            case "id_vehiculo" :
                return $this->_id_vehiculo;
            case "descripcion" :
                return $this->_descripcion;
            case "fecha_creacion" :
                return $this->_fecha_creacion;
            case "fecha_modificacion" :
                return $this->_fecha_modificacion;
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
        $ret = new Cotizacion();

        $ret->_id = $p_ar[0]['id'];
        $ret->_id_servicio = $p_ar[0]['id_servicio'];
        $ret->_id_comuna = $p_ar[0]['id_comuna'];
        $ret->_id_vehiculo = $p_ar[0]['id_vehiculo'];
        $ret->_descripcion = $p_ar[0]['descripcion'];
        $ret->_fecha_creacion = $p_ar[0]['fecha_creacion'];
        $ret->_fecha_modificacion = $p_ar[0]['fecha_modificacion'];

        return $ret;
    }

    public static function getByParameter($p_db, $p_key, $p_value) {
        $ret = null;
        
        $str_sql = self::$_str_sql .
            "  WHERE co." . $p_key . " = " . $p_value .
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
        return self::getByParameter($p_db, "id_cotizacion", $p_id);
    }
    
    public static function seek($p_db, $p_parameters, $p_order, $p_direction, $p_offset, $p_limit, $p_get_total = false) {

        $result         = new stdClass();
        $array_clauses  = array();

        $str_sql = self::$_str_sql;

        foreach($p_parameters as $key => $value) {
                            if ($key == 'id_cotizacion') {
                $array_clauses[] = "co.id_cotizacion = $value";
            }
                else             if ($key == 'id_comuna') {
                $array_clauses[] = "co.id_comuna = $value";
            }
                else             if ($key == 'id_servicio') {
                $array_clauses[] = "co.id_servicio = $value";
            }
                else             if ($key == 'id_vehiculo') {
                $array_clauses[] = "co.id_vehiculo = $value";
            }
                else         if (p.getKey().equals("mas reciente") {
            $array_clauses[] = "co.fecha_modificacion > STR_TO_DATE('" . $value . "', '%Y-%m-%d %H:%i:%s')";
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
            "  UPDATE cotizacion" .
            "  SET" .
            "  descripcion = " . (isset($this->_descripcion) ? "'{$this->_descripcion}'" : 'null') . "," .
            "  fecha_creacion = " . (isset($this->_fecha_creacion) ? "STR_TO_DATE('{$this->_fecha_creacion}', '%Y-%m-%d %H:%i:%s')" : 'null') . "," .
            "  fecha_modificacion = " . (isset($this->_fecha_modificacion) ? "STR_TO_DATE('{$this->_fecha_modificacion}', '%Y-%m-%d %H:%i:%s')" : 'null') .
            "  WHERE" .
            "  id_cotizacion = {$this->_id}";

        //echo '<br>' . $str_sql . '<br>';

        if ($p_db->Query($str_sql) === false) {
            throw new Exception('Error al actualizar registro: ' . $p_db->Error(), $p_db->ErrorNumber(), null);
        }
    }

    public function insert($p_db) {

        $str_sql =
            "  INSERT INTO cotizacion" .
            "  (" .
            "  id_servicio, " .
            "  id_comuna, " .
            "  id_vehiculo, " .
            "  descripcion, " .
            "  fecha_creacion)" .
            "  VALUES" .
            "  (" .
            "  " . (isset($this->_id_servicio) ? "{$this->_id_servicio}" : 'null') . "," .
            "  " . (isset($this->_id_comuna) ? "{$this->_id_comuna}" : 'null') . "," .
            "  " . (isset($this->_id_vehiculo) ? "{$this->_id_vehiculo}" : 'null') . "," .
            "  " . (isset($this->_descripcion) ? "''{$this->_descripcion}''" : 'null') . "," .
            "  " . (isset($this->_fecha_creacion) ? "STR_TO_DATE('{$this->_fecha_creacion}', '%Y-%m-%d %H:%i:%s')" : 'null');

        //echo '<br>' . $str_sql . '<br>';

        if ($p_db->Query($str_sql) === false) {
            throw new Exception('Error al insertar registro: ' . $p_db->Error(), $p_db->ErrorNumber(), null);
        }


        $ar_id = $p_db->QueryArray('SELECT LAST_INSERT_ID()');

        $this->_id = $ar_id[0][0];
    }

    public function delete($p_db) {


        $str_sql =
            "  DELETE FROM  cotizacion" .
            "  WHERE" .
            "  id_cotizacion = {$this->_id}";

        //echo '<br>' . $str_sql . '<br>';

        if ($p_db->Query($str_sql) === false) {
            throw new Exception('Error al borrar registro: ' . $p_db->Error(), $p_db->ErrorNumber(), null);
        }
    }


    public function load($p_db) {
        $obj = self::getById($p_db, $this->_id);

        if ($obj != null) {

            $this->_id = $obj->_id;
            $this->_id_servicio = $obj->_id_servicio;
            $this->_id_comuna = $obj->_id_comuna;
            $this->_id_vehiculo = $obj->_id_vehiculo;
            $this->_descripcion = $obj->_descripcion;
            $this->_fecha_creacion = $obj->_fecha_creacion;
            $this->_fecha_modificacion = $obj->_fecha_modificacion;
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
        return "Cotizacion [" .
               "    _id = " . (isset($this->_id) ? "{$this->_id}" : 'null') . "," .
               "    _id_servicio = " . (isset($this->_id_servicio) ? "{$this->_id_servicio}" : 'null') . "," .
               "    _id_comuna = " . (isset($this->_id_comuna) ? "{$this->_id_comuna}" : 'null') . "," .
               "    _id_vehiculo = " . (isset($this->_id_vehiculo) ? "{$this->_id_vehiculo}" : 'null') . "," .
               "    _descripcion = " . (isset($this->_descripcion) ? "'{$this->_descripcion}'" : 'null') . "," .
               "    _fecha_creacion = " . (isset($this->_fecha_creacion) ? "'{$this->_fecha_creacion}'" : 'null') . "," .
               "    _fecha_modificacion = " . (isset($this->_fecha_modificacion) ? "'{$this->_fecha_modificacion}'" : 'null') .
               "]";
    }

}
