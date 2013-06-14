<?php
	
	$hostname_binu_murphyslaws = getenv('DB_HOST');
	$database_binu_murphyslaws = getenv('DB_NAME');
	$username_binu_murphyslaws = getenv('DB_USER');
	$password_binu_murphyslaws = getenv('DB_PASS');
	
	$binu_murphyslaws = mysql_pconnect($hostname_binu_murphyslaws, $username_binu_murphyslaws, $password_binu_murphyslaws) or trigger_error(mysql_error(),E_USER_ERROR); 
	
?>