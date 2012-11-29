<?php
	class Aeolus {
		private $mainTemplate;
		private $site;
		private $page;
		
		public function __construct() {
			$this->loadTemplate();
			$this->readCurrentSite();
			$this->switchSite();
		}
		
		private function loadTemplate() {
			// Load Template class and get contents of main template
			include('template.inc.php');
			$this->mainTemplate = new Template();
			$this->mainTemplate->readTpl('main');
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
			
			$contentTemplate = new Template();
			$contentTemplate->readTpl($this->site.$this->page);
			$this->mainTemplate->tplReplace('content', $contentTemplate->getTpl());
		}
		
		private function switchSite() {
			switch ( $this->site ) {
				// home page
				case '':
				break;
			}
		}
		
		private function __destruct() {
			$this->mainTemplate->printTemplate();
		}
	}
?>