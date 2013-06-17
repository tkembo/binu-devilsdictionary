<?php
require_once('php_helper_class_library/class.biNu.php');
require_once("inc/config.php");
require_once("inc/functions.php");

// Assign application configuration variables during constructor
$app_config = array (
	'dev_id' => 17768,								// Your DevCentral developer ID goes here
	'app_id' => 5733,								// Your DevCentral application ID goes here
	'app_name' => 'Devil\'s Dictionary',				// Your application name goes here
	'app_home' => 'binu-devilsdictionary.azurewebsites.ne/',	// Publically accessible URI
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
	
	//Pick one featured recordset
$maxRows_wordsRecordset = 10;
$pageNum_wordsRecordset = 0;
if (isset($_GET['pageNum_wordsRecordset'])) {
  $pageNum_wordsRecordset = $_GET['pageNum_wordsRecordset'];
}
$startRow_wordsRecordset = $pageNum_wordsRecordset * $maxRows_wordsRecordset;
$counter = $startRow_wordsRecordset+1;
$colname_wordsRecordset = "-1";
if (isset($_GET['letter'])) {
  $colname_wordsRecordset = $_GET['letter'];
}
mysql_select_db($database_binu_devilsdictionary, $binu_devilsdictionary);
$query_wordsRecordset = sprintf("SELECT word, definition FROM word WHERE word LIKE \"%s%s\" ORDER BY word ASC", GetSQLValueString($colname_wordsRecordset, "text"),
GetSQLValueString("%", "text")); 

die($query_wordsRecordset);
        
$query_limit_wordsRecordset = sprintf("%s LIMIT %d, %d", $query_wordsRecordset, $startRow_wordsRecordset, $maxRows_wordsRecordset);
$wordsRecordset = mysql_query($query_limit_wordsRecordset, $binu_devilsdictionary) or die(mysql_error());
$row_wordsRecordset = mysql_fetch_assoc($wordsRecordset);

if (isset($_GET['totalRows_wordsRecordset'])) {
  $totalRows_wordsRecordset = $_GET['totalRows_wordsRecordset'];
} else {
  $all_wordsRecordset = mysql_query($query_wordsRecordset);
  $totalRows_wordsRecordset = mysql_num_rows($all_wordsRecordset);
}
$totalPages_wordsRecordset = ceil($totalRows_wordsRecordset/$maxRows_wordsRecordset)-1;




	
	$binu_app->add_text("Devil's Dictionary", 'header');
	$binu_app->add_text("Browse by letter",'intro');
	
	
	
 do {
        $binu_app->add_text($counter.". ".$row_wordsRecordset['word']." - ".$row_wordsRecordset['definition'],'body_text');
		$counter++;
		
	 } while ($row_wordsRecordset = mysql_fetch_assoc($wordsRecordset));
		
      
	$binu_app->add_text('Options:', 'intro');

	/* Process menu options */
	
	
	$next_page = $_GET['pageNum_wordsRecordset'] + 1;
			
	if ($totalPages_wordsRecordset >= ($next_page+1)){
		$binu_app->add_link("letter.php?pageNum_wordsRecordset=".$next_page."&amp;letter=".$_GET['letter'] , "See 10 more definitions", "intro");	
    }else{
		  $binu_app->add_link("#" , "Those are all the definitions we have in this category!", "intro");
	}
	
	
	$binu_app->add_link($binu_app->application_URL , "Home", "intro");
	$binu_app->add_link("http://apps.binu.net/apps/mybinu/index.php" , "biNu Home", "intro");

	/* Show biNu page */
	$binu_app->generate_BML();

} catch (Exception $e) {
	app_error('Error: '.$e->getMessage());
}


?>
