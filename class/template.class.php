<?php
    // template class represents one html template and is used to replace tags in it and finally to print it
    class Template {
        private $template;
        private $folder = 'tpl/';
        private $fileExtension = '.html';
        private $copiedCode;
        
        // method returns the template's source code
        public function getTpl() {
            return $this->template;
        }
        
        // method is used to change the default template folder
        public function setFolder($folder) {
            $this->folder = $folder;
        }
        
        // method is used to change the default template file extension 
        public function setFileExtension($fileExtension) {
            $this->fileExtension = $fileExtension;
        }
        
        // method reads the source code of the specified file and saves it to the template attribute
        public function readTpl($filename) {
            $file = $this->folder.$filename.$this->fileExtension;
            
            // read the error 404 template if file does not exist
            if ( ! file_exists($file) ) {
                $file = $this->folder.'error_404'.$this->fileExtension;
            }
            
            $this->template = fread(fopen($file, 'r'), filesize($file));
        }
        
        // method replaces all occurences of {$tag} with $replace
        public function tplReplace($tag, $replace) {
            $this->template = str_replace('{'.$tag.'}', $replace, $this->template);
        }
        
        // method replaces the first occurence of {$tag} with $replace
        public function tplReplaceOnce($tag, $replace) {
            $this->template = preg_replace('/\{'.$tag.'\}/', $replace, $this->template, 1);
        }
        
        // method iterates the associative $replacesArray and replaces all occurances of {$tag} (key) with $replace (value)
        public function tplMultipleReplace($replaceArray) {
            foreach ( $replaceArray as $tag => $replace ) {
                $this->tplReplace($tag, $replace);
            }
        }
        
        // method iterates the associative $replacesArray and replaces the first occurances of {$tag} (key) with $replace (value)
        public function tplMultipleReplaceOnce($replaceArray) {
            foreach ( $replaceArray as $tag => $replace ) {
                $this->tplReplaceOnce($tag, $replace);
            }
        }
        
        // method duplicates all code between {+$tag} and {-$tag} in the template
        public function copyCode($tag) {
            preg_match('@\{\+'.$tag.'\}(.*)\{\-'.$tag.'\}@s', $this->template, $subpattern);
            if ( isset($subpattern[1]) && isset($subpattern[0]) ) {
                $this->template = preg_replace('@\{\+'.$tag.'\}(.*)\{\-'.$tag.'\}@s', $subpattern[1].$subpattern[0], $this->template);
            }
            else {
                return false;
            }
        }
        
        // method deletes all code between {+$tag} and {-$tag} in the template
        public function cleanCode($tag) {
            $this->template = preg_replace('@\{\+'.$tag.'\}(.*)\{\-'.$tag.'\}@s', '', $this->template);
        }
        
        // method replaces all language tags between [[ and ]] with the translation found in the global translation array
        public function translateTemplate() {
            while ( $start = strpos( $this->template, '[[') ) {
                $length = strpos($this->template, ']]') - $start;
                $search = substr($this->template, $start + 2, $length - 2);
                if ( isset($GLOBALS['translation'][$search]) ) {
                    $this->template = str_replace('[['.$search.']]', $GLOBALS['translation'][$search], $this->template);
                }
                else {
                    $this->template = str_replace('[['.$search.']]', $search.' ('.$GLOBALS['translation']['translation_not_found'].')', $this->template);
                }
            }
        }
        
        // method prints the template
        public function printTemplate() {
            print $this->template;
        }
    }
?>