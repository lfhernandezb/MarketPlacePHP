<?php

/**
 * 
 */

include_once('mysql.class.php');

/**
 * @author Luis Hernandez
 *
 */

class ProveedorSucursalCombustible
{
    private $_id;
    private $_id_combustible;
    private $_id_proveedor_sucursal;

    private static $_str_sql = "
  SELECT
  pr.id_proveedor_sucursal_combustible AS id,
  pr.id_combustible AS id_combustible,
  pr.id_proveedor_sucursal AS id_proveedor_sucursal
  FROM proveedor_sucursal_combustible pr";

    public function __construct() {
        $this->_id = null;
        $this->_id_combustible = null;
        $this->_id_proveedor_sucursal = null;

    }

    public function __set($name, $value) {

        //echo "Setting '$name' to '$value'\n";
        switch ($name) {
            case "id" :
                $this->_id = $value;
                break;
            case "id_combustible" :
                $this->_id_combustible = $value;
                break;
            case "id_proveedor_sucursal" :
                $this->_id_proveedor_sucursal = $value;
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
            case "id_combustible" :
                return $this->_id_combustible;
            case "id_proveedor_sucursal" :
                return $this->_id_proveedor_sucursal;
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
        $ret = new ProveedorSucursalCombustible();

        $ret->_id = $p_ar[0]['id'];
        $ret->_id_combustible = $p_ar[0]['id_combustible'];
        $ret->_id_proveedor_sucursal = $p_ar[0]['id_proveedor_sucursal'];

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
        return self::getByParameter($p_db, "id_proveedor_sucursal_combustible", $p_id);
    }
    
	public static function seekSpecial($p_db, $p_id_proveedor_sucursal) {
		
	$str_sql = "
  SELECT co.id_combustible, co.descripcion, psc.id_proveedor_sucursal
  FROM combustible co
  LEFT JOIN proveedor_sucursal_combustible psc ON psc.id_combustible = co.id_combustible
  AND psc.id_proveedor_sucursal = $p_id_proveedor_sucursal
	";
				
		//echo '<br>' . $str_sql . '<br>';
		
		$ret = $p_db->QueryArray($str_sql, MYSQL_ASSOC);
		
		if (!is_array($ret)) {
			$ret = null;

			if ($p_db->RowCount() != 0) {
				throw new Exception('Error al obtener registro: ' . $p_db->Error(), $p_db->ErrorNumber(), null);
			}
		}
		
		return $ret;
		
	}
    
	public static function seek($p_db, $p_parameters, $p_order, $p_direction, $p_offset, $p_limit, $p_get_total = false) {

        $result         = new stdClass();
        $array_clauses  = array();

        $str_sql = self::$_str_sql;

        foreach($p_parameters as $key => $value) {
                            if ($key == 'id_proveedor_sucursal_combustible') {
                $array_clauses[] = "pr.id_proveedor_sucursal_combustible = $value";
            }
                else             if ($key == 'id_combustible') {
                $array_clauses[] = "pr.id_combustible = $value";
            }
                else             if ($key == 'id_proveedor_sucursal') {
                $array_clauses[] = "pr.id_proveedor_sucursal = $value";
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
            "  UPDATE proveedor_sucursal_combustible" .
            "  SET" .
            "  WHERE" .
            "  id_proveedor_sucursal_combustible = {$this->_id}";

        //echo '<br>' . $str_sql . '<br>';

        if ($p_db->Query($str_sql) === false) {
            throw new Exception('Error al actualizar registro: ' . $p_db->Error(), $p_db->ErrorNumber(), null);
        }
    }

    public function insert($p_db) {

        $str_sql =
            "  INSERT INTO proveedor_sucursal_combustible" .
            "  (" .
            "  id_combustible, " .
            "  id_proveedor_sucursal)" .
            "  VALUES" .
            "  (" .
            "  " . (isset($this->_id_combustible) ? "{$this->_id_combustible}" : 'null') . "," .
            "  " . (isset($this->_id_proveedor_sucursal) ? "{$this->_id_proveedor_sucursal}" : 'null') .
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
            "  DELETE FROM  proveedor_sucursal_combustible" .
            "  WHERE" .
            "  id_proveedor_sucursal_combustible = {$this->_id}";

        //echo '<br>' . $str_sql . '<br>';

        if ($p_db->Query($str_sql) === false) {
            throw new Exception('Error al borrar registro: ' . $p_db->Error(), $p_db->ErrorNumber(), null);
        }
    }


    public function load($p_db) {
        $obj = self::getById($p_db, $this->_id);

        if ($obj != null) {

            $this->_id = $obj->_id;
            $this->_id_combustible = $obj->_id_combustible;
            $this->_id_proveedor_sucursal = $obj->_id_proveedor_sucursal;
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
        return "ProveedorSucursalCombustible [" .
               "    _id = " . (isset($this->_id) ? "{$this->_id}" : 'null') . "," .
               "    _id_combustible = " . (isset($this->_id_combustible) ? "{$this->_id_combustible}" : 'null') . "," .
               "    _id_proveedor_sucursal = " . (isset($this->_id_proveedor_sucursal) ? "{$this->_id_proveedor_sucursal}" : 'null') .
               "]";
    }

}
