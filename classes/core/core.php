<?php 
class Wrap{
	public $core;
	
	protected $func_queue = array();
	
	protected static $helpers = array(
		'string' => 'StringH',
	);

	public static function auto_load($class){
		$class = strtolower(preg_replace("/([A-Z])$/", ".$1", $class));
		try{
			require_once $class . '.php';
			return true;
		} catch(Exception $e){
			return false;
		}
		
	}

	protected static function gettype($obj){
		if(gettype($obj) != 'object'){
			return gettype($obj);
		}else{
			return strtolower(get_class($obj));
		}
	}

	function __construct(&$core){
		$this->core = &$core;
	}

	function __call($func, $args){
		/*
			"boolean"
			"integer"
			"double" 
			"string"
			"array"
			"mixed"
		 */
		if(strpos($func, 'as_') === 0){
			$ret = $this->core;
			$type = gettype($ret);
			if($func != 'as_mixed' && $type != substr($func, 3)){
				if($func == 'as_string'){
					return ''.$ret;
				}else if($func == 'as_integer'){
					return intval($ret);
				}else if($func == 'as_double'){
					return doubleval($ret);
				}else if($func == 'as_boolean'){
					return !!$ret;
				}
				throw new Exception('type convert error!');
			}
			return $ret;
		}else{
			array_unshift($args, null);
			$args[0] = &$this->core;
			if(is_array($args[0])  
				&& !method_exists(self::$helpers['array'], $func)
				&& !ArrayH::is_assoc($args[0])){ //区分数组？ query? W($data).query('>data')...
				//不是 array_ 系列方法时，将array作为集合操作
				foreach($args[0] as $key => $core){
					$helper = gettype($args[0][$key]);
					if(method_exists(self::$helpers[$helper], $func)){
						$func = array($helper,$func); 
					}
					$args[0][$key] = call_user_func_array($func, array_merge(array($args[0][$key]),array_slice($args,1)));
				}
			}else{
				$helper = gettype($args[0]); 
				if(method_exists(self::$helpers[$helper], $func)){
					$func = array($helper, $func);
				}
				$args[0] = call_user_func_array($func, $args);
			}
			return $this;
		}
	}
}

function W($mixed){
	return new Wrap($mixed);
}

spl_autoload_register(array('Wrap', 'auto_load'));