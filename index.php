<?php
	require_once('php_helper_class_library/class.biNu.php');
	require_once("inc/config.php");
	// Assign application configuration variables during constructor
	$app_config = array (
		'dev_id' => 17768,								// Your DevCentral developer ID goes here
		'app_id' => 5733,								// Your DevCentral application ID goes here
		'app_name' => 'Devil\'s Dictionary',				// Your application name goes here
		'app_home' => 'http://binu-devilsdictionary.azurewebsites.net/',	// Publically accessible URI
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
		
		$binu_app->add_text("Devil's Dictionary",'header');	 
		if (isset($_GET['binu_transaction_res'])&&($_GET['binu_transaction_res']<>0)) 
		{
			header("Location: ".$_SERVER['SERVER_NAME'].dirname($_SERVER['PHP_SELF'])."error.php?binu_transaction_res=".$_GET['binu_transaction_res'] );
		}
		
		//Pick one featured recordset
		$maxRows_featuredWordsRecordset = 1;
		$pageNum_featuredWordsRecordset = 0;
		if (isset($_GET['pageNum_featuredWordsRecordset'])) {
		  $pageNum_featuredWordsRecordset = $_GET['pageNum_featuredWordsRecordset'];
		}
		$startRow_featuredWordsRecordset = $pageNum_featuredWordsRecordset * $maxRows_featuredWordsRecordset;
		
		mysql_select_db($database_binu_devilsdictionary, $binu_devilsdictionary);
		$query_featuredWordsRecordset = "SELECT * FROM word WHERE is_featured = 1 ORDER BY RAND()";
		$query_limit_featuredWordsRecordset = sprintf("%s LIMIT %d, %d", $query_featuredWordsRecordset, $startRow_featuredWordsRecordset, $maxRows_featuredWordsRecordset);
		$featuredWordsRecordset = mysql_query($query_limit_featuredWordsRecordset, $binu_devilsdictionary) or die(mysql_error());
		$row_featuredWordsRecordset = mysql_fetch_assoc($featuredWordsRecordset);
		
		if (isset($_GET['totalRows_featuredWordsRecordset'])) {
		  $totalRows_featuredWordsRecordset = $_GET['totalRows_featuredWordsRecordset'];
		} else {
		  $all_featuredWordsRecordset = mysql_query($query_featuredWordsRecordset);
		  $totalRows_featuredWordsRecordset = mysql_num_rows($all_featuredWordsRecordset);
		}
		$totalPages_featuredWordsRecordset = ceil($totalRows_featuredWordsRecordset/$maxRows_featuredWordsRecordset)-1;
		
		//Random Word
		$binu_app->add_text("Random Word:",'body_text');
		$binu_app->add_text($row_featuredWordsRecordset['word']." - ".$row_featuredWordsRecordset['definition'],'body_text');
		
		$binu_app->add_text("",'body_text');	
		$binu_app->add_text("Click on a letter below to browse by letter",'body_text');
	 
		$binu_app->add_link("letter.php?letter=a&amp;pageNum_wordsRecordset=0" , "A", "intro");
		$binu_app->add_link("letter.php?letter=b&amp;pageNum_wordsRecordset=0" , "B", "intro");
		$binu_app->add_link("letter.php?letter=c&amp;pageNum_wordsRecordset=0" , "C", "intro");
		$binu_app->add_link("letter.php?letter=d&amp;pageNum_wordsRecordset=0" , "D", "intro");
		$binu_app->add_link("letter.php?letter=e&amp;pageNum_wordsRecordset=0" , "E", "intro");
		$binu_app->add_link("letter.php?letter=f&amp;pageNum_wordsRecordset=0" , "F", "intro");
		$binu_app->add_link("letter.php?letter=g&amp;pageNum_wordsRecordset=0" , "G", "intro");
		$binu_app->add_link("letter.php?letter=h&amp;pageNum_wordsRecordset=0" , "H", "intro");
		$binu_app->add_link("letter.php?letter=i&amp;pageNum_wordsRecordset=0" , "I", "intro");
		$binu_app->add_link("letter.php?letter=j&amp;pageNum_wordsRecordset=0" , "J", "intro");
		$binu_app->add_link("letter.php?letter=k&amp;pageNum_wordsRecordset=0" , "K", "intro");
		$binu_app->add_link("letter.php?letter=l&amp;pageNum_wordsRecordset=0" , "L", "intro");
		$binu_app->add_link("letter.php?letter=m&amp;pageNum_wordsRecordset=0" , "M", "intro");
		$binu_app->add_link("letter.php?letter=n&amp;pageNum_wordsRecordset=0" , "N", "intro");
		$binu_app->add_link("letter.php?letter=o&amp;pageNum_wordsRecordset=0" , "O", "intro");
		$binu_app->add_link("letter.php?letter=p&amp;pageNum_wordsRecordset=0" , "P", "intro");
		$binu_app->add_link("letter.php?letter=q&amp;pageNum_wordsRecordset=0" , "Q", "intro");
		$binu_app->add_link("letter.php?letter=r&amp;pageNum_wordsRecordset=0" , "R", "intro");
		$binu_app->add_link("letter.php?letter=s&amp;pageNum_wordsRecordset=0" , "S", "intro");
		$binu_app->add_link("letter.php?letter=t&amp;pageNum_wordsRecordset=0" , "T", "intro");
		$binu_app->add_link("letter.php?letter=u&amp;pageNum_wordsRecordset=0" , "U", "intro");
		$binu_app->add_link("letter.php?letter=v&amp;pageNum_wordsRecordset=0" , "V", "intro");
		$binu_app->add_link("letter.php?letter=w&amp;pageNum_wordsRecordset=0" , "W", "intro");
		$binu_app->add_link("letter.php?letter=x&amp;pageNum_wordsRecordset=0" , "X", "intro");
		$binu_app->add_link("letter.php?letter=y&amp;pageNum_wordsRecordset=0" , "Y", "intro");
		$binu_app->add_link("letter.php?letter=z&amp;pageNum_wordsRecordset=0" , "Z", "intro");
			
		$binu_app->add_link($binu_app->application_URL , "Home", "intro");
		$binu_app->add_link("http://apps.binu.net/apps/mybinu/index.php" , "biNu Home", "intro");
		$binu_app->add_link("feedback.php", "Feedback/Help/About/More Info" , "intro");
				
		/* Show biNu page */
		$binu_app->generate_BML();
	
	} 
	catch (Exception $e) 
	{
		app_error('Error: '.$e->getMessage());
	}
?>
