<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/phpreport/interface.ReportData.php");

class MySQLRD_biggi implements IReportData {
	
	private $dbhandle;
	private $Result;
	private $RowData;
	private $Command;
	
	private $sp;
	private $param;
	
	public function __construct($command, $sp, $param){
		$this->Command = $command;
		$this->sp = $sp;
		$this->param = $param;
	}

	/**
	 * start data retriving again from the beginning
	 */
	public function reset(){
		$this->dbhandle = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		if ($this->sp != '') {
			$this->Result = $this->dbhandle->query("execute $this->sp $this->param");
		}
		else	
			$this->Result = $this->dbhandle->query($this->Command);
	}

	public function getNextRow() {
		return $this->RowData;
	}
	
	public function hasMoreRow() {
		return ($this->RowData = $this->dbhandle->get_row());
	}

}
?>