<?php
	if ( isset($_GET['js']) ) {
		$jsFileName = $_GET['js'];
		
		include('class/template.class.php');
		include('include/functions.class.php');
		
		setLanguage(array('en', 'de'));
	
		$js = new Template();
		$js->setFolder('js/');
		$js->setFileExtension('.js');
		$js->readTpl($jsFileName);
		$js->translateTemplate();
		$js->printTemplate();
	}
?>