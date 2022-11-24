function dlg_print() {
	var url = "dlg_print.php";
	$.showModalDialog({
		 url: url,
		 dialogArguments: '',
		 height: 290,
		 width: 300,
		 scrollable: false,
		 onClose: function(){ 
		 	var returnVal = this.returnValue;
		 	if (returnVal == null){		
				return false;
			}else {
				var input = document.createElement("input");
				input.setAttribute("type", "hidden");
				input.setAttribute("name", "b_print_x");
				input.setAttribute("id", "b_print_x");
				document.getElementById("output").appendChild(input);
				
				document.getElementById('wo_hidden').value = returnVal;
				document.output.submit();
		   		return true;
			}
		}
	});	
		
}