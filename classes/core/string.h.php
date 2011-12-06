<?php
require_once('base.h.php');
class stringH extends baseH {
	function __construct(){
		parent::__construct();
	}

	private static function transArgs($args){
		$str = array_shift($args);
		self::$str = strval($str);
		self::$args = join(',', $args);
	}
	/**
	* 返回字符串的子串
	* string $str 输入字符串
	* int $start 返回子串的开始位置
	* int $length 返回子串的长度
	**/
	public static function substr($str, $start, $length=''){		
		return substr(strval($str), $start, $length);
	}
}