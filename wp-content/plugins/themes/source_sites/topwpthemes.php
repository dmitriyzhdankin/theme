<?php
class Topwpthemes extends Themes {
    public $source_site = 'http://topwpthemes.com/';
    public $site_name = 'topwpthemes';

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
            $href = trim( $this->page_content->find('.pagination a',3)->href, '/');
            return intval(substr($href, strrpos( $href, '/')+1 ));
        }
        return false;
    }

    public function getSourceThemesSelector(){
        return '#content .grid_4';
    }
    public function getSourcePageUrl($page_number){
        return $this->source_site.'/page/'.$page_number.'/';
    }
    public function getSourceNameFromList(){
        return $this->currentSourceThemeHtml->find('h1 a', 0)->innertext;
    }
    public function getSourceUrlFromList(){
        return $this->currentSourceThemeHtml->find('h1 a', 0)->href;
    }
    public function getSourceZipUrlFromList(){
        return $this->currentSourceThemeHtml->find('a.download', 0)->href;
    }
    public function getSourceDemoUrlFromList(){
        return $this->currentSourceThemeHtml->find('a.preview', 0)->href;
    }
    public function getSourceScreenshotUrlFromList(){
        return $this->currentSourceThemeHtml->find('.screenshot img', 0)->src;
    }

    public function getDescription(){
		$description = '';
		foreach( $this->page_content->find('.metainfo ul li') as $meta ) {
            $description .= $meta->innertext .', ';
        }
		return trim(trim($description),',');
    }
    public function getTags(){
        $tags = array();
        foreach( $this->page_content->find('.themecat a') as $tag ) {
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