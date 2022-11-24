<?php
//require_once(dirname(__FILE__)."/../auto_load.php");
//require_once(session::get('K_ROOT_DIR')."appl.ini");	

/**
* Database class
*
* @author    Sven Wagener <wagener_at_indot_dot_de>
* @copyright Sven Wagener
* @include 	 Funktion:_include_
*/
class database{
	var $database_types="";
	
	var $db_connect="";
	var $db_close="";
	var $db_select_db="";
	var $db_query="";
	var $db_fetch_array="";
	var $db_num_rows="";
	
	var $host;
	var $database;
	var $user;
	var $password;
	var $port;
	var $database_type;
	var $dsn;
	
	var $sql;
	
	var $con; // variable for connection id
	var $con_string; // variable for connection string
	var $query_id; // variable for query id
	
	var $errors; // variable for error messages
	var $error_count=0; // variable for counting errors
	var $error_nr;
	var $error;
	
	var $debug=false; // debug mode off
	
	private $error_sp = array();		// VM, almacena el registro con error de la ejecucion de un storeprocedure
	
	/**
	* Constructor of class - Initializes class and connects to the database
	* @param string $database_type the name of the database (ifx=Informix,msql=MiniSQL,mssql=MS SQL,mysql=MySQL,pg=Postgres SQL,sybase=Sybase)
	* @param string $host the host of the database
	* @param string $database the name of the database
	* @param string $user the name of the user for the database
	* @param string $password the passord of the user for the database
	* @desc Constructor of class - Initializes class and connects to the database.
	*
	*  You can use this shortcuts for the database type:
	*
	* 		ifx -> INFORMIX
	* 		msql -> MiniSQL
	* 		mssql -> Microsoft SQL Server
	* 		mysql -> MySQL
	*		odbc -> ODBC
	* 		pg -> Postgres SQL
	*		sybase -> Sybase
	*/
	function database($database_type,$host,$database,$user,$password,$port=false,$dsn=false){
		$database_type=strtolower($database_type);
		$this->host=$host;
		$this->database=$database;
		$this->user=$user;
		$this->password=$password;
		$this->port=$port;
		$this->dsn=$dsn;
		
		$this->database_types=array("ifx","msql","mssql","mysql","odbc","pg","sybase", "oci");
		
		// Setting database type and connect to database
		if(in_array($database_type,$this->database_types)){
			$this->database_type=$database_type;
			
			$this->db_connect=$this->database_type."_connect";
			$this->db_close=$this->database_type."_close";
			$this->db_select_db=$this->database_type."_select_db";
			
			if($database_type=="odbc"){
				$this->db_query=$this->database_type."_exec";
				$this->db_fetch_array=$this->database_type."_fetch_row";
			}else if($database_type=="oci"){
				$this->db_connect=$this->database_type."_pconnect";
				$this->db_query=$this->database_type."_execute";
				$this->db_fetch_array=$this->database_type."_fetch_array";
			}else{
				$this->db_query=$this->database_type."_query";
				$this->db_fetch_array=$this->database_type."_fetch_array";
			}
			
			
			$this->db_num_rows=$this->database_type."_num_rows";
			
			return $this->connect();
		}else{
			$this->halt("Database type not supported");
			return false;
		}
	}
	
	/**
	* This function connects the database
	* @return boolean $is_connected Returns true if connection was successful otherwise false
	* @desc This function connects to the database which is set in the constructor
	*/
	function connect(){
		// Selecting connection function and connecting
		
		if($this->con==""){
			// INFORMIX
			if($this->database_type=="ifx"){
				$this->con=call_user_func($this->db_connect,$this->database."@".$this->host,$this->user,$this->password);
			}else if($this->database_type=="mysql"){
				// With port
				if(!$this->port){
					$this->con=call_user_func($this->db_connect,$this->host.":".$this->port,$this->user,$this->password);
				}
				// Without port
				else{
					$this->con=call_user_func($this->db_connect,$this->host,$this->user,$this->password);
				}
				// mSQL
			}else if($this->database_type=="msql"){
				$this->con=call_user_func($this->db_connect,$this->host,$this->user,$this->password);
				// MS SQL Server
			}else if($this->database_type=="mssql"){
				$this->con=call_user_func($this->db_connect,$this->host,$this->user,$this->password);
				// OCI
			}else if($this->database_type=="oci"){
				$this->con=call_user_func($this->db_connect,$this->user,$this->password, $this->host, 'WE8MSWIN1252');				
				$this->query("ALTER SESSION SET NLS_NUMERIC_CHARACTERS='. '"); 
				
				// ODBC
			}else if($this->database_type=="odbc"){
				$this->con=call_user_func($this->db_connect,$this->dsn,$this->user,$this->password);
				// Postgres SQL
			}else if($this->database_type=="pg"){
				// With port
				if(!$this->port){
					$this->con=call_user_func($this->db_connect,"host=".$this->host." port=".$this->port." dbname=".$this->database." user=".$this->user." password=".$this->password);
				}
				// Without port
				else{
					$this->con=call_user_func($this->db_connect,"host=".$this->host." dbname=".$this->database." user=".$this->user." password=".$this->password);
				}
				// Sybase
			}else if($this->database_type=="sybase"){
				$this->con=call_user_func($this->db_connect,$this->host,$this->user,$this->password);
			}
			
			if(!$this->con){
				$this->halt("Wrong connection data! Can't establish connection to host.");
				return false;
			}else{
				if($this->database_type!="odbc" && $this->database_type!="oci"){
					if(!call_user_func($this->db_select_db,$this->database,$this->con)){
						$this->halt("Wrong database data! Can't select database.");
						return false;
					}else{
						return true;
					}
				}
			}
		}else{
			$this->halt("Already connected to database.");
			return false;
		}
	}
	
	/**
	* This function disconnects from the database
	* @desc This function disconnects from the database
	*/
	function disconnect(){
		if(@call_user_func($this->db_close,$this->con)){
			return true;
		}else{
			$this->halt("Not connected yet");
			return false;
		}
	}
	
	/**
	* This function starts the sql query
	* @param string $sql_statement the sql statement
	* @return boolean $successfull returns false on errors otherwise true
	* @desc This function disconnects from the database
	*/
	function query($sql_statement){
		$this->sql=$sql_statement;
		if($this->debug){
			printf("SQL statement: %s<br>",$this->sql);
		}
		if($this->database_type=="odbc"){
			// ODBC
			if(!$this->query_id=call_user_func($this->db_query,$this->con,$this->sql)){
				$this->halt("No database connection exists or invalid query");
			}else{
				if (!$this->query_id) {
					$this->halt("Invalid SQL Query");
					return false;
				}else{
					return true;
				}
			}
		}else if($this->database_type=="oci"){
			$this->query_id = oci_parse($this->con, $this->sql);
			return oci_execute($this->query_id, OCI_DEFAULT);
		}
		else
		{
			// All other databases
			if(!$this->query_id=call_user_func($this->db_query,$this->sql,$this->con)){
				$this->halt("No database connection exists or invalid query");
			}else{
				if (!$this->query_id) {
					$this->halt("Invalid SQL Query");
					return false;
				}else{
					return true;
				}
			}
		}
	}
	
	/**
	* This function returns a row of the resultset
	* @return array $row the row as array or false if there is no more row
	* @desc This function returns a row of the resultset
	*/
	function get_row(){
		if($this->database_type=="odbc"){
			// ODBC database
			if($row=call_user_func($this->db_fetch_array,$this->query_id, OCI_BOTH + OCI_RETURN_NULLS)){
				
				for ($i=1; $i<=odbc_num_fields($this->query_id); $i++) {
					$fieldname=odbc_field_name($this->query_id,$i);
					$row_array[$fieldname]=odbc_result($this->query_id,$i);
				}
				return $row_array;
			}else{
				return false;
			}
		}else{
			// All other databases
			$row=call_user_func($this->db_fetch_array,$this->query_id, MSSQL_ASSOC);
			return $row;
		}
	}
	
	/**
	* This function returns number of rows in the resultset
	* @return int $row_count the nuber of rows in the resultset
	* @desc This function returns number of rows in the resultset
	*/
	function count_rows(){
		if($this->database_type=="oci"){
			$sql = "SELECT COUNT(*) AS CANT FROM(".$this->sql.")";
			$statement = oci_parse($this->con, $sql);
			oci_execute($statement);
			$row = oci_fetch_array($statement);
			return $row['CANT'];
		}
		
		$row_count=call_user_func($this->db_num_rows,$this->query_id);
		if($row_count>=0){
			return $row_count;
		}else{
			$this->halt("Can't count rows before query was made");
			return false;
		}
	}
	/**
	* This function returns all tables of the database in an array
	* @return array $tables all tables of the database in an array
	* @desc This function returns all tables of the database in an array
	*/
	function get_tables(){
		if($this->database_type=="odbc"){
			// ODBC databases
			$tablelist=odbc_tables($this->con);
			
			for($i=0;odbc_fetch_row($tablelist);$i++) {
				$tables[$i]=odbc_result($tablelist,3);
			}
			return $tables;
		}else{
			// All other databases
			$tables = "";
			$sql="SHOW TABLES";
			$this->query_id($sql);
			for($i=0;$data=$this->get_row();$i++){
				$tables[$i]=$data['Tables_in_'.$this->database];
			}
			return $tables;
		}
	}
	
	/**
	* Prints out a error message
	* @param string $message all occurred errors as array
	* @desc Returns all occurred errors
	*/
	function halt($message){
		if($this->debug){
			printf("Database error: %s\n", $message);
			if($this->error_nr!="" && $this->error!=""){
				printf("MySQL Error: %s (%s)\n",$this->error_nr,$this->error);
			}
			die ("Session halted.");
		}
	}
	
	/**
	* Switches to debug mode
	* @param boolean $switch
	* @desc Switches to debug mode
	*/
	function debug_mode($debug=true){
		$this->debug=$debug;
	}

/////////////////////	
	///// VMC, 13-06-2008
	function count_rows_sql($sql){
		// Obtiene las rows de un sql
		if($this->database_type=="oci"){
			$sql = "SELECT COUNT(*) AS CANT FROM(".$sql.")";
			$statement = oci_parse($this->con, $sql);
			oci_execute($statement);
			$row = oci_fetch_array($statement);
			return $row['CANT'];
		}
		elseif($this->database_type=="mssql"){
			$pos = strpos(strtoupper($sql), 'ORDER BY');
			if ($pos!==false)
				$sql = substr($sql, 0, $pos);
			$sql = "SELECT COUNT(*) CANT FROM (".$sql.") A";
			$q = mssql_query($sql, $this->con);
			$row = mssql_fetch_array($q);
			return $row['CANT'];
		}
		else
			$this->halt("count_rows_sql, no soportado para ".$this->database_type);
	}
	function fetch_rows($max=0) {
		$i = 0;		
		$result_arr = array();
		while($my_row = $this->get_row()){
			$result_arr[] = $my_row;
			
			$i++;
			if ($max!=0 && $i >= $max)
				break;
		}
		return $result_arr;
	}
	function build_results($query, $max=0)	{
		/* 	Executes query, 
	 			inserts query result rows into array
	 			and returns this array
	 			$max = 0, indica todos
	 */
		$res = $this->query($query);
		$rows = $this->fetch_rows($max);
		if($this->database_type=="oci") {
			oci_free_statement($this->query_id);
			$this->query_id = false;
			//oci_close($conn);
		}
		return $rows;
	}	
	function get_fields() {
		if (!$this->query_id)
			$this->query($this->sql);	 
		
		$fields = array();
		if ($this->database_type=="mysql")
			$fieldcount = mysql_num_fields($this->query_id);
		elseif ($this->database_type=="mssql")
			$fieldcount = mssql_num_fields($this->query_id);
		else if($this->database_type=="oci")
			$fieldcount = oci_num_fields($this->query_id);
		else
			$this->halt("get_fields, no soportado para ".$this->database_type);
		
		for ($i=0; $i < $fieldcount; $i++) {
			if ($this->database_type=="mysql")
				$f = mysql_fetch_field($this->query_id, $i);
			elseif ($this->database_type=="mssql")
				$f = mssql_fetch_field($this->query_id, $i);
			elseif ($this->database_type=="oci") {
				$f = new stdClass;
				$f->name = oci_field_name($this->query_id, $i + 1);
				$tipo = oci_field_type($this->query_id, $i + 1);
				if  ($tipo=='NUMBER')
					$f->numeric = 1;
				else
					$f->numeric = 0;	// DATE, VARCHAR2, etc				
			}
			
			$f->name = strtoupper($f->name);
			$fields[] = $f;
		}
		return $fields;
	}	
	function haltVM($message) {
		// a diferencia del halt de la clase original el mensaje de error se da independiente de si esta o no activado debug
		printf("Base datos error: %s\n", $message);
		if($this->error_nr!="" && $this->error!=""){
			printf("MySQL Error: %s (%s)\n",$this->error_nr,$this->error);
		}
		die ("Session halted!!");
	}
	function EXECUTE_SP($sp, $param='') {
		$cod_usuario = session::get('COD_USUARIO');
		if ($this->database_type=="mysql")
			return $this->query("CALL ".$sp."(".$param.")");
		elseif ($this->database_type=="mssql") {
			$param2 = str_replace("'", "''", $param);	// reemplaza ' por ''
			if (!$this->query("EXECUTE spu_execute_sp 'INSERT', NULL, '$sp', '$param2', $cod_usuario"))
				return false;
				
			$cod_execute_sp = $this->GET_IDENTITY();				
			$sql = "BEGIN TRY
    					EXECUTE ".$sp." ".$param." 
						EXECUTE spu_execute_sp 'DELETE', $cod_execute_sp; 
					END TRY
					BEGIN CATCH
						declare @ERROR_NUMBER 		numeric,
								@ERROR_SEVERITY		numeric,
								@ERROR_STATE		numeric,
								@ERROR_PROCEDURE	varchar(100),
								@ERROR_LINE			numeric,
								@ERROR_MESSAGE		varchar(2000)
						select 	@ERROR_NUMBER = ERROR_NUMBER(), 
								@ERROR_SEVERITY = ERROR_SEVERITY(),
								@ERROR_STATE = ERROR_STATE(),
								@ERROR_PROCEDURE = ERROR_PROCEDURE(),
								@ERROR_LINE = ERROR_LINE(),
								@ERROR_MESSAGE = ERROR_MESSAGE()								
						EXECUTE spu_execute_sp 'UPDATE', $cod_execute_sp, '$sp', '$param2', $cod_usuario, @ERROR_NUMBER,@ERROR_SEVERITY,@ERROR_STATE,@ERROR_PROCEDURE,@ERROR_LINE,@ERROR_MESSAGE; 
					END CATCH;"; 
			if (!$this->query($sql))
				return false;
				
			$sql = "SELECT NOM_SP,
						CONVERT(TEXT,PARAM_SP)PARAM_SP,
						COD_USUARIO, 	
						ERROR_NUMBER,
						ERROR_SEVERITY,
						ERROR_STATE,
						ERROR_PROCEDURE,
						ERROR_LINE,
						CONVERT(TEXT,ERROR_MESSAGE) ERROR_MESSAGE
				FROM EXECUTE_SP
				WHERE cod_execute_sp = $cod_execute_sp";
			$this->error_sp = $this->build_results($sql);
			if (count($this->error_sp)==0) {
				return true;
			}
			else {
				if ($this->error_sp[0]['ERROR_NUMBER']=='') {
					return true;
				}
				else {
					return false;
				}
			}
			
		}
		elseif ($this->database_type=="oci"){
			$order  = array("\r\n", "\n", "\r");
			$param	= str_replace($order, "", $param);
			return $this->query("BEGIN ".$sp." (".$param."); END;");
		}	
		else
			$this->haltVM("EXECUTE_SP, no soportado para ".$this->database_type);
	}
	function GET_ERROR($fuerza_grabar_error=true) {
		$error_sp = $this->error_sp;
		// Normalmente si se llama a GET_ERROR se ejecuto un ROLLBACK por lo que la ejecucion que causo el error no quedara 
		// grabada en la tabla execute_sp.
		// Si $fuerza_grabar_error es TRUE fuerza a que se grabe el error 
		if ($fuerza_grabar_error) {
			$nom_sp = $this->error_sp[0]['NOM_SP'];
			$param_sp = $this->error_sp[0]['PARAM_SP'];
			$param2 = str_replace("'", "''", $param_sp);	// reemplaza ' por ''
			$cod_usuario = $this->error_sp[0]['COD_USUARIO'];
			$ERROR_NUMBER = $this->error_sp[0]['ERROR_NUMBER'];
			$ERROR_SEVERITY = $this->error_sp[0]['ERROR_SEVERITY'];
			$ERROR_STATE = $this->error_sp[0]['ERROR_STATE'];
			$ERROR_PROCEDURE = $this->error_sp[0]['ERROR_PROCEDURE'];
			$ERROR_LINE = $this->error_sp[0]['ERROR_LINE'];
			$ERROR_MESSAGE = str_replace("'","''", $this->error_sp[0]['ERROR_MESSAGE']);
			$sql = "EXECUTE spu_execute_sp 'INSERT', null, '$nom_sp', '$param2', $cod_usuario, $ERROR_NUMBER,$ERROR_SEVERITY,$ERROR_STATE,'$ERROR_PROCEDURE',$ERROR_LINE,'$ERROR_MESSAGE';";
			$this->BEGIN_TRANSACTION();
			$this->query($sql);
			$this->COMMIT_TRANSACTION();
			//////////////
			
		}
		$this->error_sp = $error_sp;
		return $error_sp;
	}
	function make_msg_error_bd() {
		$result_err = $this->error_sp;
		$err = 'ERROR_NUMBER: '.$result_err[0]['ERROR_NUMBER'].'\n';
		$err .= 'ERROR_SEVERITY: '.$result_err[0]['ERROR_SEVERITY'].'\n';
		$err .= 'ERROR_STATE: '.$result_err[0]['ERROR_STATE'].'\n';
		$err .= 'ERROR_PROCEDURE: '.$result_err[0]['ERROR_PROCEDURE'].'\n';
		$err .= 'ERROR_LINE: '.$result_err[0]['ERROR_LINE'].'\n';
		$err .= 'ERROR_MESSAGE: '.$result_err[0]['ERROR_MESSAGE'].'\n';
		return $err;
	}
	function BEGIN_TRANSACTION() {
		if ($this->database_type=="mssql")
			return $this->query("BEGIN TRANSACTION");
		elseif ($this->database_type=="mysql")
			return $this->query("START TRANSACTION");
		elseif ($this->database_type=="oci")
			return true;	
		else
			$this->haltVM("BEGIN_TRANSACTION, no soportado para ".$this->database_type);
	}
	function COMMIT_TRANSACTION() {
		if ($this->database_type=="mssql")
			return $this->query("COMMIT TRANSACTION");
		elseif ($this->database_type=="mysql")
			return $this->query("COMMIT");
		elseif ($this->database_type=="oci")
			return $this->query("COMMIT");
		else
			$this->haltVM("COMMIT_TRANSACTION, no soportado para ".$this->database_type);
	}
	function ROLLBACK_TRANSACTION() {
		if ($this->database_type=="mssql")
			return $this->query("ROLLBACK TRANSACTION");
		elseif ($this->database_type=="mysql")
			return $this->query("ROLLBACK");
		elseif ($this->database_type=="oci")
			return $this->query("ROLLBACK");
		else
			$this->haltVM("ROLLBACK_TRANSACTION, no soportado para ".$this->database_type);
	}
	function GET_IDENTITY($tabla='') {
		if ($this->database_type=="mssql") {
			$result = $this->build_results("select @@IDENTITY ID_COL");
			return $result[0]['ID_COL'];
		}
		elseif ($this->database_type=="mysql") {
			$result = $this->build_results("select LAST_INSERT_ID() ID_COL");
			return $result[0]['ID_COL'];
		}
		elseif ($this->database_type=="oci") {
			$result = $this->build_results("select ".$tabla."_sq.CURRVAL ID_COL from dual");
			return $result[0]['ID_COL'];
		}
		else
			$this->haltVM("GET_IDENTITY, no soportado para ".$this->database_type);
	}
	function current_date() {
		if ($this->database_type=="mssql") {
			$result_fecha = $this-> build_results('select convert(varchar(20), getdate(), 103) FECHA');
			$fecha_actual = $result_fecha[0]['FECHA'];
			return $fecha_actual;
		}
		elseif ($this->database_type=="mysql") {
			$result_fecha = $this-> build_results("select DATE_FORMAT(curdate(), '%d/%m/%Y') FECHA");
			$fecha_actual = $result_fecha[0]['FECHA'];
			return $fecha_actual;
		}
		elseif ($this->database_type=="oci") {
			$result_fecha = $this-> build_results("select to_char(sysdate, 'dd/mm/yyyy') FECHA from DUAL");
			$fecha_actual = $result_fecha[0]['FECHA'];
			return $fecha_actual;
		}
		else {
			$this->haltVM("current_date, no soportado para ".$this->database_type);
			return 'ERROR';
		}
	}
	function current_year() {
		if ($this->database_type=="mssql") {
			$result = $this-> build_results('select year(getdate()) ANO');
			return $result[0]['ANO'];
		}
		elseif ($this->database_type=="oci") {
			$result = $this-> build_results("select to_number(to_char(sysdate, 'yyyy')) ANO from DUAL");
			return $result[0]['ANO'];
		}
		else {
			$this->haltVM("current_date, no soportado para ".$this->database_type);
			return 'ERROR';
		}
	}
	function current_month() {
		if ($this->database_type=="mssql") {
			$result = $this-> build_results('select month(getdate()) MES');
			return $result[0]['MES'];
		}
		elseif ($this->database_type=="oci") {
			$result = $this-> build_results("select to_number(to_char(sysdate, 'mm')) MES from DUAL");
			return $result[0]['MES'];
		}
		else {
			$this->haltVM("current_date, no soportado para ".$this->database_type);
			return 'ERROR';
		}
	}
	function current_date_time() {
		if ($this->database_type=="mssql") {
			$result_fecha = $this-> build_results("select convert(varchar(20), getdate(), 103) + ' ' + convert(varchar(20), getdate(), 108) FECHA");
			$fecha_actual = $result_fecha[0]['FECHA'];
			return $fecha_actual;
		}
		elseif ($this->database_type=="mysql") {
			$result_fecha = $this-> build_results("select CONCAT(DATE_FORMAT(curdate(), '%d/%m/%Y'), ' ', curtime()) FECHA");
			$fecha_actual = $result_fecha[0]['FECHA'];
			return $fecha_actual;
		}
		elseif ($this->database_type=="oci") {
			$result_fecha = $this-> build_results("select to_char(sysdate, 'dd/mm/yyyy hh24:mi:ss') FECHA from DUAL");
			$fecha_actual = $result_fecha[0]['FECHA'];
			return $fecha_actual;
		}
		else {
			$this->haltVM("current_date_time, no soportado para ".$this->database_type);
			return 'ERROR';
		}
	}
	function current_time() {
		if ($this->database_type=="mssql") {
			$result_fecha = $this-> build_results("select convert(varchar(20), getdate(), 108) HORA");
			$fecha_actual = $result_fecha[0]['HORA'];
			return $fecha_actual;
		}
		elseif ($this->database_type=="mysql") {
			$result_fecha = $this-> build_results("select curtime() HORA");
			$fecha_actual = $result_fecha[0]['HORA'];
			return $fecha_actual;
		}
		elseif ($this->database_type=="oci") {
			$result_fecha = $this-> build_results("select to_char(sysdate, 'hh24:mi:ss') HORA from DUAL");
			$fecha_actual = $result_fecha[0]['HORA'];
			return $fecha_actual;
		}
		else {
			$this->haltVM("current_date_time, no soportado para ".$this->database_type);
			return 'ERROR';
		}
	}
	function get_parametro($cod_parametro) {
		$sql = "select VALOR from PARAMETRO where COD_PARAMETRO = ".$cod_parametro;
		$result = $this->build_results($sql);
		return $result[0]['VALOR'];
	}
	function data_seek($row) {
		if ($this->database_type=="mssql") {
			if ($this->count_rows() > $row)
				return mssql_data_seek($this->query_id, $row);
			
		}
		elseif ($this->database_type=="oci") {
			// ************falta
			return true;
		}
		else {
			$this->haltVM("data_seek, no soportado para ".$this->database_type);
			return 'ERROR';
		}
	}
}
?>