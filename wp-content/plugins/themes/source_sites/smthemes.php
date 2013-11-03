<?php
class Smthemes extends Theme {
    public $source_site = 'http://smthemes.com';
    public $site_name = 'smthemes';

    public function getThemes() {

        $pages_list = $this->getPagesList();
        if( $pages_list === null ) {
            $this->loadPagesList();//need load pages list
        } elseif( $pages_list === false ) {
            die('all pages was loaded');// all pages was loaded // need stop script
        } elseif(is_array($pages_list) ) {
            $this->loadPageWithThemes($pages_list); // need load page
        }
        die('1');
    }

    private function loadPageWithThemes( $pages_list ) {
        foreach( $pages_list as $key => $page_number ) {
            echo $key;
            $this->get_web_page_html( $this->source_site.'/page/'.$page_number );
            $this->insertThemesFromPageHtml();
            unset($pages_list[$key]);
            $this->updatePagesList($pages_list);
            die;
        }
    }

    private function insertThemesFromPageHtml() {
        foreach( $this->theme_page_html->find('#main_content #catalog .item') as $theme ) {
            $theme_info = array(
                'name' => $theme->find('.caption h2', 0)->innertext,
                'url' => $theme->find('.caption a', 0)->href,
                'preview' => $this->source_site.$theme->find('.review .demo', 0)->href,
                'zip' => $this->source_site.$theme->find('.review .download', 0)->href,
                'screenshot' => $theme->find('.review a img', 0)->src,
                'site_name' => $this->site_name,
            );
            $this->insertTheme($theme_info);
        }
    }

    private function getThemeName( $theme_html ) {

    }

    protected function getCountPages() {
        if( $this->theme_page_html ) {
            $num_page = '';
            foreach( $this->theme_page_html->find('#main_content .pagination a') as $page ) {
                $num_page = $page->innertext;
            }
            if( $num_page ) {
                return intval($num_page);
            }
        }
        return false;
    }

    protected function getDescription() {
        return $this->theme_page_html->find('#upcontent .articles dd div div',0)->innertext;
    }

    protected function getTags() {
        $tags = array();
        foreach( $this->theme_page_html->find('.bigitem div a') as $tag ) {
            if( ( strpos($tag->innertext, 'Download') !== 0 ) && $tag->innertext != 'Demo' ) {
                $tags[] = $tag->innertext;
            }
        }
        return $tags;
    }

    protected function getLocalZip() {
        $local_zip =  $this->local_zip_path.basename($this->theme_options['zip_url']);
        file_put_contents(ABSPATH.$local_zip.'.zip', fopen('http://smthemes.com/getfile/'.basename($this->theme_options['zip_url']), 'r'));
        return $local_zip;
    }

    protected function getLocalPreview() {
        return $this->theme_options['preview'];
    }

    protected function getThumbnail() {
        return $this->theme_options['screenshot'];
    }


}
?>