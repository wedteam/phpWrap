<?php
require_once('helper.h.php');
class StringH extends HelperH {
	function __construct(){
		parent::__construct();
	}

	/**
	* 返回字符串长度
	* string $str 输入字符串
	* bool $is_utf8 是否非ascii编码的utf-8字符串
	**/
	public static function strlen($str, $is_utf8 = false){
		if($is_utf8){
			$str = utf8_decode($str);
		}
		return (int) strlen($str);
	}

	/**
	* 返回ascii字符串的子串
	* string $str 输入字符串
	* int $start 返回子串的开始位置
	* int $length 返回子串的长度
	**/
	public static function substr($str, $start, $length=''){
		if($length === ''){
			return substr((string) $str, (int) $start);
		}
		return substr((string) $str, (int) $start, (int) $length);
	}	
}