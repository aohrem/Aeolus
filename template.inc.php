<?php
	class Template {
		private $template;
		private $folder = 'tpl/';
		private $fileExtension = '.html';
		private $copiedCode;
		
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
		
		public function tplReplaceOnce($tag, $replace) {
			$this->template = preg_replace('/\{'.$tag.'\}/', $replace, $this->template, 1);
		}
		
		public function copyCode($tag) {
			preg_match('@\{\+'.$tag.'\}(.*)\{\-'.$tag.'\}@s', $this->template, $subpattern);
			if ( isset($subpattern[1]) && isset($subpattern[0]) ) {
				$this->template = preg_replace('@\{\+'.$tag.'\}(.*)\{\-'.$tag.'\}@s', $subpattern[1].$subpattern[0], $this->template);
			}
			else {
				return false;
			}
		}
		
		public function cleanCode($tag) {
			$this->template = preg_replace('@\{\+'.$tag.'\}(.*)\{\-'.$tag.'\}@s', '', $this->template);
		}
		
		public function printTemplate() {
			print $this->template;
		}
	}
?>