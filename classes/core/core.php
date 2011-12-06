<?php 

class Wrap{
	public $core;

	protected static $helpers = array(
		'array' => 'ArrayH',
		'string' => 'StringH',
	);
	
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
		if(substr($func, 0,3) == 'as_'){ //如果是as开头的对象，那么拆封Wrap
			if(self::gettype($this->core) == substr($func,3) 
				or 'mixed' == substr($func,3)){ //as_array, as_string, as_int etc...
				return $this->core;
			}else{
				throw new Exception('type convert error!');
			}
		}

		else{
			$helpers = self::$helpers;
			$type = self::gettype($this->core);
			$helper = $helpers[$type];
			$h_args = $args;
			
			if($type == 'array' && //如果是数组
				!ArrayH::is_assoc($this->core)	&& //并且不是关联数组
				!in_array($func, array('each','map','filter'))){	
				//如果类型是Array并且调用的不是each、map、filter等数组批量方法
				//集合操作
				$ret = array();
				array_unshift($h_args, null);
				foreach($this->core as $key=>$value){
					$h_args[0] = &$this->core[$key];
					$helper = $helpers[self::gettype($value)];

					if(isset($helper) && method_exists($helper,$func)){
						$_r = call_user_func_array(array($helper,$func), $h_args);
					}else if(method_exists($value,$func) || method_exists($value,'__call')){
						$_r = call_user_func_array(array($value,$func), $args);
					}else{
						$_r = call_user_func_array($func, $h_args);
					}
					if(!isset($_r)){
						$_r = &$this->core[$key];
					}
					$ret[] = $_r;
				}
				return new Wrap($ret);
			}
			else{
				array_unshift($h_args, null);
				$h_args[0] = &$this->core;

				if(isset($helper) && method_exists($helper,$func)){	
					//如果helper方法存在的话，调用helper方法
					$ret = call_user_func_array(array($helper,$func), $h_args);	 
				}else if(method_exists($h_args[0],$func) || method_exists($h_args[0],'__call')){ 
					//否则调用object方法
					$ret = call_user_func_array(array($h_args[0],$func), $args);	
				}else{																			
					//再不然调用全局函数
					$ret = call_user_func_array($func, $h_args);
				}
				if(!isset($ret))	//如果Helper方法没有返回值，返回Wrap自身，否则，返回实际返回值的Wrap或实际返回值自身
					return $this;

				$ret_helper =  $helpers[self::gettype($ret)];
				if(isset($ret_helper)){	//如果返回值的helper存在
					return new Wrap($ret);	//返回Wrap过的对象
				}else{
					return $ret;	//否则返回返回值自身
				}
			}
		}
	}
}

function W(&$mixed){
	return new Wrap($mixed);
}