<?php 
/**
 * Wrap是一个包装对象，它可以“修饰”php原生对象，Wrap修饰后的php原生对象有几个作用
 * 首先是能够支持Helper链式调用和批量操作
 * 所谓Helper链式调用是指能用Helper对象上的范式方法作为Wrap对象的方法来调用，并链式返回Wrap后的结果
 * 批量操作是指Wrap对象可以包装一个数组，对数组上的每一个元素应用Helper对象的方法
 * 其次，Helper可以将php原生函数符合Helper规范的方法也代理成链式调用方法，如substr、strlen等等
 * Helper规范和Wrap的概念参考著名的JavaScript框架 QWrap
 * 
 * @package    PhpWrap
 * @category   Core
 * @author     akira.cn@gmail.com
 * @copyright  (c) 2011 WED Team
 * @license    http://qwrap.com
 */
class Wrap{
	/**
	 * 包装方法的核心对象，用引用方式关联非数组的宿主对象，用mirror方式关联数组
	 *
	 * @var mixed $core
	 */
	public $core;
	
	/**
	 *
	 * 配置映射表，出于效率考虑，只支持一个类型对应一个Helper，暂不支持用户自定义object细分类型对应Helper
	 * @var Array $helper Helper
	 */
	protected static $helpers = array(
		'string' => 'StringH',
		'array'	=> 'ArrayH',
	);
	
	/**
	 * Wrap的自动类加载器
	 *
	 * @param String $class 要加载的类名
	 * @return Boolean 是否加载成功
	 */
	public static function auto_load($class){
		$class = strtolower(preg_replace("/([A-Z])$/", ".$1", $class));
		try{
			require_once $class . '.php';
			return true;
		} catch(Exception $e){
			return false;
		}
		
	}
	
	/**
	 * 取出或设置Wrap中的元素中Key-Value内容，对于非数组，会抛出index异常
	 *
	 * @param String $key 
	 * @param mixed $value 缺省是setter，传入该参数则作为setter
	 */
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
	
	/**
	 * 构造一个新的Wrap，对于Helper有返回值的function调用后会构造新的Wrap，无返回值的function则会直接返回当前Wrap
	 *
	 * @param mixedRef $core 代理的数据对象
	 */
	function __construct(&$core){
		$this->core = &$core;
	}
	
	/**
	 * 类型转为数组
	 */
	public function as_array(){
		if(is_array($this->core))
			return $this->core;
		else
			throw new Exception('type convert error');
	}

	/**
	 * 类型转为字符串
	 */
	public function as_string(){
		return $this->__toString();
	}

	/**
	 * 类型转为整数
	 */
	public function as_int(){
		return intval($this->core);
	}
	
	/**
	 * 类型转为浮点数
	 */
	public function as_float(){
		return floatval($this->core);
	}

	/**
	 * 类型转为布尔
	 */
	public function as_bool(){
		return !!$this->core;
	}
	
	/**
	 * 任意类型，直接返回core
	 */
	public function as_mixed(){
		return $this->core;
	}

	/**
	 * 用魔术方法来代理Helper方法
	 *
	 * @param String $func 要代理的方法名
	 * @param Array $args 方法调用的参数
	 */
	function __call($func, $args){
		// 将$this->core作为第一个参数
		array_unshift($args, null);
		$args[0] = &$this->core;
		
		if(is_array($args[0])  
			&& !method_exists(self::$helpers['array'], $func))
		{ 
			//如果core是数组并且不是调用ArrayH上的方法
			//将数组作为集合来进行批量操作
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
			//如果存在对应的Helper并且Helper上有此方法
			if(method_exists(self::$helpers[$helper], $func)){
				$func = array(self::$helpers[$helper], $func);
			}
			//否则，尝试调用php系统自带的方法
			$ret = call_user_func_array($func, $args);
			if(isset($ret)){	//如果有返回值，用返回值的Wrapper代替$this
				return new Wrap($ret);
			}
		}
		return $this;
	}
}

/**
 * 如果是常量，用这个函数生成代理，因为默认代理必须是core的引用，直接调用常量无法引用
 *
 * @param const $constant
 * @return Wrap
 */
function C($constant){
	return W($constant);
}

/**
 * 如果是变量，产生包装的Wrap对象
 * 
 * @param mixedRef $mixed
 */
function W(&$mixed){
	//如果已经是Wrap对象，那么复制它
	if($mixed instanceof Wrap){
		$mixed = &$mixed->core;
	}
	//如果是数组，传数组拷贝引用
	if(is_array($mixed)){
		$rr = ArrayH::mirror($mixed);
	}else{
		$rr = &$mixed;	//否则直接传变量引用
	}
	return new Wrap($rr);
}

/**
 * 注册类自动加载器
 */
spl_autoload_register(array('Wrap', 'auto_load'));