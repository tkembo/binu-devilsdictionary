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
		
		$binu_app->add_text("Feedback/Help/About/More Info",'header');	
		$binu_app->add_text("",'intro');	
		$binu_app->add_text("My name is Tawanda Kembo. I am glad to have people like you using my app",'body_text');
		$binu_app->add_text("If you have any suggestions on how I can improve this app to give you a better experience when you use it, the fastest way to reach me is via email. My email address is tkembo@gmail.com.",'body_text');
		$binu_app->add_text("Also, I have made this application opensource so if you are developer feel free to fork it on GitHub, contribute or to help me with refactoring the code. The GitHub repository for this application (and others I have worked on) is at https://github.com/tkembo?tab=repositories.",'body_text');
		$binu_app->add_text("If you like this application, then you will probably like other applications I have developed and these are:",'body_text');			
		$binu_app->add_text("- Murphy's Laws",'intro');
		$binu_app->add_text("- Urban Dictionary",'intro');
		$binu_app->add_text("",'body_text');
		$binu_app->add_text("You can find out more about me on: http://about.me/tawandakembo",'body_text');	
		
		$binu_app->add_link($binu_app->application_URL , "Home", "intro");
		$binu_app->add_link("http://apps.binu.net/apps/mybinu/index.php" , "biNu Home", "intro");
				
		/* Show biNu page */
		$binu_app->generate_BML();
	
	} catch (Exception $e) {
		app_error('Error: '.$e->getMessage());
	}

?>
