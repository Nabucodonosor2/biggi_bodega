function haga_scroll(ve_cod_nota_venta) {
	var vl_scroll =  document.getElementById('wo_scroll');

	for (var i=0; i<300; i++) {
		var vl_hidden = document.getElementById('COD_NOTA_VENTA_H_' + i);
		if (vl_hidden) {
			if (vl_hidden.value == ve_cod_nota_venta) {
				var puntodescroll = vl_hidden.parentNode.parentNode.offsetTop;
				vl_scroll.scrollTop = puntodescroll;
			}
		}
	}
}