<?php

/**
 * 
 */

include_once('mysql.class.php');

/**
 * @author Luis Hernandez
 *
 */

class Factura
{
    private $_id;
    private $_id_proveedor;
    private $_id_medio_pago;
    private $_numero_tx;
    private $_detalle_pago;
    private $_pagada;

    private static $_str_sql = "
  SELECT
  fa.folio AS id,
  fa.id_proveedor AS id_proveedor,
  fa.id_medio_pago AS id_medio_pago,
  fa.numero_tx AS numero_tx,
  fa.detalle_pago AS detalle_pago,
  0+fa.pagada AS pagada
  FROM factura fa";

    public function __construct() {
        $this->_id = null;
        $this->_id_proveedor = null;
        $this->_id_medio_pago = null;
        $this->_numero_tx = null;
        $this->_detalle_pago = null;
        $this->_pagada = null;

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
            case "id_medio_pago" :
                $this->_id_medio_pago = $value;
                break;
            case "numero_tx" :
                $this->_numero_tx = $value;
                break;
            case "detalle_pago" :
                $this->_detalle_pago = $value;
                break;
            case "pagada" :
                $this->_pagada = $value;
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
            case "id_medio_pago" :
                return $this->_id_medio_pago;
            case "numero_tx" :
                return $this->_numero_tx;
            case "detalle_pago" :
                return $this->_detalle_pago;
            case "pagada" :
                return $this->_pagada;
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
        $ret = new Factura();

        $ret->_id = $p_ar[0]['id'];
        $ret->_id_proveedor = $p_ar[0]['id_proveedor'];
        $ret->_id_medio_pago = $p_ar[0]['id_medio_pago'];
        $ret->_numero_tx = $p_ar[0]['numero_tx'];
        $ret->_detalle_pago = $p_ar[0]['detalle_pago'];
        $ret->_pagada = $p_ar[0]['pagada'];

        return $ret;
    }

    public static function getByParameter($p_db, $p_key, $p_value) {
        $ret = null;
        
        $str_sql = self::$_str_sql .
            "  WHERE fa." . $p_key . " = " . $p_value .
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
        return self::getByParameter($p_db, "id_factura", $p_id);
    }
    
    public static function seek($p_db, $p_parameters, $p_order, $p_direction, $p_offset, $p_limit, $p_get_total = false) {

        $result         = new stdClass();
        $array_clauses  = array();

        $str_sql = self::$_str_sql;

        foreach($p_parameters as $key => $value) {
                            if ($key == 'folio') {
                $array_clauses[] = "fa.folio = $value";
            }
                else             if ($key == 'id_medio_pago') {
                $array_clauses[] = "fa.id_medio_pago = $value";
            }
                else             if ($key == 'id_proveedor') {
                $array_clauses[] = "fa.id_proveedor = $value";
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
            "  UPDATE factura" .
            "  SET" .
            "  numero_tx = " . (isset($this->_numero_tx) ? "'{$this->_numero_tx}'" : 'null') . "," .
            "  detalle_pago = " . (isset($this->_detalle_pago) ? "'{$this->_detalle_pago}'" : 'null') . "," .
            "  pagada = " . (isset($this->_pagada) ? "b'{$this->_pagada}'" : 'null') .
            "  WHERE" .
            "  folio = {$this->_id}";

        //echo '<br>' . $str_sql . '<br>';

        if ($p_db->Query($str_sql) === false) {
            throw new Exception('Error al actualizar registro: ' . $p_db->Error(), $p_db->ErrorNumber(), null);
        }
    }

    public function insert($p_db) {

        $str_sql =
            "  INSERT INTO factura" .
            "  (" .
            "  folio, " .
            "  id_proveedor, " .
            "  id_medio_pago, " .
            "  numero_tx, " .
            "  detalle_pago, " .
            "  pagada)" .
            "  VALUES" .
            "  (" .
            "  " . (isset($this->_id) ? "{$this->_id}" : 'null') . "," .
            "  " . (isset($this->_id_proveedor) ? "{$this->_id_proveedor}" : 'null') . "," .
            "  " . (isset($this->_id_medio_pago) ? "{$this->_id_medio_pago}" : 'null') . "," .
            "  " . (isset($this->_numero_tx) ? "''{$this->_numero_tx}''" : 'null') . "," .
            "  " . (isset($this->_detalle_pago) ? "''{$this->_detalle_pago}''" : 'null') . "," .
            "  " . (isset($this->_pagada) ? "b'{$this->_pagada}'" : 'null');

        //echo '<br>' . $str_sql . '<br>';

        if ($p_db->Query($str_sql) === false) {
            throw new Exception('Error al insertar registro: ' . $p_db->Error(), $p_db->ErrorNumber(), null);
        }

    }

    public function delete($p_db) {


        $str_sql =
            "  DELETE FROM  factura" .
            "  WHERE" .
            "  folio = {$this->_id}";

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
            $this->_id_medio_pago = $obj->_id_medio_pago;
            $this->_numero_tx = $obj->_numero_tx;
            $this->_detalle_pago = $obj->_detalle_pago;
            $this->_pagada = $obj->_pagada;
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
        return "Factura [" .
               "    _id = " . (isset($this->_id) ? "{$this->_id}" : 'null') . "," .
               "    _id_proveedor = " . (isset($this->_id_proveedor) ? "{$this->_id_proveedor}" : 'null') . "," .
               "    _id_medio_pago = " . (isset($this->_id_medio_pago) ? "{$this->_id_medio_pago}" : 'null') . "," .
               "    _numero_tx = " . (isset($this->_numero_tx) ? "'{$this->_numero_tx}'" : 'null') . "," .
               "    _detalle_pago = " . (isset($this->_detalle_pago) ? "'{$this->_detalle_pago}'" : 'null') . "," .
               "    _pagada = " . (isset($this->_pagada) ? "b'{$this->_pagada}'" : 'null') .
               "]";
    }

}
