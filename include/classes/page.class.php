<?php /* FILEVERSION: v1.0.1b */ ?>
<?php

class page{
	public function buildPage($content){

		//include the head information
		require_once('include/head.php');

		//echoing the page content passed on instantiation
		echo $content;

		//include the head information
		require_once('include/foot.php');
	}
}

?>