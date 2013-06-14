<?php
require_once('php_helper_class_library/class.biNu.php');
require_once("inc/config.php");
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
	/////$binu_app->add_style( array('name' => 'footer', 'color' => '#0000FF') );
	
	
	$binu_app->add_text("Murphy's Laws",'header');
	
	$binu_app->add_text("Select Category",'intro');
	
	 
	
	
	
if (isset($_GET['binu_transaction_res'])&&($_GET['binu_transaction_res']<>0)) 
	{
		header("Location: ".$_SERVER['SERVER_NAME'].dirname($_SERVER['PHP_SELF'])."error.php?binu_transaction_res=".$_GET['binu_transaction_res'] );
	}
	
	//We want to pick 10 categories at a time
	$maxRows_categoryRecordset = 10;
	$pageNum_categoryRecordset = 0;
	if (isset($_GET['pageNum_categoryRecordset'])) 
	{
  		$pageNum_categoryRecordset = $_GET['pageNum_categoryRecordset'];
	}
	$startRow_categoryRecordset = $pageNum_categoryRecordset * $maxRows_categoryRecordset;

	mysql_select_db($database_binu_murphyslaws, $binu_murphyslaws);
	$query_categoryRecordset = "SELECT * FROM category ORDER BY category_id ASC";
	$query_limit_categoryRecordset = sprintf("%s LIMIT %d, %d", $query_categoryRecordset, $startRow_categoryRecordset, 							$maxRows_categoryRecordset);
	$categoryRecordset = mysql_query($query_limit_categoryRecordset, $binu_murphyslaws) or die(mysql_error());
	$row_categoryRecordset = mysql_fetch_assoc($categoryRecordset);

	if (isset($_GET['totalRows_categoryRecordset'])) 
	{
  		$totalRows_categoryRecordset = $_GET['totalRows_categoryRecordset'];
	} else 
	{
	  $all_categoryRecordset = mysql_query($query_categoryRecordset);
	  $totalRows_categoryRecordset = mysql_num_rows($all_categoryRecordset);
	}
	$totalPages_categoryRecordset = ceil($totalRows_categoryRecordset/$maxRows_categoryRecordset)-1;
	
	
	$catIDBuffer = -1;
	
 do 
	 	{
			$binu_app->add_link("quotes.php?pageNum_quoteRecordset=0&amp;id=".$row_categoryRecordset['category_id'], $row_categoryRecordset['category_id'].") ".$row_categoryRecordset['category'],'body_text');

		
	} while ($row_categoryRecordset = mysql_fetch_assoc($categoryRecordset));
	 
	
	if (isset($_GET['pageNum_categoryRecordset']) && $_GET['pageNum_categoryRecordset'] >0 ) 
			{
				$nextPage = $pageNum_categoryRecordset + 1;
				$prevPage = $pageNum_categoryRecordset - 1;
			  	if ($catIDBuffer < $totalRows_categoryRecordset)
				{
					$binu_app->add_link("?pageNum_categoryRecordset=".$nextPage, "Next Page", "intro");
					$binu_app->add_link("?pageNum_categoryRecordset=".$prevPage, "Previous Page", "intro");
					
				}
				if ($catIDBuffer == $totalRows_categoryRecordset)
					{
						$binu_app->add_link("?pageNum_categoryRecordset=".$prevPage, "Previous Page", "intro");
					}
			}
			else
			{
				$nextPage = $pageNum_categoryRecordset + 1;
				$binu_app->add_link("?pageNum_categoryRecordset=".$nextPage, "Next Page", "intro");
			}
			
	$binu_app->add_link($binu_app->application_URL , "Home", "intro");
	$binu_app->add_link("http://apps.binu.net/apps/mybinu/index.php" , "biNu Home", "intro");
			
	/* Show biNu page */
	$binu_app->generate_BML();

} catch (Exception $e) {
	app_error('Error: '.$e->getMessage());
}


?>
