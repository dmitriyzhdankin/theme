<?php
class Fwpthemes extends Themes {
    public $source_site = 'http://fwpthemes.com/';
    public $site_name = 'fwpthemes';
    
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
    
    public function getSourceNameFromList(){
        return $this->currentSourceThemeHtml->find('h2.title a', 0)->innertext;
    }
    public function getSourceUrlFromList(){
        return $this->currentSourceThemeHtml->find('h2.title a', 0)->href;   
    }
    public function getSourceZipUrlFromList(){ 
        //fake URl
        return $this->currentSourceThemeHtml->find('h2.title a', 0)->href;
    }

    public function getSourceDemoUrlFromList(){ return null; }
    public function getSourceScreenshotUrlFromList(){ return null; }
    
    public function getDescription(){ 

        $description = '';
        $description .= $this->getName().'. ';
        $tags = $this->getTags();
        if( is_array($tags) && !empty($tags) ) {
            $description = implode(',', $tags);
        }
        return $description;
    }
    public function getTags(){ 
        $tags = array();
        foreach( $this->page_content->find('.meta_tags a') as $tag ) {
            $tags[] = $tag->innertext;
        }
        return $tags;
    }
    public function getZipUrl() {
        return $this->page_content->find('#content .post .entry p a',0)->href;
    }
    public function getPreviewUrl() {
        return $this->theme_options['preview'];
    }
    public function getThumbnailUrl() {
        return $this->page_content->find('#content .post .entry img.featured_image',0)->src;
    }
    public function getSourceThemesSelector(){
        return '#content .post-wrap';
    }
    public function getSourcePageUrl($page_number){
        return $this->source_site.'/page/'.$page_number.'/';
    }
}