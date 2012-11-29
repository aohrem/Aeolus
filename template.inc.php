<?php
	class Template {
		private $template;
		private $folder = 'tpl/';
		private $fileExtension = '.html';
		
		public function getTpl() {
			return $this->template;
		}
		
		public function readTpl($filename) {
			$file = $this->folder.$filename.$this->fileExtension;
			if ( ! file_exists($file) ) {
				$file = $this->folder.'error_404'.$this->fileExtension;
			}
			
			$this->template = fread(fopen($file, 'r'), filesize($file));
		}
		
		public function tplReplace($tag, $replace) {
			$this->template = str_replace('{'.$tag.'}', $replace, $this->template);
		}
		
		public function printTemplate() {
			print $this->template;
		}
	}
?>