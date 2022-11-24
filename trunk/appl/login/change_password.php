<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

class w_change_password extends w_base {
	function w_change_password() {
		parent::w_base('', '0505');
		$this->work_directory = getcwd();
	}
	function change_password($clave_actual, $clave_nueva, $clave_confirmacion) {
		$t = new Template_appl("change_password.htm");
		$t->setVar('W_CLAVE_ACTUAL', $clave_actual);
		$t->setVar('W_CLAVE_NUEVA', $clave_nueva);
		$t->setVar('W_CLAVE_CONFIRMACION', $clave_confirmacion);
		
		$t->setVar("WO_RUTA_MENU", $this->ruta_menu);
		$t->setVar("WO_FECHA_ACTUAL", 'Fecha Actual: '.$this->current_date());
		$t->setVar("WO_NOM_USUARIO", $this->nom_usuario);
		
		$menu = session::get('menu_appl');
		$menu->draw($t);
		
		print $t->toString();
	}	
	function validate_change_password() {
		
		$clave_actual				= $_POST['clave_actual'];
		$clave_nueva 				= $_POST['clave_nueva'];
		$clave_confirmacion = $_POST['clave_confirmacion'];
		$cod_usuario 				= session::get('COD_USUARIO');

		$sql = "select 	PASSWORD
				  from  USUARIO
    			 where  COD_USUARIO = $cod_usuario";
    			 
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);		
		$result = $db->build_results($sql);
		$passwd_bd = $result[0]['PASSWORD'];
		
		$caracteres_validos = '';
		if (ereg("^[a-zA-Z0-9]{6,20}$", $clave_nueva)) {
     	      $caracteres_validos = 'S'; 		
		}else{
       		  $caracteres_validos = 'N';
		}
  		
    	if ($passwd_bd != $this->f_encriptar($clave_actual)) {
			$this->change_password($clave_actual, $clave_nueva, $clave_confirmacion, $cod_usuario);
			$this->message('Clave Actual no Valida');
			return;
		}
		elseif ($clave_nueva == '') {
			$this->change_password($clave_actual, $clave_nueva, $clave_confirmacion, $cod_usuario);
			$this->message('Debe Ingresar Clave Nueva');
			return;
		}
		elseif ($clave_confirmacion == '') {
			$this->change_password($clave_actual, $clave_nueva, $clave_confirmacion, $cod_usuario);
			$this->message('Debe Ingresar Clave Confirmación');
			return;
		}
		elseif (strlen($clave_nueva) < 6) {
			$this->change_password($clave_actual, $clave_nueva, $clave_confirmacion, $cod_usuario);
			$this->message('La nueva Clave debe tener minimo 6 caracteres');
			return;
		}
		elseif ($clave_nueva != $clave_confirmacion) {
			$this->change_password($clave_actual, $clave_nueva, $clave_confirmacion, $cod_usuario);
			$this->message('La nueva Clave no es identica a la de Confirmación');
			return;
		}
		elseif ($clave_nueva == $clave_actual) {
			$this->change_password($clave_actual, $clave_nueva, $clave_confirmacion, $cod_usuario);
			$this->message('La nueva Clave nueva no puede ser igual a la anterior');
			return;
		}
		elseif ($caracteres_validos <>   'S') {
			$this->change_password($clave_actual, $clave_nueva, $clave_confirmacion, $cod_usuario);
			$this->message('La nueva Clave tiene caracteres no validos ,esta deben solo tener letras y numeros');
			return;
		}
		elseif (!preg_match('`[0-9]`',$clave_nueva)){
			$this->change_password($clave_actual, $clave_nueva, $clave_confirmacion, $cod_usuario);
			$this->message('La clave debe tener al menos un caracter numérico');
			return;
		}
		elseif (!preg_match('`[a-zA-Z]`',$clave_nueva)){
			$this->change_password($clave_actual, $clave_nueva, $clave_confirmacion, $cod_usuario);
			$this->message('La clave debe tener al menos una letra');
			return;
		}
				
		$clave_actual	= $this->f_encriptar($clave_actual);
		$clave_nueva = $this->f_encriptar($clave_nueva);
		$clave_confirmacion = $this->f_encriptar($clave_confirmacion);
		
		$db->BEGIN_TRANSACTION();
		$sp = 'sp_change_password';
		$param = "'$clave_actual', '$clave_nueva', '$clave_confirmacion', $cod_usuario";
		if ($db->EXECUTE_SP($sp, $param)) {
			$db->COMMIT_TRANSACTION();
			$this->presentacion('Su clave fue cambiada exitosamente.');
		}
		else {
			$db->ROLLBACK_TRANSACTION();
			$this->error('No se pudo cambiar la clave.');
		}
	}
}

$w = new w_change_password();
if (isset($_POST['b_ingresar']))
	$w->validate_change_password();
elseif(isset($_POST['b_cancelar']))	
	header ('Location:'."login.php");
else
	$w->change_password('', '','');
?>