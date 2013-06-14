<?php
require_once('php_helper_class_library/class.biNu.php');
require_once("inc/config.php");
require_once("inc/functions.php");

// Assign application configuration variables during constructor
$app_config = array (
	'dev_id' => 17768,								// Your DevCentral developer ID goes here
	'app_id' => 4699,								// Your DevCentral application ID goes here
	'app_name' => 'Murphy\s Laws',				// Your application name goes here
	'app_home' => 'http://binu-murphyslaws.azurewebsites.net/',	// Publically accessible URI
	'ttl' => 1										// Your page "time to live" parameter here
);

try {
	// Construct biNu object
	$binu_app = new biNu_app($app_config);
	
	$binu_app->time_to_live = 60;
	
	
	//Define Styles
	$binu_app->add_style( array('name' => 'header', 'color' => '#1540eb', 'size' => '20') );
	$binu_app->add_style( array('name' => 'intro', 'color' => '#FF0000') );
	$binu_app->add_style( array('name' => 'body_text', 'color' => '#0000FF') );
	
	
	
	
	 
	
	
if (isset($_GET['binu_transaction_res'])&&($_GET['binu_transaction_res']<>0)) 
	{
		header("Location: ".$_SERVER['SERVER_NAME'].dirname($_SERVER['PHP_SELF'])."error.php?binu_transaction_res=".$_GET['binu_transaction_res'] );
	}

	//We want to pick 10 quotqtions at a time
	$maxRows_quoteRecordset = 10;
	
	 //The first page is page 0
	$pageNum_quoteRecordset = 0; 
	if (isset($_GET['pageNum_quoteRecordset'])) {
	  $pageNum_quoteRecordset = $_GET['pageNum_quoteRecordset'];
	}
	$startRow_quoteRecordset = $pageNum_quoteRecordset * $maxRows_quoteRecordset;

	mysql_select_db($database_binu_murphyslaws, $binu_murphyslaws);
	$query_quoteRecordset = "SELECT * FROM quote ORDER BY quote_id ASC";
	$query_limit_quoteRecordset = sprintf("%s LIMIT %d, %d", $query_quoteRecordset, $startRow_quoteRecordset, $maxRows_quoteRecordset);
	$quoteRecordset = mysql_query($query_limit_quoteRecordset, $binu_murphyslaws) or die(mysql_error());
	$row_quoteRecordset = mysql_fetch_assoc($quoteRecordset);

	if (isset($_GET['totalRows_quoteRecordset'])) {
	  $totalRows_quoteRecordset = $_GET['totalRows_quoteRecordset'];
	} else {
	  $all_quoteRecordset = mysql_query($query_quoteRecordset);
	  $totalRows_quoteRecordset = mysql_num_rows($all_quoteRecordset);
	}
	$totalPages_quoteRecordset = ceil($totalRows_quoteRecordset/$maxRows_quoteRecordset)-1;$maxRows_quoteRecordset = 6;
	$pageNum_quoteRecordset = 0;
	if (isset($_GET['pageNum_quoteRecordset'])) {
	  $pageNum_quoteRecordset = $_GET['pageNum_quoteRecordset'];
	}
	
	$startRow_quoteRecordset = $pageNum_quoteRecordset * $maxRows_quoteRecordset;
	$quoteCounter = $startRow_quoteRecordset + 1;
	$colname_quoteRecordset = "-1";
	if (isset($_GET['id'])) {
	  $colname_quoteRecordset = $_GET['id'];
	}
	mysql_select_db($database_binu_murphyslaws, $binu_murphyslaws);
	$query_quoteRecordset = sprintf("SELECT * FROM quote WHERE category_id = %s ORDER BY quote_id ASC", GetSQLValueString($colname_quoteRecordset, "int"));
	$query_limit_quoteRecordset = sprintf("%s LIMIT %d, %d", $query_quoteRecordset, $startRow_quoteRecordset, $maxRows_quoteRecordset);
	$quoteRecordset = mysql_query($query_limit_quoteRecordset, $binu_murphyslaws) or die(mysql_error());
	$row_quoteRecordset = mysql_fetch_assoc($quoteRecordset);
	
	if (isset($_GET['totalRows_quoteRecordset'])) {
	  $totalRows_quoteRecordset = $_GET['totalRows_quoteRecordset'];
	} else {
	  $all_quoteRecordset = mysql_query($query_quoteRecordset);
	  $totalRows_quoteRecordset = mysql_num_rows($all_quoteRecordset);
	}
	$totalPages_quoteRecordset = ceil($totalRows_quoteRecordset/$maxRows_quoteRecordset)-1;
	
	$colname_categoryNameRecordset = "-1";
	if (isset($_GET['id'])) {
	  $colname_categoryNameRecordset = $_GET['id'];
	}
	mysql_select_db($database_binu_murphyslaws, $binu_murphyslaws);
	$query_categoryNameRecordset = sprintf("SELECT * FROM category WHERE category_id = %s", GetSQLValueString($colname_categoryNameRecordset, "int"));
	$categoryNameRecordset = mysql_query($query_categoryNameRecordset, $binu_murphyslaws) or die(mysql_error());
	$row_categoryNameRecordset = mysql_fetch_assoc($categoryNameRecordset);
	$totalRows_categoryNameRecordset = mysql_num_rows($categoryNameRecordset);
	
	
	$binu_app->add_text("Murphy's ".$row_categoryNameRecordset['category'],'intro');
	
	
 do {
        $binu_app->add_text("Law ".$quoteCounter.": ".$row_quoteRecordset['quote'],'body_text');
		$quoteCounter++;
		
	 } while ($row_quoteRecordset = mysql_fetch_assoc($quoteRecordset));
		
      
	$binu_app->add_text('Options:', 'intro');

	/* Process menu options */
	
	$binu_app->add_link("index.php", "Category Listing", "intro");
	$next_page = $_GET['pageNum_quoteRecordset'] + 1;
	if ($totalPages_quoteRecordset >= ($next_page+1)){
		$binu_app->add_link("quotes.php?pageNum_quoteRecordset=".$next_page."&amp;id=".$_GET['id'], "See 10 more ".$row_categoryNameRecordset['category'], "intro");
	}
	
	$binu_app->add_link($binu_app->application_URL , "Home", "intro");
	$binu_app->add_link("http://apps.binu.net/apps/mybinu/index.php" , "biNu Home", "intro");

	/* Show biNu page */
	$binu_app->generate_BML();

} catch (Exception $e) {
	app_error('Error: '.$e->getMessage());
}


?>
