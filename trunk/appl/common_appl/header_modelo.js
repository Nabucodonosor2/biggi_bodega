function dlg_find_modelo(ve_nom_header, ve_valor_filtro, ve_campo) {
	var id_campo = ve_campo.id;
   var url = "../common_appl/dlg_find_modelo.php?nom_header="+URLEncode(ve_nom_header)+"&valor_filtro="+URLEncode(ve_valor_filtro);
	$.showModalDialog({
		 url: url,
		 dialogArguments: '',
		 height: 170,
		 width: 500,
		 scrollable: false,
		 onClose: function(){ 
		 	var returnVal = this.returnValue;
		 	var input = document.createElement("input");
				input.setAttribute("type", "hidden");
				input.setAttribute("name", id_campo+'_X');
				input.setAttribute("id", id_campo+'_X');
				
				document.getElementById("output").appendChild(input);
			if (returnVal == null)		
				document.getElementById('wo_hidden').value = '__BORRAR_FILTRO__';	
			else {
				document.getElementById('wo_hidden').value = returnVal.trim();
			}
			document.output.submit();
			return true;	
		}
	});	
}
