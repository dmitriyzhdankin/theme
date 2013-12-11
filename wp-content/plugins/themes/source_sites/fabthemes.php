<?php
class Fabthemes extends Themes {
    public $source_site = 'http://www.fabthemes.com/';
    public $site_name = 'fabthemes';

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
        return '#primary .tblock';
    }
    public function getSourcePageUrl($page_number){
        return $this->source_site.'/page/'.$page_number.'/';
    }
    public function getSourceNameFromList(){
        return $this->currentSourceThemeHtml->find('h2 a', 0)->innertext;
    }
    public function getSourceUrlFromList(){
        return $this->currentSourceThemeHtml->find('h2 a', 0)->href;
    }
    public function getSourceZipUrlFromList(){
//        return $this->currentSourceThemeHtml->find('.notify .download a', 0)->href;
        return $this->source_site.'get/'. $this->getSourceNameFromList().'.zip';
    }
    public function getSourceDemoUrlFromList(){
        return $this->currentSourceThemeHtml->find('.boxmeta a.fab-preview', 0)->href;
    }
    public function getSourceScreenshotUrlFromList(){
        return $this->currentSourceThemeHtml->find('a img.t-shot', 0)->src;
    }

    public function getDescription(){
        return $this->page_content->find('.entry div',0)->innertext;
    }
    public function getTags(){
        $tags = array();
        foreach( $this->page_content->find('#right .category a') as $tag ) {
            $tags[] = $tag->innertext;
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