<?php
class Smthemes extends Themes {
    public $source_site = 'http://smthemes.com';
    public $site_name = 'smthemes';
    
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
            $num_page = '';
            foreach( $this->page_content->find('#main_content .pagination a') as $page ) {
                $num_page = $page->innertext;
            }
            if( $num_page ) {
                return intval($num_page);
            }
        }
        return false;
    }
    
    public function getSourceThemesSelector(){
        return '#main_content #catalog .item';
    }
    public function getSourcePageUrl($page_number){
        return $this->source_site.'/page/'.$page_number.'/';
    }
    public function getSourceNameFromList(){
        return $this->currentSourceThemeHtml->find('.caption h2', 0)->innertext;
    }
    public function getSourceUrlFromList(){
        return $this->currentSourceThemeHtml->find('.caption a', 0)->href;   
    }
    public function getSourceZipUrlFromList(){ 
        return 'http://smthemes.com/getfile/'.basename($this->currentSourceThemeHtml->find('.review .download', 0)->href);
    }
    public function getSourceDemoUrlFromList(){ 
        return 'http://smthemes.com'.$this->currentSourceThemeHtml->find('.review .demo', 0)->href; 
    }
    public function getSourceScreenshotUrlFromList(){ 
        return $this->currentSourceThemeHtml->find('.review a img', 0)->src; 
    }
    
    public function getDescription(){ 
        return $this->page_content->find('#upcontent .articles dd div div',0)->innertext;
    }
    public function getTags(){ 
        $tags = array();
        foreach( $this->page_content->find('.bigitem div a') as $tag ) {
            if( ( strpos($tag->innertext, 'Download') !== 0 ) && $tag->innertext != 'Demo' ) {
                $tags[] = $tag->innertext;
            }
        }
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