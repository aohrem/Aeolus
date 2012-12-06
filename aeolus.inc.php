<?php
	class Aeolus {
		private $mainTemplate;
		private $contentTemplate;
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
			
			$this->contentTemplate = new Template();
			$this->contentTemplate->readTpl($this->site.$this->page);
		}
		
		private function switchSite() {
			switch ( $this->site ) {
				// home page
				case '':
				break;
				
				// table view
				case 'table':
					include('table.inc.php');
				break;
				
				// diagram view
				case 'diagram':
					include('diagram.inc.php');
				break;
			}
		}
		
		private function __destruct() {
			$this->mainTemplate->tplReplace('content', $this->contentTemplate->getTpl());
			$this->mainTemplate->printTemplate();
		}
	}
?>