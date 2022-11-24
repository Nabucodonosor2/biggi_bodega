function validate() {
	var vl_tabla = document.getElementById('ITEM_MOD_ARRIENDO');
	var aTR = vl_tabla.getElementsByTagName("tr");
	if (aTR.length==0) {
		alert('Debe ingresar al menos 1 item antes de grabar.');
		return false;
	}
	return true;
}
