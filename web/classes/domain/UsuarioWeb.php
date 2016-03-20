<?php

/**
 * 
 */

include_once('mysql.class.php');

/**
 * @author Luis Hernandez
 *
 */

class UsuarioWeb
{
    private $_id;
    private $_nombre_usuario;
    private $_contrasena;
    private $_nombre;
    private $_apellidos;
    private $_email;
    private $_activo;
    private $_borrado;

    private static $_str_sql = "
  SELECT
  us.id_usuario_web AS id,
  us.nombre_usuario AS nombre_usuario,
  us.contrasena AS contrasena,
  us.nombre AS nombre,
  us.apellidos AS apellidos,
  us.email AS email,
  0+us.activo AS activo,
  0+us.borrado AS borrado
  FROM usuario_web us";

    public function __construct() {
        $this->_id = null;
        $this->_nombre_usuario = null;
        $this->_contrasena = null;
        $this->_nombre = null;
        $this->_apellidos = null;
        $this->_email = null;
        $this->_activo = null;
        $this->_borrado = null;

    }

    public function __set($name, $value) {

        //echo "Setting '$name' to '$value'\n";
        switch ($name) {
            case "id" :
                $this->_id = $value;
                break;
            case "nombre_usuario" :
                $this->_nombre_usuario = $value;
                break;
            case "contrasena" :
                $this->_contrasena = $value;
                break;
            case "nombre" :
                $this->_nombre = $value;
                break;
            case "apellidos" :
                $this->_apellidos = $value;
                break;
            case "email" :
                $this->_email = $value;
                break;
            case "activo" :
                $this->_activo = $value;
                break;
            case "borrado" :
                $this->_borrado = $value;
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
            case "nombre_usuario" :
                return $this->_nombre_usuario;
            case "contrasena" :
                return $this->_contrasena;
            case "nombre" :
                return $this->_nombre;
            case "apellidos" :
                return $this->_apellidos;
            case "email" :
                return $this->_email;
            case "activo" :
                return $this->_activo;
            case "borrado" :
                return $this->_borrado;
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
        $ret = new UsuarioWeb();

        $ret->_id = $p_ar[0]['id'];
        $ret->_nombre_usuario = $p_ar[0]['nombre_usuario'];
        $ret->_contrasena = $p_ar[0]['contrasena'];
        $ret->_nombre = $p_ar[0]['nombre'];
        $ret->_apellidos = $p_ar[0]['apellidos'];
        $ret->_email = $p_ar[0]['email'];
        $ret->_activo = $p_ar[0]['activo'];
        $ret->_borrado = $p_ar[0]['borrado'];

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
        return self::getByParameter($p_db, "id_usuario_web", $p_id);
    }
    
    public static function getByUsername($p_conn, $p_username) {
        return self::getByParameter($p_conn, "nombre_usuario", $p_username);
    }
    
    public static function seek($p_db, $p_parameters, $p_order, $p_direction, $p_offset, $p_limit, $p_get_total = false) {

        $result         = new stdClass();
        $array_clauses  = array();

        $str_sql = self::$_str_sql;

        foreach($p_parameters as $key => $value) {
                            if ($key == 'id_usuario_web') {
                $array_clauses[] = "us.id_usuario_web = $value";
            }
                else         if ($key == "no borrado") {
             $array_clauses[] = "us.borrado = 0";
        }
                else         if ($key == "borrado") {
            $array_clauses[] = "us.borrado = 1";
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
            "  UPDATE usuario_web" .
            "  SET" .
            "  nombre_usuario = " . (isset($this->_nombre_usuario) ? "'{$this->_nombre_usuario}'" : 'null') . "," .
            "  contrasena = " . (isset($this->_contrasena) ? "'{$this->_contrasena}'" : 'null') . "," .
            "  nombre = " . (isset($this->_nombre) ? "'{$this->_nombre}'" : 'null') . "," .
            "  apellidos = " . (isset($this->_apellidos) ? "'{$this->_apellidos}'" : 'null') . "," .
            "  email = " . (isset($this->_email) ? "'{$this->_email}'" : 'null') . "," .
            "  activo = " . (isset($this->_activo) ? "b'{$this->_activo}'" : 'null') . "," .
            "  borrado = " . (isset($this->_borrado) ? "b'{$this->_borrado}'" : 'null') .
            "  WHERE" .
            "  id_usuario_web = {$this->_id}";

        //echo '<br>' . $str_sql . '<br>';

        if ($p_db->Query($str_sql) === false) {
            throw new Exception('Error al actualizar registro: ' . $p_db->Error(), $p_db->ErrorNumber(), null);
        }
    }

    public function insert($p_db) {

        $str_sql =
            "  INSERT INTO usuario_web" .
            "  (" .
            "  nombre_usuario, " .
            "  contrasena, " .
            "  nombre, " .
            "  apellidos, " .
            "  email)" .
            "  VALUES" .
            "  (" .
            "  " . (isset($this->_nombre_usuario) ? "''{$this->_nombre_usuario}''" : 'null') . "," .
            "  " . (isset($this->_contrasena) ? "''{$this->_contrasena}''" : 'null') . "," .
            "  " . (isset($this->_nombre) ? "''{$this->_nombre}''" : 'null') . "," .
            "  " . (isset($this->_apellidos) ? "''{$this->_apellidos}''" : 'null') . "," .
            "  " . (isset($this->_email) ? "''{$this->_email}''" : 'null');

        //echo '<br>' . $str_sql . '<br>';

        if ($p_db->Query($str_sql) === false) {
            throw new Exception('Error al insertar registro: ' . $p_db->Error(), $p_db->ErrorNumber(), null);
        }


        $ar_id = $p_db->QueryArray('SELECT LAST_INSERT_ID()');

        $this->_id = $ar_id[0][0];
    }

    public function delete($p_db) {


        $str_sql =
            "  DELETE FROM  usuario_web" .
            "  WHERE" .
            "  id_usuario_web = {$this->_id}";

        //echo '<br>' . $str_sql . '<br>';

        if ($p_db->Query($str_sql) === false) {
            throw new Exception('Error al borrar registro: ' . $p_db->Error(), $p_db->ErrorNumber(), null);
        }
    }


    public function load($p_db) {
        $obj = self::getById($p_db, $this->_id);

        if ($obj != null) {

            $this->_id = $obj->_id;
            $this->_nombre_usuario = $obj->_nombre_usuario;
            $this->_contrasena = $obj->_contrasena;
            $this->_nombre = $obj->_nombre;
            $this->_apellidos = $obj->_apellidos;
            $this->_email = $obj->_email;
            $this->_activo = $obj->_activo;
            $this->_borrado = $obj->_borrado;
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
        return "UsuarioWeb [" .
               "    _id = " . (isset($this->_id) ? "{$this->_id}" : 'null') . "," .
               "    _nombre_usuario = " . (isset($this->_nombre_usuario) ? "'{$this->_nombre_usuario}'" : 'null') . "," .
               "    _contrasena = " . (isset($this->_contrasena) ? "'{$this->_contrasena}'" : 'null') . "," .
               "    _nombre = " . (isset($this->_nombre) ? "'{$this->_nombre}'" : 'null') . "," .
               "    _apellidos = " . (isset($this->_apellidos) ? "'{$this->_apellidos}'" : 'null') . "," .
               "    _email = " . (isset($this->_email) ? "'{$this->_email}'" : 'null') . "," .
               "    _activo = " . (isset($this->_activo) ? "b'{$this->_activo}'" : 'null') . "," .
               "    _borrado = " . (isset($this->_borrado) ? "b'{$this->_borrado}'" : 'null') .
               "]";
    }


	public function tieneAcceso($p_db, $p_acceso) {
		$ret = false;
		
		// usuario 'admin' tienen todos los privilegios
		if ($this->_nombre_usuario == 'admin') {
			$ret = true;
		}
		else {
			$str_sql =
				"  SELECT ua.*" .
			 	"  FROM usuario_acceso ua" .
				"  JOIN usuario_web u ON u.id_usuario_web = ua.id_usuario_FK" .
				"  JOIN acceso a ON a.id_acceso = ua.id_acceso_FK" .
				"  WHERE a.descripcion = '$p_acceso'" .
				"  AND u.id_usuario_web = {$this->_id}";
			
			//echo '<br>' . $str_sql . '<br>';
			
			if ($p_db->Query($str_sql) !== false) {
				if ($p_db->RowCount() != 0) {
					$ret = true;	
				}
	 		}
			else {
				throw new Exception('Error al obtener registro: ' . $p_db->Error(), $p_db->ErrorNumber(), null);
			}
		}
		
		return $ret;
		
	}
}
