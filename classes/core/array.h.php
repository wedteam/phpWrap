<?php
require_once('helper.h.php');
class ArrayH extends HelperH {
	public static function is_assoc(array &$array)
	{
		// Keys of the array
		$keys = array_keys($array);

		// If the array keys of the keys match the keys, then the array must
		// not be associative (e.g. the keys array looked like {0:0, 1:1...}).
		return array_keys($keys) !== $keys;
	}

	/**
	 * 根据selector查询数组，并返回找到的查询结果
	 *
	 * 支持的查询格式有
	 *
	 * "a b" 查找a属性的子孙中包含b属性
	 * "#a b" 查找唯一的a属性的子孙中包含所有b属性
	 * "a>b" 查找a属性下的b属性儿子
	 * "a+b" 查找兄弟，即同时有a、b属性，取b
	 * "a b[value=1]" 查找并过滤value=1的结果
	 */
	public static function query(array &$array, $selector, $byRef = false){
		$selector = preg_replace('/([#>])/',' ${1} ',$selector);
		$selector = preg_replace('/\s*(\+)\s*/', '${1}', $selector);
		$selector = explode(' ', $selector);
		
		$ret = array($array);
		$pat = '';

		foreach($selector as $token){
			if($token == '') continue;

			if(in_array($token, array('#','>'))){
				$pat = $token;
			}
			else{
				$_r = array(); //temp
				foreach($ret as &$arr){
					if(strpos($token, '+') !== false){
						$k_b = explode('+', $token);
						$_r += self::_searchBrotherKey($arr, $k_b[1], $k_b[0], $pat == '#', $pat == '>');
					}
					else{
						$_r += self::_searchKey($arr, $token, $pat == '#', $pat == '>');
					}
				}
				$ret = $_r;
				$pat = '';
			}
		}
		
		return $ret;
	}
	private static function _searchBrotherKey(array &$array, $key, $brother, $find_one = false, $child_olny = false){
		$key_match = null; $brother_match = null;
		$ret = array();
		if($child_only){
			if(array_key_exists($key, $array) && array_key_exists($brother, $array)){
				$ret[] = &$array[$key];
			}
		}
		else{
			foreach($array as $k => &$v){
				if($key === $k){
					$key_match = &$v;
				}else if($brother === $k){
					$brother_match = $v;
				}
				if(isset($key_match) && isset($brother_match)){
					$ret[] = &$key_match;
				}else if(is_array($v)){
					$ret = array_merge($ret, self::_searchBrotherKey($v, $key, $brother, $find_one));
				}
				if($find_one && count($ret) > 0) return $ret; //如果只找一个，找到就返回
			}
		}
		return $ret;
	}

	private static function _searchKey(array &$array, $key, $find_one = false, $child_only = false){
		$ret = array();
		if($child_only){
			if(array_key_exists($key, $array)){
				$ret[] = &$array[$key];
			}
		}else{
			foreach($array as $k => &$v){
				if($key === $k){
					$ret[] = &$v;
				}else if(is_array($v)){
					$ret = array_merge($ret, self::_searchKey($v, $key, $find_one));
				}
				if($find_one&& count($ret) > 0) return $ret; //如果只找一个，找到就返回
			}
		}
		return $ret;
	}

	public static function mirror(array &$arr){
		$ret = array();
		foreach($arr as $k => $v){
			$ret[$k] = &$arr[$k];
		}
		return $ret;
	}	
	
	public static function each(array &$array, $callback){
		return array_map(
			$callback, $array
		);
	}
}