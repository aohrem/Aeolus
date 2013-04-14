<?php
    class Aeolus {
        // attributes containing GET parameters read from the URL
        private $url;
        private $site = '';
        private $page = '';
        private $reg;
        
        // attributes containing the HTML code of the website as strings
        private $mainTemplate;
        private $registerTemplate;
        private $contentTemplate;
        
        // current language chosen by the user and supported languages
        private $language;
        private $languages = array('en', 'de');
        
        // constructor calls all necessary methods to build the website sequentially
        public function __construct() {
            $this->loadTemplate();
            $this->language = setLanguage($this->languages);
            $this->readCurrentSite();
            $this->replaceCurrentSite();
            $this->handleRegisterPopup();
            $this->switchSite();
        }
        
        private function loadTemplate() {
            // load Template class and get contents of main template
            include('template.class.php');
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

            // get current page and extend content template if page is set
            if ( isset($_GET['p']) ) {
                $this->page = '_'.mysql_real_escape_string($_GET['p']);
            }
            else {
                $this->page = '';
            }
            
            // read template file depending on the current site and page
            $this->contentTemplate = new Template();
            $this->contentTemplate->readTpl($this->site.$this->page);
        }
        
        private function replaceCurrentSite() {
            // get the current URL and get the part after the last slash
            $url = $_SERVER['REQUEST_URI'];
            $url_parts = explode('/', $url);
            $url = htmlentities($url_parts[sizeof($url_parts) - 1]);
            
            // get the current URL with the host/domain part and cut off the tail
            $this->url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            $url_parts = explode('/', $this->url);
            $this->url = str_replace($url_parts[sizeof($url_parts) - 1], '', $this->url);
            
            // cut off the language part from the URL ...
            $url = str_replace('&amp;lang='.$this->language, '', $url);
            $url = str_replace('?lang='.$this->language, '', $url);
            
            // ... and add a ? if there is none or a & in the other case
            if ( strstr($url, '?') ) {
                $url .= '&amp;';
            }
            else {
                $url .= '?';
            }
            
            // replace URL in the main template
            $this->mainTemplate->tplReplace('site', $url);
        }
        
        private function handleRegisterPopup() {
            include('include/register.inc.php');
        }
        
        private function switchSite() {
            switch ( $this->site ) {
                // home page
                case '':
                case 'start':
                    include('include/start.inc.php');
                break;
                
                // table view
                case 'table':
                    include('datavisualisation.class.php');
                    include('table.class.php');
                    new Table($this->contentTemplate);
                break;
                
                // diagram view
                case 'diagram':
                    include('datavisualisation.class.php');
                    include('diagram.class.php');
                    new Diagram($this->contentTemplate);
                break;
                
                // data download
                case 'download':
                    include('datavisualisation.class.php');
                    include('download.class.php');
                    new Download($this->contentTemplate);
                break;
                
                // map view
                case 'map':
                    include('include/map.inc.php');
                break;
                
                // "delete AQE"-page
                case 'delete':
                    include('include/delete.inc.php');
                break;
                
                // contact page
                case 'contact':
                    include('include/contact.inc.php');
                break;
            }
        }
        
        private function __destruct() {
            // copy the content template into the main template
            $this->mainTemplate->tplReplace('content', $this->contentTemplate->getTpl());
            
            // translate all tags in [[ ]] to the language chosen by the user
            $this->mainTemplate->translateTemplate();
            
            // replace current URL and language in the main template
            $this->mainTemplate->tplReplace('url', $this->url);
            $this->mainTemplate->tplReplace('language', $this->language);
            
            // print the website
            $this->mainTemplate->printTemplate();
        }
    }
?>