<?php
class Wordpress extends Themes {
    public $source_site = 'http://wordpress.org';
    public $site_name = 'wordpress';
    
    private $path_to_filters = '/themes/tag-filter/';
    
    public function createPagesList() {
        $this->getPageHtml( $this->source_site. $this->path_to_filters ); // only for WP
        $pages_list = array();
        foreach($this->page_content->find('#tag-filter-form input[type=checkbox]') as $checkbox ) {
            $pages_list[$checkbox->name] = $checkbox->value;
        }
        return $pages_list;
    }
 
    public function getSourceThemesSelector(){
        return '#theme-list .available-theme';
    }
    public function getSourcePageUrl($page_number){
        return array(
            'url' => $this->source_site.$this->path_to_filters,
            'post_fields' => array('tags['.$page_number.']' => $page_number)
        );
    }
    public function getSourceNameFromList(){
        return $this->currentSourceThemeHtml->find('a.activatelink[target=_blank]', 0)->innertext;
    }
    public function getSourceUrlFromList(){
        return $this->source_site.$this->currentSourceThemeHtml->find('a.activatelink[target=_blank]', 0)->href;   
    }
    public function getSourceZipUrlFromList(){ 
        return $this->source_site.$this->currentSourceThemeHtml->find('span a.activatelink', 0)->href;
    }
    public function getSourceDemoUrlFromList(){ 
        return $this->currentSourceThemeHtml->find('a.previewlink', 0)->href;
    }
    public function getSourceScreenshotUrlFromList(){ 
        return $this->currentSourceThemeHtml->find('a.previewlink img', 0)->src;
    }
    
    public function getDescription(){ 
        $el_description = $this->page_content->find('.block-content');
        $description = '';
        if( $el_description ) {
            $description = $el_description[0]->text();
        }
        // remove Tags from descriptipon
        if(strpos($description,'Tags')) {
    	    $description = substr($description,0,strpos($description,'Tags'));
        }
        return $description;
    }
    public function getTags(){         
        $tags = array();
        foreach( $this->page_content->find('.block-content #plugin-tags a') as $tag ) {
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