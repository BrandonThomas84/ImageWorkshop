<?php /* FILEVERSION: v1.0.1b */ ?>
<?php

//setting class autoloader
function __autoload($classname){
	include __DIR__ . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . $classname . '.class.php';
}

//linking database connection settings
require_once('settings.inc.php');

?>