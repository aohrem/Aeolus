<?php
	class Aeolus {
		private $mainTemplate;
		private $contentTemplate;
		private $registerTemplate;
		private $site;
		private $page;
		private $reg;
		private $language;
		private $standardLanguage = 'de';
		private $languages = array('en', 'de');
		
		public function __construct() {
			$this->loadTemplate();
			$this->setLanguage();
			$this->readCurrentSite();
			$this->switchSite();
		}
		
		private function loadTemplate() {
			// Load Template class and get contents of main template
			include('template.inc.php');
			$this->mainTemplate = new Template();
			$this->mainTemplate->readTpl('main');
		}
		
		private function setLanguage() {
			// get current language
			if ( isset($_GET['lang']) ) {
				$this->language = $_GET['lang'];
				
				// iterate available languages to check if we have got a correct language abbreviation
				$langCorrect = false;
				foreach ( $this->languages as $lang ) {
					if ( $this->language == $lang ) {
						$langCorrect = true;
					}
				}
				
				// set standard language if the given language is not supported
				if ( ! $langCorrect ) {
					$this->language = $this->standardLanguage;
				}
			}
			else {
				$this->language = $this->standardLanguage;
			}
		}
		
		private function readCurrentSite() {
			// get current site and set content template
			if ( isset($_GET['s']) ) {
				$this->site = mysql_real_escape_string($_GET['s']);
			}
			else {
				$this->site = 'start';
			}

			// get current page and extend content template if it's set
			if ( isset($_GET['p']) ) {
				$this->page = '_'.mysql_real_escape_string($_GET['p']);
			}
			else {
				$this->page = '';
			}
			
			$this->contentTemplate = new Template();
			$this->contentTemplate->readTpl($this->site.$this->page);
			$this->registerTemplate = new Template();
			$this->registerTemplate->readTpl('register');
			$this->registerTemplate->tplReplace('site', $this->site);

			// handle register popup
			include('register.inc.php');
		}
		
		private function switchSite() {
			switch ( $this->site ) {
				case '':
				// home page
				break;
				
				// table view
				case 'table':
					include('table.inc.php');
				break;
				
				// diagram view
				case 'diagram':
					include('diagram.inc.php');
				break;
				
				// map view
				case 'map':
					include('map.inc.php');
				break;
				
				// "delete AQE"-page
				case 'delete':
					include('delete.inc.php');
				break;
			}
		}
		
		private function __destruct() {
			$this->mainTemplate->tplReplace('content', $this->contentTemplate->getTpl());
			$this->mainTemplate->tplReplace('register', $this->registerTemplate->getTpl());
			$this->mainTemplate->setLanguage($this->language);
			$this->mainTemplate->printTemplate();
		}
	}
?>