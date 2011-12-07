<?php 
class Wrap{
	public $core;

	protected static $helpers = array(
		'string' => 'StringH',
		'array'	=> 'ArrayH',
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
	
	public function item($key, $value = null){
		if(isset($value)){
			$this->core[$key] = $value;
			return $this;
		}
		else{
			return new Wrap($this->core[$key]);
		}
	}

	public function __toString(){
		return json_encode($this->core);
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
				&& !method_exists(self::$helpers['array'], $func)){ //区分数组？ query? W($data).query('>data')...
				//不是 array_ 系列方法时，将array作为集合操作
				$_r = array();
				foreach($args[0] as &$core){
					$helper = gettype($core);
					if(method_exists(self::$helpers[$helper], $func)){
						$func = array(self::$helpers[$helper],$func); 
					}
					$ret = call_user_func_array($func, array_merge(array($core),array_slice($args,1)));
					if(isset($ret))
						$_r[] = $ret;
				}
				return new Wrap($_r);
			}else{
				$helper = gettype($args[0]); 
				if(method_exists(self::$helpers[$helper], $func)){
					$func = array(self::$helpers[$helper], $func);
				}
				$ret = call_user_func_array($func, $args);
				if(isset($ret)){
					return new Wrap($ret);
				}
			}
			return $this;
		}
	}
}

function C($constant){
	return W($constant);
}
function W(&$mixed){
	if($mixed instanceof Wrap){
		$mixed = &$mixed->core;
	}
	if(is_array($mixed)){
		$rr = ArrayH::mirror($mixed); //如果是数组，传数组拷贝引用
	}else{
		$rr = &$mixed;	//否则传引用
	}
	return new Wrap($rr);
}

spl_autoload_register(array('Wrap', 'auto_load'));