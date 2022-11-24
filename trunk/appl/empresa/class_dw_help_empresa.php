<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

// Esta clase es auxiliar y la idea es que todo el codigo que sea comun entre help_empresa.php y helpo_lista_empresa.php este en esta clase
class dw_help_empresa extends datawindow {
	function dw_help_empresa($sql, $label_record='', $b_add_line_visible=false, $b_del_line_visible=false, $tipo_empresa = 'C') {
		parent::datawindow($sql, $label_record, $b_add_line_visible, $b_del_line_visible);

		// EMPRESA		
		$this->add_controls_empresa_help($tipo_empresa);		
	}
	function add_controls_empresa_help($tipo_empresa = 'C') {
		/* Agrega los constrols standar para manejar la selección de empresa con help					
			 Los anchos y maximos de cada campo quedan fijos, la idea es que sean iguales en todos los formularios
			 si se desean tamaños distintos se debe reiimplementar esta función
			 
			 $tipo_empresa: Es un string con alguna combinacion de 'C', 'P' y 'T'.  Para indicar el tipo de empresas que se deben desplegar
			 								'C' clientes
			 								'P' proveedores
			 								'T' Trabajador o personal
		*/
		$java_script = "help_empresa(this, '".$tipo_empresa."');";
		$this->add_control($control = new edit_num('COD_EMPRESA', 10, 10));
		$control->set_onChange($java_script);
		$control->con_separador_miles = false;

		$this->add_control($control = new edit_num('RUT', 10, 10));
		$control->set_onChange($java_script);

		$this->add_control(new static_text('DIG_VERIF'));
		
		$this->add_control($control = new edit_text_upper('ALIAS', 37, 100));
		$control->set_onChange($java_script);

		$this->add_control($control = new edit_text_upper('NOM_EMPRESA', 121, 100));
		$control->set_onChange($java_script);

		// sucursales de despacho y facturacion
		$this->add_control(new drop_down_sucursal('COD_SUCURSAL_FACTURA'));
		$this->add_control(new drop_down_sucursal('COD_SUCURSAL_DESPACHO'));
		
		// atencion
		$this->add_control(new drop_down_persona('COD_PERSONA'));
				
				
		$this->add_control(new static_text('SUJETO_A_APROBACION'));
		$this->add_control(new static_text('GIRO'));
		$this->add_control(new static_text('DIRECCION_FACTURA'));
		$this->add_control(new static_text('DIRECCION_DESPACHO'));
		$this->add_control(new static_text('MAIL_CARGO_PERSONA'));
		
		// mandatorys
		$this->set_mandatory('RUT', 'RUT de la empresa');
		$this->set_mandatory('COD_SUCURSAL_FACTURA', 'Sucursal de facturación');
		$this->set_mandatory('COD_SUCURSAL_DESPACHO', 'Sucursal de despacho');
		$this->set_mandatory('COD_PERSONA', 'Atención a');
	}
}
?>