function validate() {
	var aTR = get_TR('ITEM_PROYECTO_INGRESO');
	for (var i = 0; i < aTR.length; i++){
		
		var cod_cuenta_contable = document.getElementById('COD_CUENTA_CONTABLE_' + i).value;
		
		if(cod_cuenta_contable == ''){
			alert('Debe ingresar una cuenta contable');
			
			document.getElementById('COD_CUENTA_CONTABLE_' + i).value = '';
			document.getElementById('COD_CUENTA_CONTABLE_' + i).focus();
			return false;
		}
	}
	return true;
}