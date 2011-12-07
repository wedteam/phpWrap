<?php
require_once('helper.h.php');
class StringH extends HelperH {
	static function mysubstr($self, $start, $end=0){
		return substr($self, $start, $end);
	}
}