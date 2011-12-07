<?php
require('classes/core/core.php');

$d = microtime(true);
$x = 0;
for($i = 0; $i < 500; $i++){
 $x += W(array('qwertyuiop'))->substr(2,6)->md5()->substr(-4)->strlen()->core[0];
 //call_user_func_array('strlen', array(call_user_func_array('stringH::mysubstr',array(md5(stringH::mysubstr('qwertyuiop', 2, 6)),-4))));;
}
echo $x;
echo microtime(true) - $d;

$arr = array('a'=>1);
print_r( array_keys($arr));