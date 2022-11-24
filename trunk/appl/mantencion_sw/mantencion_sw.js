function cambia_estado(ve_cod_estado_solucion_sw) {
	var K_RECHAZADO = 100;
	var vl_TR_RESPUESTA = document.getElementById('TR_RESPUESTA_CLIENTE');
	if (ve_cod_estado_solucion_sw.value == K_RECHAZADO)
		vl_TR_RESPUESTA.style.display = '';
	else 
		vl_TR_RESPUESTA.style.display = 'none';
}