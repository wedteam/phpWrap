<?php
define('_PHPWRAP_DIR_', __DIR__ . '/classes/core/');
function __autoload($class){
    $class = strtolower(preg_replace("/([A-Z])/", ".$1", $class));
    require_once _PHPWRAP_DIR_ . $class . '.php';
}

echo stringH::substr('hello', 2, 4);
?>