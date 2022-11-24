<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

class static_num_miles extends static_num {
	function static_num_miles($field) {
		parent::static_num($field, 0);
	}
	function draw_no_entrable($dato, $record) {
		if ($dato!='')
			$dato = number_format(round($dato/1000, 0), 0, ',', '.');

		return $dato;		
	}
}
?>