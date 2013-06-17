<?php
	$hostname_binu_devilsdictionary = getenv('DB_HOST');
	$database_binu_devilsdictionary = getenv('DB_NAME');
	$username_binu_devilsdictionary = getenv('DB_USER');
	$password_binu_devilsdictionary = getenv('DB_PASS');
	
	$binu_devilsdictionary = mysql_pconnect($hostname_binu_devilsdictionary, $username_binu_devilsdictionary, $password_binu_devilsdictionary) or trigger_error(mysql_error(),E_USER_ERROR); 
	
?>