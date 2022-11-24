function dlg_print() {
	var args = "location:no;dialogLeft:400px;dialogTop:300px;dialogWidth:400px;dialogHeight:150px;dialogLocation:0;Toolbar:no;";
	var returnVal = window.showModalDialog("dlg_print_por_despachar.php", "_blank", args);
 	if (returnVal == null)
 		return false;
	else {	
		document.getElementById('wo_hidden').value = returnVal;
		document.output.submit();
   		return true;
	}
}