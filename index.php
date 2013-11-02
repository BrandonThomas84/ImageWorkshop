<?php /* FILEVERSION: v1.0.1b */ ?>
<?php

//including the functions file that will include everything else
require_once('include/functions.php');

//dumping database backup if it has been requested
if(isset($_GET['mysqldump'])){
	$dump = new mySqlDump;
	$dump->start();
}

//image loader class contains page class that loads header and footer content
$image = new imageLoader();

?>

