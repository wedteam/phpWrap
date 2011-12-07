<?php
require_once('helper.h.php');

/**
 * 数组的Helper方法
 *
 * @package    PhpWrap
 * @category   Core
 * @author     akira.cn@gmail.com
 * @copyright  (c) 2011 WED Team
 * @license    http://qwrap.com
 */
class ArrayH extends HelperH {
	/**
	 * 判断数组是一个传统的list还是一个关联数组
	 * 这个方法来自 Kohana 3.2 
	 * 
	 * @param ArrayRef $array
	 * @return boolean
	 */
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
	 *	
     * @param ArrayRef $array
	 * @param String $selector
	 */
	public static function query(array &$array, $selector){
		$selector = preg_replace('/([#>])/',' ${1} ',$selector);
		$selector = preg_replace('/\s*(\+)\s*/', '${1}', $selector);
		$selector = explode(' ', $selector);
		
		$ret = array($array);
		$pat = '';

		foreach($selector as $token){
			//对选择符从左到右依次进行处理
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
	/**
	 * 寻找兄弟节点
	 *
	 * @param ArrayRef  $array
	 * @param String $key
	 * @param String $brother 兄弟节点
	 * @param Boolean $find_one 只找一个
	 * @param Boolean $child_only 只找直系儿子，不递归查找
	 * @return Array 符合条件的结果数组
	 */
	private static function _searchBrotherKey(array &$array, $key, $brother, $find_one = false, $child_olny = false){
		$key_match = null; $brother_match = null;
		$ret = array();
		//如果找到了，返回
		if(array_key_exists($key, $array) && array_key_exists($brother, $array)){
			$ret[] = &$array[$key];
		}
		else if(!$child_only){
			//否则如果不只找儿子的话，需要递归往下查找
			foreach($array as $k => &$v){
				if(is_array($v)){
					$ret = array_merge($ret, self::_searchBrotherKey($v, $key, $brother, $find_one));
				}
				if($find_one && count($ret) > 0) break; //如果只找一个，找到就返回
			}
		}
		return $ret;
	}
	
	/**
	 * 寻找子孙节点
	 *
	 * @param ArrayRef  $array
	 * @param String $key
	 * @param Boolean $find_one 只找一个
	 * @param Boolean $child_only 只找直系儿子，不递归查找
	 * @return Array 符合条件的结果数组
	 */
	private static function _searchKey(array &$array, $key, $find_one = false, $child_only = false){
		$ret = array();

		if(array_key_exists($key, $array)){
			//如果找到了一个，不需要再找本系谱子孙
			$ret[] = &$array[$key];
		}
		else if(!$child_only){
			//否则如果不只找儿子的话，要继续往下查
			foreach($array as $k => &$v){
				if(is_array($v)){
					$ret = array_merge($ret, self::_searchKey($v, $key, $find_one));
				}
				if($find_one && count($ret) > 0) break; //如果只找一个，找到就返回
			}
		}
		return $ret;
	}
	
	/**
	 * 生成数组镜像，一个数组镜像的每一个key都是另一个数组的对应key的引用
	 * 用来生成重要的数组拷贝引用，来关联core和Wrap
	 *
	 * @param ArrayRef $array
	 * @return Array 镜像数组
	 */
	public static function mirror(array &$arr){
		$ret = array();
		foreach($arr as $k => $v){
			$ret[$k] = &$arr[$k];
		}
		return $ret;
	}	
	
	/**
	 * 遍历迭代数组，对每个元素执行定义的方法
	 *
	 * @param ArrayRef $array
	 * @param Closure $callback
	 */
	public static function each(array &$array, $callback){
		return array_map(
			$callback, $array
		);
	}
}