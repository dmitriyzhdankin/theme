<?php
class Rockkitty extends Themes {
    public $source_site = 'http://themes.rock-kitty.net/';
    public $site_name = 'rockkitty';

    public function createPagesList() {
        $pages_list = array();
        if($count_pages = $this->getCountPages() ) {
            for( $i=1; $i <= $count_pages; $i++ ) {
                $pages_list[$i] = $i;
            }
        }
        return $pages_list;
    }
    private function getCountPages() {
        if( $this->page_content ) {
            $href = trim( $this->page_content->find('.wp-pagenavi a.last',0)->href, '/');
            return intval(substr($href, strrpos( $href, '/')+1 ));
        }
        return false;
    }
    
    public function getSourceThemesSelector(){
        return '.leftside .template-box';
    }
    public function getSourcePageUrl($page_number){
        return $this->source_site.'/page/'.$page_number.'/';
    }
    public function getSourceNameFromList(){
        return $this->currentSourceThemeHtml->find('h3 a', 0)->innertext;
    }
    public function getSourceUrlFromList(){
        return $this->currentSourceThemeHtml->find('h3 a', 0)->href;   
    }
    public function getSourceZipUrlFromList(){ 
        return $this->currentSourceThemeHtml->find('.notify .download a', 0)->href;
    }
    public function getSourceDemoUrlFromList(){ 
        return $this->currentSourceThemeHtml->find('.notify .preview a', 0)->href; 
    }
    public function getSourceScreenshotUrlFromList(){
        return substr($this->currentSourceThemeHtml->find('.image a img', 0)->src, strpos($this->currentSourceThemeHtml->find('.image a img', 0)->src, 'imgfile=') +  strlen('imgfile=') ); 
    }
    
    public function getDescription(){ 
        return $this->page_content->find('.innerimage p',7)->innertext;
    }
    public function getTags(){ 
        $tags = array();
        foreach( $this->page_content->find('.template-box2 .postmeta a') as $tag ) {
            $tags[] = $tag->innertext;
        }
        $tags[] = trim(preg_replace("(<([a-z]+)>.*?</\\1>)is","",$this->page_content->find('.innerimage p',3)->innertext));
        return $tags;
        
    }
    public function getZipUrl() {
        return $this->theme_options['zip_url'];
    }
    public function getPreviewUrl() {
        return $this->theme_options['preview'];
    }
    public function getThumbnailUrl() {
        return $this->theme_options['screenshot'];
    }
}
?>