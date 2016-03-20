<?php

/**
 * 
 */

include_once('mysql.class.php');

/**
 * @author Luis Hernandez
 *
 */

class MarcaProveedor
{
    private $_id;
    private $_id_marca;
    private $_id_proveedor;
    private $_certificado;

    private static $_str_sql = "
  SELECT
  ma.id_marca_proveedor AS id,
  ma.id_marca AS id_marca,
  ma.id_proveedor AS id_proveedor,
  0+ma.certificado AS certificado
  FROM marca_proveedor ma";

    public function __construct() {
        $this->_id = null;
        $this->_id_marca = null;
        $this->_id_proveedor = null;
        $this->_certificado = null;

    }

    public function __set($name, $value) {

        //echo "Setting '$name' to '$value'\n";
        switch ($name) {
            case "id" :
                $this->_id = $value;
                break;
            case "id_marca" :
                $this->_id_marca = $value;
                break;
            case "id_proveedor" :
                $this->_id_proveedor = $value;
                break;
            case "certificado" :
                $this->_certificado = $value;
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
            case "id_marca" :
                return $this->_id_marca;
            case "id_proveedor" :
                return $this->_id_proveedor;
            case "certificado" :
                return $this->_certificado;
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
        $ret = new MarcaProveedor();

        $ret->_id = $p_ar[0]['id'];
        $ret->_id_marca = $p_ar[0]['id_marca'];
        $ret->_id_proveedor = $p_ar[0]['id_proveedor'];
        $ret->_certificado = $p_ar[0]['certificado'];

        return $ret;
    }

    public static function getByParameter($p_db, $p_key, $p_value) {
        $ret = null;
        
        $str_sql = self::$_str_sql .
            "  WHERE ma." . $p_key . " = " . $p_value .
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
        return self::getByParameter($p_db, "id_marca_proveedor", $p_id);
    }
    
    public static function seek($p_db, $p_parameters, $p_order, $p_direction, $p_offset, $p_limit, $p_get_total = false) {

        $result         = new stdClass();
        $array_clauses  = array();

        $str_sql = self::$_str_sql;

        foreach($p_parameters as $key => $value) {
                            if ($key == 'id_marca_proveedor') {
                $array_clauses[] = "ma.id_marca_proveedor = $value";
            }
                else             if ($key == 'id_marca') {
                $array_clauses[] = "ma.id_marca = $value";
            }
                else             if ($key == 'id_proveedor') {
                $array_clauses[] = "ma.id_proveedor = $value";
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
            "  UPDATE marca_proveedor" .
            "  SET" .
            "  certificado = " . (isset($this->_certificado) ? "b'{$this->_certificado}'" : 'null') .
            "  WHERE" .
            "  id_marca_proveedor = {$this->_id}";

        //echo '<br>' . $str_sql . '<br>';

        if ($p_db->Query($str_sql) === false) {
            throw new Exception('Error al actualizar registro: ' . $p_db->Error(), $p_db->ErrorNumber(), null);
        }
    }

    public function insert($p_db) {

        $str_sql =
            "  INSERT INTO marca_proveedor" .
            "  (" .
            "  id_marca_proveedor, " .
            "  id_marca, " .
            "  id_proveedor, " .
            "  certificado)" .
            "  VALUES" .
            "  (" .
            "  " . (isset($this->_id) ? "{$this->_id}" : 'null') . "," .
            "  " . (isset($this->_id_marca) ? "{$this->_id_marca}" : 'null') . "," .
            "  " . (isset($this->_id_proveedor) ? "{$this->_id_proveedor}" : 'null') . "," .
            "  " . (isset($this->_certificado) ? "b'{$this->_certificado}'" : 'null');

        //echo '<br>' . $str_sql . '<br>';

        if ($p_db->Query($str_sql) === false) {
            throw new Exception('Error al insertar registro: ' . $p_db->Error(), $p_db->ErrorNumber(), null);
        }

    }

    public function delete($p_db) {


        $str_sql =
            "  DELETE FROM  marca_proveedor" .
            "  WHERE" .
            "  id_marca_proveedor = {$this->_id}";

        //echo '<br>' . $str_sql . '<br>';

        if ($p_db->Query($str_sql) === false) {
            throw new Exception('Error al borrar registro: ' . $p_db->Error(), $p_db->ErrorNumber(), null);
        }
    }


    public function load($p_db) {
        $obj = self::getById($p_db, $this->_id);

        if ($obj != null) {

            $this->_id = $obj->_id;
            $this->_id_marca = $obj->_id_marca;
            $this->_id_proveedor = $obj->_id_proveedor;
            $this->_certificado = $obj->_certificado;
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
        return "MarcaProveedor [" .
               "    _id = " . (isset($this->_id) ? "{$this->_id}" : 'null') . "," .
               "    _id_marca = " . (isset($this->_id_marca) ? "{$this->_id_marca}" : 'null') . "," .
               "    _id_proveedor = " . (isset($this->_id_proveedor) ? "{$this->_id_proveedor}" : 'null') . "," .
               "    _certificado = " . (isset($this->_certificado) ? "b'{$this->_certificado}'" : 'null') .
               "]";
    }

}
