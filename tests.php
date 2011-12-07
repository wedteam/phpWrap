<?php
require('classes/core/core.php');
/*function mirror(&$a){
	$b = array();
	foreach($a as $k => $v){
		$b[$k] = &$a[$k];
	}
	return $b;
}
$aa = array(1,2,3);
$bb = mirror($aa);
$bb = &$bb[2];
$bb=4;
print_r($aa);
exit;*/

/*function t(&$arr){
	$r = array();
	$r[] = &$arr[0];
	return $r;
}
$a = array(1,2,3);
$c = t($a);
$c[0] = 4;
print_r($c);
print_r($a);
exit;*/

print_r(C("abcdef")->substr(2,4));
exit;

$arr = array(
	'a'	=> array(
		'b' => 1,
		'c' => array(
			'b' => array(2)
		),
		'd' => array(
			'e' => 5
		),
	),
	'b' => 3,
);
$r = W($arr)->query('a b')->each(function($a){
	return $a = array('ok');	
});
//$r->core[0] = -333;
//print_r($r);
print_r($arr);
exit;

/*$arr = array(array(0,1,3), array(0,2,4));
$x = W($arr)->item(1);
$x->core[0] = -1;
print_r($x);
print_r($arr);
exit;*/

$str = array('abced','fffff');
$x = W($str)->substr(0,3);
print_r($x);
print_r($str);
exit;

$d = microtime(true);
$x = 0;
for($i = 0; $i < 500; $i++){
 $x += W(array('qwertyuiop','abcedf'))->substr(2,6)->md5()->substr(-4)->strlen()->core[0];
 //call_user_func_array('strlen', array(call_user_func_array('stringH::mysubstr',array(md5(stringH::mysubstr('qwertyuiop', 2, 6)),-4))));;
}
echo $x;
echo microtime(true) - $d;

$arr = array(1,2,3);
$aw = W($arr)->item(2,4);
print_r($aw->as_array());

print_r(W(array(array(1,2,3),array(4,5,6)))->item(1)->core);

print_r(W(array(array(1,2,3),array(4,5,6)))->item(1)->each(function($v){
	return $v+1;
})->core);

$arrr = array(1,2,3);
$o = &$arrr[2];
$o = 5;
print_r($arrr);