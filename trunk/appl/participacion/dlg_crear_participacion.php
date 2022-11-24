<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$K_ESTADO_EMITIDA 			= 1;
$K_ESTADO_CONFIRMADA		= 2;

$cod_usuario = session::get("COD_USUARIO");
$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);

$sql = "select null COD_USUARIO
				,null COD_TIPO_ORDEN_PAGO
				,null COD_CC
				,'N' ES_SUELDO";
$dw = new datawindow($sql);

$sql = "select '0' COD_USUARIO, ''NOM_USUARIO, 'N' RECIBE_SUELDO
		UNION
		SELECT distinct U.COD_USUARIO
			,U.NOM_USUARIO
			,U.RECIBE_SUELDO
		FROM USUARIO U, ORDEN_PAGO OP
		WHERE OP.COD_EMPRESA = U.COD_EMPRESA
		  and dbo.f_get_tiene_acceso (".$cod_usuario.", 'ORDEN_PAGO', U.COD_USUARIO, null) = 1
		  and OP.COD_ORDEN_PAGO not in (SELECT COD_ORDEN_PAGO FROM PARTICIPACION_ORDEN_PAGO POP, PARTICIPACION P
															WHERE POP.COD_PARTICIPACION = P.COD_PARTICIPACION
															AND COD_ESTADO_PARTICIPACION in($K_ESTADO_EMITIDA, $K_ESTADO_CONFIRMADA))
		ORDER BY NOM_USUARIO";
$dw->add_control($control = new drop_down_dw('COD_USUARIO', $sql, 0, '', false));
$control->set_onChange("dlg_tipo_op(this);");

$sql_tipo_op = "SELECT '0' COD_TIPO_ORDEN_PAGO, ''NOM_TIPO_ORDEN_PAGO
				UNION
				SELECT '' COD_TIPO_ORDEN_PAGO
						,'' NOM_TIPO_ORDEN_PAGO";
$dw->add_control($control = new drop_down_dw('COD_TIPO_ORDEN_PAGO', $sql_tipo_op, 0, '', false));
$control->set_onChange("dlg_centro_costo(this);");

$sql_centro_costo = "SELECT '0' COD_CC, ''NOM_CC
					 UNION
					 SELECT '' COD_CC
							,'' NOM_CC";
$dw->add_control(new drop_down_dw('COD_CC', $sql_centro_costo, 0, '', false));
$dw->add_control(new edit_check_box('ES_SUELDO', 'S', 'N', 'Sueldo'));

$dw->insert_row();

$temp = new Template_appl('dlg_crear_participacion.htm');	
$dw->habilitar($temp, true);


print $temp->toString();
?>