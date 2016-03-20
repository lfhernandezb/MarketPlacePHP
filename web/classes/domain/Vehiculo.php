<?php

/**
 * 
 */

include_once('mysql.class.php');

/**
 * @author Luis Hernandez
 *
 */

class Vehiculo
{
    private $_id;
    private $_id_usuario;
    private $_id_modelo;
    private $_id_combustible;
    private $_id_tipo_transmision;
    private $_id_traccion;
    private $_patente;
    private $_anio;
    private $_aire_acondicionado;
    private $_alza_vidrios;

    private static $_str_sql = "
  SELECT
  ve.id_vehiculo AS id,
  ve.id_usuario AS id_usuario,
  ve.id_modelo AS id_modelo,
  ve.id_combustible AS id_combustible,
  ve.id_tipo_transmision AS id_tipo_transmision,
  ve.id_traccion AS id_traccion,
  ve.patente AS patente,
  ve.anio AS anio,
  0+ve.aire_acondicionado AS aire_acondicionado,
  0+ve.alza_vidrios AS alza_vidrios
  FROM vehiculo ve";

    public function __construct() {
        $this->_id = null;
        $this->_id_usuario = null;
        $this->_id_modelo = null;
        $this->_id_combustible = null;
        $this->_id_tipo_transmision = null;
        $this->_id_traccion = null;
        $this->_patente = null;
        $this->_anio = null;
        $this->_aire_acondicionado = null;
        $this->_alza_vidrios = null;

    }

    public function __set($name, $value) {

        //echo "Setting '$name' to '$value'\n";
        switch ($name) {
            case "id" :
                $this->_id = $value;
                break;
            case "id_usuario" :
                $this->_id_usuario = $value;
                break;
            case "id_modelo" :
                $this->_id_modelo = $value;
                break;
            case "id_combustible" :
                $this->_id_combustible = $value;
                break;
            case "id_tipo_transmision" :
                $this->_id_tipo_transmision = $value;
                break;
            case "id_traccion" :
                $this->_id_traccion = $value;
                break;
            case "patente" :
                $this->_patente = $value;
                break;
            case "anio" :
                $this->_anio = $value;
                break;
            case "aire_acondicionado" :
                $this->_aire_acondicionado = $value;
                break;
            case "alza_vidrios" :
                $this->_alza_vidrios = $value;
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
            case "id_usuario" :
                return $this->_id_usuario;
            case "id_modelo" :
                return $this->_id_modelo;
            case "id_combustible" :
                return $this->_id_combustible;
            case "id_tipo_transmision" :
                return $this->_id_tipo_transmision;
            case "id_traccion" :
                return $this->_id_traccion;
            case "patente" :
                return $this->_patente;
            case "anio" :
                return $this->_anio;
            case "aire_acondicionado" :
                return $this->_aire_acondicionado;
            case "alza_vidrios" :
                return $this->_alza_vidrios;
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
        $ret = new Vehiculo();

        $ret->_id = $p_ar[0]['id'];
        $ret->_id_usuario = $p_ar[0]['id_usuario'];
        $ret->_id_modelo = $p_ar[0]['id_modelo'];
        $ret->_id_combustible = $p_ar[0]['id_combustible'];
        $ret->_id_tipo_transmision = $p_ar[0]['id_tipo_transmision'];
        $ret->_id_traccion = $p_ar[0]['id_traccion'];
        $ret->_patente = $p_ar[0]['patente'];
        $ret->_anio = $p_ar[0]['anio'];
        $ret->_aire_acondicionado = $p_ar[0]['aire_acondicionado'];
        $ret->_alza_vidrios = $p_ar[0]['alza_vidrios'];

        return $ret;
    }

    public static function getByParameter($p_db, $p_key, $p_value) {
        $ret = null;
        
        $str_sql = self::$_str_sql .
            "  WHERE ve." . $p_key . " = " . $p_value .
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
        return self::getByParameter($p_db, "id_vehiculo", $p_id);
    }
    
    public static function seek($p_db, $p_parameters, $p_order, $p_direction, $p_offset, $p_limit, $p_get_total = false) {

        $result         = new stdClass();
        $array_clauses  = array();

        $str_sql = self::$_str_sql;

        foreach($p_parameters as $key => $value) {
                            if ($key == 'id_vehiculo') {
                $array_clauses[] = "ve.id_vehiculo = $value";
            }
                else             if ($key == 'id_combustible') {
                $array_clauses[] = "ve.id_combustible = $value";
            }
                else             if ($key == 'id_modelo') {
                $array_clauses[] = "ve.id_modelo = $value";
            }
                else             if ($key == 'id_tipo_transmision') {
                $array_clauses[] = "ve.id_tipo_transmision = $value";
            }
                else             if ($key == 'id_traccion') {
                $array_clauses[] = "ve.id_traccion = $value";
            }
                else             if ($key == 'id_usuario') {
                $array_clauses[] = "ve.id_usuario = $value";
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
            "  UPDATE vehiculo" .
            "  SET" .
            "  patente = " . (isset($this->_patente) ? "'{$this->_patente}'" : 'null') . "," .
            "  anio = " . (isset($this->_anio) ? "{$this->_anio}" : 'null') . "," .
            "  aire_acondicionado = " . (isset($this->_aire_acondicionado) ? "b'{$this->_aire_acondicionado}'" : 'null') . "," .
            "  alza_vidrios = " . (isset($this->_alza_vidrios) ? "b'{$this->_alza_vidrios}'" : 'null') .
            "  WHERE" .
            "  id_vehiculo = {$this->_id}";

        //echo '<br>' . $str_sql . '<br>';

        if ($p_db->Query($str_sql) === false) {
            throw new Exception('Error al actualizar registro: ' . $p_db->Error(), $p_db->ErrorNumber(), null);
        }
    }

    public function insert($p_db) {

        $str_sql =
            "  INSERT INTO vehiculo" .
            "  (" .
            "  id_usuario, " .
            "  id_modelo, " .
            "  id_combustible, " .
            "  id_tipo_transmision, " .
            "  id_traccion, " .
            "  patente, " .
            "  anio, " .
            "  aire_acondicionado, " .
            "  alza_vidrios)" .
            "  VALUES" .
            "  (" .
            "  " . (isset($this->_id_usuario) ? "{$this->_id_usuario}" : 'null') . "," .
            "  " . (isset($this->_id_modelo) ? "{$this->_id_modelo}" : 'null') . "," .
            "  " . (isset($this->_id_combustible) ? "{$this->_id_combustible}" : 'null') . "," .
            "  " . (isset($this->_id_tipo_transmision) ? "{$this->_id_tipo_transmision}" : 'null') . "," .
            "  " . (isset($this->_id_traccion) ? "{$this->_id_traccion}" : 'null') . "," .
            "  " . (isset($this->_patente) ? "''{$this->_patente}''" : 'null') . "," .
            "  " . (isset($this->_anio) ? "{$this->_anio}" : 'null') . "," .
            "  " . (isset($this->_aire_acondicionado) ? "b'{$this->_aire_acondicionado}'" : 'null') . "," .
            "  " . (isset($this->_alza_vidrios) ? "b'{$this->_alza_vidrios}'" : 'null');

        //echo '<br>' . $str_sql . '<br>';

        if ($p_db->Query($str_sql) === false) {
            throw new Exception('Error al insertar registro: ' . $p_db->Error(), $p_db->ErrorNumber(), null);
        }


        $ar_id = $p_db->QueryArray('SELECT LAST_INSERT_ID()');

        $this->_id = $ar_id[0][0];
    }

    public function delete($p_db) {


        $str_sql =
            "  DELETE FROM  vehiculo" .
            "  WHERE" .
            "  id_vehiculo = {$this->_id}";

        //echo '<br>' . $str_sql . '<br>';

        if ($p_db->Query($str_sql) === false) {
            throw new Exception('Error al borrar registro: ' . $p_db->Error(), $p_db->ErrorNumber(), null);
        }
    }


    public function load($p_db) {
        $obj = self::getById($p_db, $this->_id);

        if ($obj != null) {

            $this->_id = $obj->_id;
            $this->_id_usuario = $obj->_id_usuario;
            $this->_id_modelo = $obj->_id_modelo;
            $this->_id_combustible = $obj->_id_combustible;
            $this->_id_tipo_transmision = $obj->_id_tipo_transmision;
            $this->_id_traccion = $obj->_id_traccion;
            $this->_patente = $obj->_patente;
            $this->_anio = $obj->_anio;
            $this->_aire_acondicionado = $obj->_aire_acondicionado;
            $this->_alza_vidrios = $obj->_alza_vidrios;
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
        return "Vehiculo [" .
               "    _id = " . (isset($this->_id) ? "{$this->_id}" : 'null') . "," .
               "    _id_usuario = " . (isset($this->_id_usuario) ? "{$this->_id_usuario}" : 'null') . "," .
               "    _id_modelo = " . (isset($this->_id_modelo) ? "{$this->_id_modelo}" : 'null') . "," .
               "    _id_combustible = " . (isset($this->_id_combustible) ? "{$this->_id_combustible}" : 'null') . "," .
               "    _id_tipo_transmision = " . (isset($this->_id_tipo_transmision) ? "{$this->_id_tipo_transmision}" : 'null') . "," .
               "    _id_traccion = " . (isset($this->_id_traccion) ? "{$this->_id_traccion}" : 'null') . "," .
               "    _patente = " . (isset($this->_patente) ? "'{$this->_patente}'" : 'null') . "," .
               "    _anio = " . (isset($this->_anio) ? "{$this->_anio}" : 'null') . "," .
               "    _aire_acondicionado = " . (isset($this->_aire_acondicionado) ? "b'{$this->_aire_acondicionado}'" : 'null') . "," .
               "    _alza_vidrios = " . (isset($this->_alza_vidrios) ? "b'{$this->_alza_vidrios}'" : 'null') .
               "]";
    }

}
