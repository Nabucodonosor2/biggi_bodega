function dlg_print_anexo() {
	var args = "location:no;dialogLeft:400px;dialogTop:200px;dialogWidth:450px;dialogHeight:150;dialogLocation:0;Toolbar:no;";
	var returnVal = window.showModalDialog("dlg_print_anexo.php", "_blank", args);
 	if (returnVal == null)
 		return false;
	else {
		document.getElementById('wi_hidden').value = returnVal;
		document.input.submit();
   		return true;
	}
}