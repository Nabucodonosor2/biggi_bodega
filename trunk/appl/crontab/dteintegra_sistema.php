<?php
/*
 * LIBRERIA PARA EL API DE LIBRE_DT
 * LICENCIA: GNU FREE DOCUMENTATION LICENSE 1.3
 * FECHA CREACIÓN:28/04/2020
 * ULTIMA MODIFICACIÓN: 28/042020
 * Nota:
 * servicio de consulta frente a documentos registrados en INTEGRADTE
*/

ini_set('display_errors', '1');
include(dirname(__FILE__)."/../../appl.ini");
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
session::set('K_ROOT_DIR', K_ROOT_DIR);

class documentos_integradte {
    
    const K_PARAM_HASH = 200;
    const K_FECHA_DESDE = 5;

    function documentos_integradte() {
        $db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
        	
		$dte = new dte();
			
		//Se le pasa como variable hash de la clase obtenida en parametros en la BD
		$SqlHash = "SELECT  dbo.f_get_parametro(".self::K_PARAM_HASH.") K_HASH
                            ,REPLACE(REPLACE(dbo.f_get_parametro(20),'.',''),'-5','') as RUTEMISOR
                            ,convert(varchar,GETDATE()- ".self::K_FECHA_DESDE.",23) FECHA_DESDE
                            ,convert(varchar,GETDATE(),23) FECHA_HASTA";
		$Datos_Hash = $db->build_results($SqlHash);
		$dte->hash = $Datos_Hash[0]['K_HASH'];
		$rutemisor = $Datos_Hash[0]['RUTEMISOR'];
		$fecha_desde = $Datos_Hash[0]['FECHA_DESDE'];
		$fecha_hasta = $Datos_Hash[0]['FECHA_HASTA'];
		
		/*"receptor": null,
		 "razon_social": null,
		 "dte": null,
		 "folio": null,
		 "fecha": null,
		 "total": null,
		 "usuario": null,
		 "fecha_desde": null,
		 "fecha_hasta": null,
		 "total_desde": null,
		 "total_hasta": null,
		 "sucursal_sii": null,
		 "periodo": null,
		 "receptor_evento": null,
		 "cedido": null,
		 "xml": []*/
		
		$filtrolistar             = array();
		//$filtrolistar['fecha']	= $fecha_revision;
		//$filtrolistar['fecha_desde']		= '2020-04-01';
		$filtrolistar['fecha_desde']		= $fecha_desde;
		$filtrolistar['fecha_hasta']		= $fecha_hasta;
    
		//se agrega el json_para codificacion requerida por libre_dte.
		$objEnJson = json_encode($filtrolistar,$rutemisor);
		$response = $dte->listar_dte($objEnJson,$rutemisor);
		$response = explode("[", $response);
		
		$response_add = '['.$response[1];
	
		$listasdte = json_decode($response_add);
		
		$sp = 'spu_doc_integradte';
		$operacion = 'DOC_EXISTENTE';
		
		
		foreach($listasdte as $lista){
		    $vl_dte           = $lista->dte;
		    $vl_tipo          = utf8_decode($lista->tipo);
		    $vl_folio         = $lista->folio;
		    $vl_receptor      = $lista->receptor;
		    $vl_razon_social  = utf8_decode($lista->razon_social);
		    $vl_fecha         = $lista->fecha;
		    $vl_total         = $lista->total;
            $vl_usuario       = $lista->usuario;
            
            $vl_dte		 		= ($vl_dte =='') ? "null" : "'$vl_dte'";
            $vl_tipo		 	= ($vl_tipo =='') ? "null" : "'$vl_tipo'";
            $vl_folio		 	= ($vl_folio =='') ? "null" : "'$vl_folio'";
            $vl_receptor		= ($vl_receptor =='') ? "null" : "'$vl_receptor'";
            $vl_razon_social    = ($vl_razon_social =='') ? "null" : "'$vl_razon_social'";
            $vl_fecha		 	= ($vl_fecha =='') ? "null" : "'$vl_fecha'";
            $vl_total   	    = (strlen($vl_total)==0) ? "null" : $vl_total;
            $vl_usuario		 	= ($vl_usuario =='') ? "null" : "'$vl_usuario'";
            
            $param = "'$operacion'
                        ,$vl_dte
			            ,$vl_tipo
            			,$vl_folio
            			,$vl_receptor
                        ,$vl_razon_social
                        ,$vl_fecha
                        ,$vl_total
                        ,$vl_usuario";

		    if (!$db->EXECUTE_SP($sp, $param))
		        return false;
		}
			
    }
}
$a = new documentos_integradte();
$response = $a->documentos_integradte();
echo $response;
