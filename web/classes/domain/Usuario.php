<?php

/**
 * 
 */

include_once('mysql.class.php');

/**
 * @author Luis Hernandez
 *
 */

class Usuario
{
    private $_id;
    private $_id_comuna;
    private $_nombreusuario;
    private $_contrasena;
    private $_nombre;
    private $_apellido_paterno;
    private $_apellido_materno;
    private $_correo;
    private $_telefono;
    private $_fecha_creacion;
    private $_activo;

    private static $_str_sql = "
  SELECT
  us.id_usuario AS id,
  us.id_comuna AS id_comuna,
  us.nombreusuario AS nombreusuario,
  us.contrasena AS contrasena,
  us.nombre AS nombre,
  us.apellido_paterno AS apellido_paterno,
  us.apellido_materno AS apellido_materno,
  us.correo AS correo,
  us.telefono AS telefono,
  DATE_FORMAT(us.fecha_creacion, '%Y-%m-%d %H:%i:%s') AS fecha_creacion,
  0+us.activo AS activo
  FROM usuario us";

    public function __construct() {
        $this->_id = null;
        $this->_id_comuna = null;
        $this->_nombreusuario = null;
        $this->_contrasena = null;
        $this->_nombre = null;
        $this->_apellido_paterno = null;
        $this->_apellido_materno = null;
        $this->_correo = null;
        $this->_telefono = null;
        $this->_fecha_creacion = null;
        $this->_activo = null;

    }

    public function __set($name, $value) {

        //echo "Setting '$name' to '$value'\n";
        switch ($name) {
            case "id" :
                $this->_id = $value;
                break;
            case "id_comuna" :
                $this->_id_comuna = $value;
                break;
            case "nombreusuario" :
                $this->_nombreusuario = $value;
                break;
            case "contrasena" :
                $this->_contrasena = $value;
                break;
            case "nombre" :
                $this->_nombre = $value;
                break;
            case "apellido_paterno" :
                $this->_apellido_paterno = $value;
                break;
            case "apellido_materno" :
                $this->_apellido_materno = $value;
                break;
            case "correo" :
                $this->_correo = $value;
                break;
            case "telefono" :
                $this->_telefono = $value;
                break;
            case "fecha_creacion" :
                $this->_fecha_creacion = $value;
                break;
            case "activo" :
                $this->_activo = $value;
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
            case "id_comuna" :
                return $this->_id_comuna;
            case "nombreusuario" :
                return $this->_nombreusuario;
            case "contrasena" :
                return $this->_contrasena;
            case "nombre" :
                return $this->_nombre;
            case "apellido_paterno" :
                return $this->_apellido_paterno;
            case "apellido_materno" :
                return $this->_apellido_materno;
            case "correo" :
                return $this->_correo;
            case "telefono" :
                return $this->_telefono;
            case "fecha_creacion" :
                return $this->_fecha_creacion;
            case "activo" :
                return $this->_activo;
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
        $ret = new Usuario();

        $ret->_id = $p_ar[0]['id'];
        $ret->_id_comuna = $p_ar[0]['id_comuna'];
        $ret->_nombreusuario = $p_ar[0]['nombreusuario'];
        $ret->_contrasena = $p_ar[0]['contrasena'];
        $ret->_nombre = $p_ar[0]['nombre'];
        $ret->_apellido_paterno = $p_ar[0]['apellido_paterno'];
        $ret->_apellido_materno = $p_ar[0]['apellido_materno'];
        $ret->_correo = $p_ar[0]['correo'];
        $ret->_telefono = $p_ar[0]['telefono'];
        $ret->_fecha_creacion = $p_ar[0]['fecha_creacion'];
        $ret->_activo = $p_ar[0]['activo'];

        return $ret;
    }

    public static function getByParameter($p_db, $p_key, $p_value) {
        $ret = null;
        
        $str_sql = self::$_str_sql .
            "  WHERE us." . $p_key . " = " . $p_value .
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
        return self::getByParameter($p_db, "id_usuario", $p_id);
    }
    
    public static function seek($p_db, $p_parameters, $p_order, $p_direction, $p_offset, $p_limit, $p_get_total = false) {

        $result         = new stdClass();
        $array_clauses  = array();

        $str_sql = self::$_str_sql;

        foreach($p_parameters as $key => $value) {
                            if ($key == 'id_usuario') {
                $array_clauses[] = "us.id_usuario = $value";
            }
                else             if ($key == 'id_comuna') {
                $array_clauses[] = "us.id_comuna = $value";
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
            "  UPDATE usuario" .
            "  SET" .
            "  nombreusuario = " . (isset($this->_nombreusuario) ? "'{$this->_nombreusuario}'" : 'null') . "," .
            "  contrasena = " . (isset($this->_contrasena) ? "'{$this->_contrasena}'" : 'null') . "," .
            "  nombre = " . (isset($this->_nombre) ? "'{$this->_nombre}'" : 'null') . "," .
            "  apellido_paterno = " . (isset($this->_apellido_paterno) ? "'{$this->_apellido_paterno}'" : 'null') . "," .
            "  apellido_materno = " . (isset($this->_apellido_materno) ? "'{$this->_apellido_materno}'" : 'null') . "," .
            "  correo = " . (isset($this->_correo) ? "'{$this->_correo}'" : 'null') . "," .
            "  telefono = " . (isset($this->_telefono) ? "'{$this->_telefono}'" : 'null') . "," .
            "  fecha_creacion = " . (isset($this->_fecha_creacion) ? "STR_TO_DATE('{$this->_fecha_creacion}', '%Y-%m-%d %H:%i:%s')" : 'null') . "," .
            "  activo = " . (isset($this->_activo) ? "b'{$this->_activo}'" : 'null') .
            "  WHERE" .
            "  id_usuario = {$this->_id}";

        //echo '<br>' . $str_sql . '<br>';

        if ($p_db->Query($str_sql) === false) {
            throw new Exception('Error al actualizar registro: ' . $p_db->Error(), $p_db->ErrorNumber(), null);
        }
    }

    public function insert($p_db) {

        $str_sql =
            "  INSERT INTO usuario" .
            "  (" .
            "  id_comuna, " .
            "  nombreusuario, " .
            "  contrasena, " .
            "  nombre, " .
            "  apellido_paterno, " .
            "  apellido_materno, " .
            "  correo, " .
            "  telefono, " .
            "  fecha_creacion)" .
            "  VALUES" .
            "  (" .
            "  " . (isset($this->_id_comuna) ? "{$this->_id_comuna}" : 'null') . "," .
            "  " . (isset($this->_nombreusuario) ? "''{$this->_nombreusuario}''" : 'null') . "," .
            "  " . (isset($this->_contrasena) ? "''{$this->_contrasena}''" : 'null') . "," .
            "  " . (isset($this->_nombre) ? "''{$this->_nombre}''" : 'null') . "," .
            "  " . (isset($this->_apellido_paterno) ? "''{$this->_apellido_paterno}''" : 'null') . "," .
            "  " . (isset($this->_apellido_materno) ? "''{$this->_apellido_materno}''" : 'null') . "," .
            "  " . (isset($this->_correo) ? "''{$this->_correo}''" : 'null') . "," .
            "  " . (isset($this->_telefono) ? "''{$this->_telefono}''" : 'null') . "," .
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
            "  DELETE FROM  usuario" .
            "  WHERE" .
            "  id_usuario = {$this->_id}";

        //echo '<br>' . $str_sql . '<br>';

        if ($p_db->Query($str_sql) === false) {
            throw new Exception('Error al borrar registro: ' . $p_db->Error(), $p_db->ErrorNumber(), null);
        }
    }


    public function load($p_db) {
        $obj = self::getById($p_db, $this->_id);

        if ($obj != null) {

            $this->_id = $obj->_id;
            $this->_id_comuna = $obj->_id_comuna;
            $this->_nombreusuario = $obj->_nombreusuario;
            $this->_contrasena = $obj->_contrasena;
            $this->_nombre = $obj->_nombre;
            $this->_apellido_paterno = $obj->_apellido_paterno;
            $this->_apellido_materno = $obj->_apellido_materno;
            $this->_correo = $obj->_correo;
            $this->_telefono = $obj->_telefono;
            $this->_fecha_creacion = $obj->_fecha_creacion;
            $this->_activo = $obj->_activo;
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
        return "Usuario [" .
               "    _id = " . (isset($this->_id) ? "{$this->_id}" : 'null') . "," .
               "    _id_comuna = " . (isset($this->_id_comuna) ? "{$this->_id_comuna}" : 'null') . "," .
               "    _nombreusuario = " . (isset($this->_nombreusuario) ? "'{$this->_nombreusuario}'" : 'null') . "," .
               "    _contrasena = " . (isset($this->_contrasena) ? "'{$this->_contrasena}'" : 'null') . "," .
               "    _nombre = " . (isset($this->_nombre) ? "'{$this->_nombre}'" : 'null') . "," .
               "    _apellido_paterno = " . (isset($this->_apellido_paterno) ? "'{$this->_apellido_paterno}'" : 'null') . "," .
               "    _apellido_materno = " . (isset($this->_apellido_materno) ? "'{$this->_apellido_materno}'" : 'null') . "," .
               "    _correo = " . (isset($this->_correo) ? "'{$this->_correo}'" : 'null') . "," .
               "    _telefono = " . (isset($this->_telefono) ? "'{$this->_telefono}'" : 'null') . "," .
               "    _fecha_creacion = " . (isset($this->_fecha_creacion) ? "'{$this->_fecha_creacion}'" : 'null') . "," .
               "    _activo = " . (isset($this->_activo) ? "b'{$this->_activo}'" : 'null') .
               "]";
    }

}
