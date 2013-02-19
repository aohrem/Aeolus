<?php
	class Aeolus {
		private $mainTemplate;
		private $contentTemplate;
		private $registerTemplate;
		private $site = '';
		private $page = '';
		private $reg;
        private $language;
		private $standardLanguage = 'de';
		private $languages = array('en', 'de');
		
		public function __construct() {
			$this->loadTemplate();
			$this->setLanguage();
			$this->readCurrentSite();
            $this->replaceCurrentSite();
            $this->handleRegisterPopup();
			$this->switchSite();
		}
		
		private function loadTemplate() {
			// load Template class and get contents of main template
			include('template.inc.php');
			$this->mainTemplate = new Template();
			$this->mainTemplate->readTpl('main');
		}
		
		private function setLanguage() {
			// get current language
			if ( isset($_GET['lang']) ) {
				$language = $_GET['lang'];
				
				// iterate available languages to check if we have got a correct language abbreviation
				$langCorrect = false;
				foreach ( $this->languages as $itlang ) {
					if ( $language == $itlang ) {
						$langCorrect = true;
					}
				}
				
				// set standard language if the given language is not supported
				if ( ! $langCorrect ) {
					$language = $this->standardLanguage;
				}
			}
			else {
				$language = $this->standardLanguage;
			}
            
            $this->language = $language;
            
			include('lang/'.$language.'.lang.php');
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
		}
        
        private function replaceCurrentSite() {
            $url = $_SERVER['REQUEST_URI'];
            $url_parts = explode('/', $url);
            $url = $url_parts[sizeof($url_parts) - 1];
            
            foreach ( $this->languages as $itlang ) {
                $url = str_replace('&lang='.$itlang, '', $url);
                $url = str_replace('?lang='.$itlang, '', $url);
            }
            
            if ( strstr($url, '?') ) {
                $url .= '&amp;';
            }
            else {
                $url .= '?';
            }
            
            $this->mainTemplate->tplReplace('site', $url);
        }
        
        private function handleRegisterPopup() {
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
            $this->mainTemplate->tplReplace('language', $this->language);
			$this->mainTemplate->translateTemplate();
			$this->mainTemplate->printTemplate();
		}
	}
?>