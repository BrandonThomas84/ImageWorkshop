<?php
require_once('settings.inc.php');
class createEnvironment {
	public function  createEnvironment(){
		
		//recursive code linking
		$link = mysql_connect(_DB_SERVER_,_DB_USER_,_DB_PASSWD_); 
			if (!$link){die("Could not connect to MySQL: " . mysql_error());}


		$table = "CREATE TABLE `1516920_ps`.`imageworkshop` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `name` varchar(255) NOT NULL,
			  `lx` int(11) DEFAULT NULL,
			  `ly` int(11) DEFAULT NULL,
			  `rx` int(11) DEFAULT NULL,
			  `ry` int(11) DEFAULT NULL,
			  PRIMARY KEY (`id`),
			  UNIQUE KEY `id_UNIQUE` (`id`)
			) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=latin1;";
		
		$query = mysql_query($database);
		if(!$query){ echo mysql_error();}
		$query = mysql_query($table);
		if(!$query){ echo mysql_error();}
		
	}
}
 new createEnvironment;
?>