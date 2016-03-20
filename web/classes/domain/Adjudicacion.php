<?php

/**
 * 
 */

include_once('mysql.class.php');

/**
 * @author Luis Hernandez
 *
 */

class Adjudicacion
{
    private $_id;
    private $_id_cotizacion;
    private $_id_oferta_cotizacion;
    private $_fecha_creacion;
    private $_fecha_modificacion;
    private $_comentarios;

    private static $_str_sql = "
  SELECT
  ad.id_adjudicacion AS id,
  ad.id_cotizacion AS id_cotizacion,
  ad.id_oferta_cotizacion AS id_oferta_cotizacion,
  DATE_FORMAT(ad.fecha_creacion, '%Y-%m-%d %H:%i:%s') AS fecha_creacion,
  DATE_FORMAT(ad.fecha_modificacion, '%Y-%m-%d %H:%i:%s') AS fecha_modificacion,
  ad.comentarios AS comentarios
  FROM adjudicacion ad";

    public function __construct() {
        $this->_id = null;
        $this->_id_cotizacion = null;
        $this->_id_oferta_cotizacion = null;
        $this->_fecha_creacion = null;
        $this->_fecha_modificacion = null;
        $this->_comentarios = null;

    }

    public function __set($name, $value) {

        //echo "Setting '$name' to '$value'\n";
        switch ($name) {
            case "id" :
                $this->_id = $value;
                break;
            case "id_cotizacion" :
                $this->_id_cotizacion = $value;
                break;
            case "id_oferta_cotizacion" :
                $this->_id_oferta_cotizacion = $value;
                break;
            case "fecha_creacion" :
                $this->_fecha_creacion = $value;
                break;
            case "fecha_modificacion" :
                $this->_fecha_modificacion = $value;
                break;
            case "comentarios" :
                $this->_comentarios = $value;
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
            case "id_cotizacion" :
                return $this->_id_cotizacion;
            case "id_oferta_cotizacion" :
                return $this->_id_oferta_cotizacion;
            case "fecha_creacion" :
                return $this->_fecha_creacion;
            case "fecha_modificacion" :
                return $this->_fecha_modificacion;
            case "comentarios" :
                return $this->_comentarios;
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
        $ret = new Adjudicacion();

        $ret->_id = $p_ar[0]['id'];
        $ret->_id_cotizacion = $p_ar[0]['id_cotizacion'];
        $ret->_id_oferta_cotizacion = $p_ar[0]['id_oferta_cotizacion'];
        $ret->_fecha_creacion = $p_ar[0]['fecha_creacion'];
        $ret->_fecha_modificacion = $p_ar[0]['fecha_modificacion'];
        $ret->_comentarios = $p_ar[0]['comentarios'];

        return $ret;
    }

    public static function getByParameter($p_db, $p_key, $p_value) {
        $ret = null;
        
        $str_sql = self::$_str_sql .
            "  WHERE ad." . $p_key . " = " . $p_value .
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
        return self::getByParameter($p_db, "id_adjudicacion", $p_id);
    }
    
    public static function seek($p_db, $p_parameters, $p_order, $p_direction, $p_offset, $p_limit, $p_get_total = false) {

        $result         = new stdClass();
        $array_clauses  = array();

        $str_sql = self::$_str_sql;

        foreach($p_parameters as $key => $value) {
                            if ($key == 'id_adjudicacion') {
                $array_clauses[] = "ad.id_adjudicacion = $value";
            }
                else             if ($key == 'id_cotizacion') {
                $array_clauses[] = "ad.id_cotizacion = $value";
            }
                else             if ($key == 'id_oferta_cotizacion') {
                $array_clauses[] = "ad.id_oferta_cotizacion = $value";
            }
                else         if (p.getKey().equals("mas reciente") {
            $array_clauses[] = "ad.fecha_modificacion > STR_TO_DATE('" . $value . "', '%Y-%m-%d %H:%i:%s')";
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
            "  UPDATE adjudicacion" .
            "  SET" .
            "  fecha_creacion = " . (isset($this->_fecha_creacion) ? "STR_TO_DATE('{$this->_fecha_creacion}', '%Y-%m-%d %H:%i:%s')" : 'null') . "," .
            "  fecha_modificacion = " . (isset($this->_fecha_modificacion) ? "STR_TO_DATE('{$this->_fecha_modificacion}', '%Y-%m-%d %H:%i:%s')" : 'null') . "," .
            "  comentarios = " . (isset($this->_comentarios) ? "'{$this->_comentarios}'" : 'null') .
            "  WHERE" .
            "  id_adjudicacion = {$this->_id}";

        //echo '<br>' . $str_sql . '<br>';

        if ($p_db->Query($str_sql) === false) {
            throw new Exception('Error al actualizar registro: ' . $p_db->Error(), $p_db->ErrorNumber(), null);
        }
    }

    public function insert($p_db) {

        $str_sql =
            "  INSERT INTO adjudicacion" .
            "  (" .
            "  id_cotizacion, " .
            "  id_oferta_cotizacion, " .
            "  fecha_creacion, " .
            "  comentarios)" .
            "  VALUES" .
            "  (" .
            "  " . (isset($this->_id_cotizacion) ? "{$this->_id_cotizacion}" : 'null') . "," .
            "  " . (isset($this->_id_oferta_cotizacion) ? "{$this->_id_oferta_cotizacion}" : 'null') . "," .
            "  " . (isset($this->_fecha_creacion) ? "STR_TO_DATE('{$this->_fecha_creacion}', '%Y-%m-%d %H:%i:%s')" : 'null') . "," .
            "  " . (isset($this->_comentarios) ? "''{$this->_comentarios}''" : 'null');

        //echo '<br>' . $str_sql . '<br>';

        if ($p_db->Query($str_sql) === false) {
            throw new Exception('Error al insertar registro: ' . $p_db->Error(), $p_db->ErrorNumber(), null);
        }


        $ar_id = $p_db->QueryArray('SELECT LAST_INSERT_ID()');

        $this->_id = $ar_id[0][0];
    }

    public function delete($p_db) {


        $str_sql =
            "  DELETE FROM  adjudicacion" .
            "  WHERE" .
            "  id_adjudicacion = {$this->_id}";

        //echo '<br>' . $str_sql . '<br>';

        if ($p_db->Query($str_sql) === false) {
            throw new Exception('Error al borrar registro: ' . $p_db->Error(), $p_db->ErrorNumber(), null);
        }
    }


    public function load($p_db) {
        $obj = self::getById($p_db, $this->_id);

        if ($obj != null) {

            $this->_id = $obj->_id;
            $this->_id_cotizacion = $obj->_id_cotizacion;
            $this->_id_oferta_cotizacion = $obj->_id_oferta_cotizacion;
            $this->_fecha_creacion = $obj->_fecha_creacion;
            $this->_fecha_modificacion = $obj->_fecha_modificacion;
            $this->_comentarios = $obj->_comentarios;
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
        return "Adjudicacion [" .
               "    _id = " . (isset($this->_id) ? "{$this->_id}" : 'null') . "," .
               "    _id_cotizacion = " . (isset($this->_id_cotizacion) ? "{$this->_id_cotizacion}" : 'null') . "," .
               "    _id_oferta_cotizacion = " . (isset($this->_id_oferta_cotizacion) ? "{$this->_id_oferta_cotizacion}" : 'null') . "," .
               "    _fecha_creacion = " . (isset($this->_fecha_creacion) ? "'{$this->_fecha_creacion}'" : 'null') . "," .
               "    _fecha_modificacion = " . (isset($this->_fecha_modificacion) ? "'{$this->_fecha_modificacion}'" : 'null') . "," .
               "    _comentarios = " . (isset($this->_comentarios) ? "'{$this->_comentarios}'" : 'null') .
               "]";
    }

}
