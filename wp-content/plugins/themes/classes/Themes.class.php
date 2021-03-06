<?php
abstract class Themes {
    public $parsed_themes_table = 'themes';
    
    public $source_site = false;
    public $site_name = false;
    public $pagination_path = false;

    
    public $page_content = false;
    public $currentSourceThemeHtml = false;
    
    public $theme_options = false;
    
    protected $local_zip_path = 'wp-content/uploads/';
    
    abstract public function createPagesList();

    abstract public function getSourceNameFromList();
    abstract public function getSourceUrlFromList();
    abstract public function getSourceDemoUrlFromList();
    abstract public function getSourceZipUrlFromList();
    abstract public function getSourceScreenshotUrlFromList();
    
    abstract public function getSourceThemesSelector();

    abstract public function getTags();
    abstract public function getDescription();
    abstract public function getZipUrl();
    abstract public function getPreviewUrl();
    abstract public function getThumbnailUrl();

    abstract public function getSourcePageUrl($page);

    public function __construct() {
        global $wpdb;
        $this->db = $wpdb;
        $this->db->show_errors();

        if( !$this->source_site || !$this->site_name || !$this->pagination_path ) {
            return false;
        }
        return true;
    }
    
    public function loadNewPagesWithThemes() { // run from cron 
        if( !$this->getNotLoadedPages() ) {
            $this->addPagesWithThemes();
        }
        die('Pages didnt was load from site '.$this->site_name);
    }
    
    public function LoadThemesFromPages() { // run from cron
        $pages_list = $this->getNotLoadedPages();
        if( is_array($pages_list) ) {
            foreach( $pages_list as $key=>$page ) {
                $this->insertThemesFromPage($page);
                unset($pages_list[$key]);
                $this->updatePagesList($pages_list);
                die('themes was loaded from page '.$key.' of site '.$this->site_name);
            }
        }
    }
    
    protected function getThemeOptions() {
        if( !$this->theme_options ) {
            return false;
        }        

        $this->getPageHtml($this->theme_options['url']);      

        $this->theme_options['theme_name'] = trim($this->getName());
        $this->theme_options['theme_description'] = trim($this->getDescription());
        $this->theme_options['theme_tags'] = $this->getTags();
        $this->theme_options['theme_local_zip'] = trim($this->getLocalZip());
        $this->theme_options['theme_local_preview'] = trim($this->getLocalPreview());
        $this->theme_options['theme_local_thumbnail'] = trim($this->getThumbnailUrl());
    }
    
    protected function getName() {
        return $this->theme_options['name'];
    }
    
    private function getLocalZip() {
        $local_zip =  $this->local_zip_path.basename($this->theme_options['url']).'.zip';
        file_put_contents(ABSPATH.$local_zip, fopen($this->getZipUrl(), 'r'));
        return $local_zip;
    }
    
    private function getLocalPreview() {
        return $this->getPreviewUrl();
    }
    
    public function createTheme() {
        $this->getThemeOptions();

        $default_theme = array(
            'theme_name' => false,
            'theme_description' => false,
            'theme_tags' => false,
            'theme_local_zip' => false,
            'theme_local_thumbnail' => false,
            'theme_local_preview' => false,
        );       
        
        $theme = array_merge( $default_theme, $this->theme_options );

        $post = array(
            'post_type' => 'post',
            'post_title' => $theme['theme_name'],
            'post_content' => $theme['theme_description'],
            'post_status' => 'publish',
        );

        if( is_array($theme['theme_tags']) && !empty($theme['theme_tags']) ) {
            $post['tags_input'] = implode(',', $theme['theme_tags']);
        }

        $post_ID = wp_insert_post( $post );
        if( $post_ID ) {

            if( $theme['theme_local_thumbnail'] ) {
                $this->addThumbnailToPost( $post_ID, $theme['theme_local_thumbnail'] );
            }

            if( $theme['theme_local_preview'] ) {
                add_post_meta($post_ID, 'preview', $theme['theme_local_preview'], true) or
                    update_post_meta($post_ID, 'preview', $theme['theme_local_preview']);
            }

            if( $theme['theme_local_zip'] ) {
                add_post_meta($post_ID, 'zip', $theme['theme_local_zip'], true) or
                    update_post_meta($post_ID, 'zip', $theme['theme_local_zip']);
            }

            if( $theme['theme_local_thumbnail'] ) {
                add_post_meta($post_ID, 'thumbnail', $theme['theme_local_thumbnail'], true) or
                    update_post_meta($post_ID, 'thumbnail', $theme['theme_local_thumbnail']);
            }

            if( $theme['theme_local_zip'] ) {
                $zip_id = $this->createLocalZip($theme);
                add_post_meta($post_ID, 'zip_id', $zip_id) or
                    update_post_meta($post_ID, 'zip_id', $zip_id);
            }

            return true;
        }

    }
    
    protected function addThumbnailToPost( $post_id, $image_url ) {
        $upload_dir = wp_upload_dir();
        $image_data = file_get_contents($image_url);
        $filename = $post_id.'_'.basename($image_url);
        if(wp_mkdir_p($upload_dir['path'])) {
            $file = $upload_dir['path'] . '/' . $filename;
        } else {
            $file = $upload_dir['basedir'] . '/' . $filename;
        }
        file_put_contents($file, $image_data);

        $wp_filetype = wp_check_filetype($filename, null );
        $attachment = array(
            'post_mime_type' => $wp_filetype['type'],
            'post_title' => sanitize_file_name($filename),
            'post_content' => '',
            'post_status' => 'inherit'
        );
        $attach_id = wp_insert_attachment( $attachment, $file, $post_id );
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        $attach_data = wp_generate_attachment_metadata( $attach_id, $file );
        wp_update_attachment_metadata( $attach_id, $attach_data );

        set_post_thumbnail( $post_id, $attach_id );
    }
    //add local zip to table
    protected function createLocalZip($theme) {
        $file_table = 'ahm_files';
        $file = array(
            'title' => $theme['theme_name'],
            'description' => $theme['theme_name'],
            'category' => 'N;',
            'file' => basename($theme['theme_local_zip']),
            'access' => 'guest',
            'link_label' => 'Download',
            'icon' => '35.png',
        );
        $this->db->insert($file_table,$file);
        return $this->db->insert_id;
    }
    
    public function setThemeIsDownloaded() {
        $query = 'UPDATE '. $this->parsed_themes_table .' SET loaded = 1, date_loaded = "'.date('Y-m-d H:i:s').'" WHERE id = '.$this->theme_options['id'];
        return $this->db->query($query);
    }

    protected function addPagesWithThemes() {
        $this->getPageHtml( $this->source_site );      
        $pages_list = $this->createPagesList();
        $this->updatePagesList($pages_list);
        $this->setLastParsed();
        die( count($pages_list).' pages list was loaded from site '.$this->site_name);
    }
    
    protected function updatePagesList($pages_list) {
        $pages = false;
        if(is_array($pages_list) && !empty($pages_list)) {
            $pages = serialize($pages_list);
        } else {
            $pages = $this->source_site;
        }
        $this->setPagesList($pages);
    }
    
    protected function setPagesList( $pages ) {
        $table_name = $this->db->prefix.'options';
        if( $this->getNotLoadedPages() === null ) {
            $this->db->insert( $table_name,  array('option_name' => 'not_loaded_pages', 'option_value' => $pages ) );
        } else {
            $this->db->update( $table_name,  array( 'option_value' => $pages ), array( 'option_name' => 'not_loaded_pages' ) );
        }
    }
    
    private function setLastParsed() {
        $table_name = $this->db->prefix.'options';
        if( getLastParsedSource() ) {
            $this->db->update( $table_name,  array( 'option_value' => $this->site_name ), array( 'option_name' => 'last_parsed' ) );
        } else {
            $this->db->insert( $table_name,  array('option_name' => 'last_parsed', 'option_value' => $this->site_name ) );
        }
    }
    
    
    protected function getNotLoadedPages() {
        $table_name = $this->db->prefix.'options';
        $query = 'SELECT option_value FROM '.$table_name .' WHERE option_name = "not_loaded_pages"';
        $option = $this->db->get_var( $query );
        if( $option && is_serialized( $option )) {
            return unserialize($option); // ARRAY not loaded pages
        } elseif(is_string ($option)) {
            return false; // FALSE all pages was loaded
        } else {
            return null; // NULL pages was not loaded
        }
    }
    
    protected function insertThemesFromPage($page_number) {
        $this->loadPageWithThemes($page_number);
        foreach( $this->page_content->find($this->getSourceThemesSelector()) as $theme ) {
            $this->currentSourceThemeHtml = $theme;
            $theme_info = array(
                'name' => $this->getSourceNameFromList(),
                'url' => $this->getSourceUrlFromList(),
                'preview' => $this->getSourceDemoUrlFromList(),
                'zip' => $this->getSourceZipUrlFromList(),
                'screenshot' => $this->getSourceScreenshotUrlFromList(),
                'site_name' => $this->site_name,
            );
            $this->insertThemeFromList($theme_info);
        }
    }
    
    private function insertThemeFromList($theme) {
        $data = array(
            'name' => $theme['name'],
            'url' => $theme['url'],
            'zip_url' => $theme['zip'],
            'preview' => $theme['preview'],
            'screenshot' => $theme['screenshot'],
            'site_name' => $theme['site_name'],
            'added' => date('Y-m-d H:i:s'),
        );
        return $this->db->insert($this->parsed_themes_table,$data);
    }
    
    private function loadPageWithThemes($page_number) {
        $this->getPageHtml( $this->getSourcePageUrl($page_number) );
    }
    
    protected function getPageHtml( $params ) {
        $url = false;
        $post_fields = array();
        if(is_string($params)) {
            $url = $params;
        } elseif(is_array($params)) {
            if(isset($params['url'])) {
               $url = $params['url'];
            }
            if(isset($params['post_fields'])) {
                $post_fields = $params['post_fields'];
            }
        }
        if(!$url) {return false;}
        
        $page = $this->loadPage( $url, $post_fields );
        $page_content = $page['content'];
        $this->page_content = str_get_html($page_content);
    }
    
    private function loadPage( $url, $post_fields = array() ) {
        $uagent="Mozilla/5.0 (Windows; U; Windows NT 5.1; ru; rv:1.9.0.8) Gecko/2009032609 Firefox/3.0.9";
        $ch = curl_init( $url );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_ENCODING, 'UTF-8');
        curl_setopt($ch, CURLOPT_USERAGENT, $uagent);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
        if( $post_fields ) {
            $post_fields_string = '';
            foreach($post_fields as $key=>$value) { $post_fields_string .= $key.'='.$value.'&'; }
            rtrim($post_fields_string, '&');
            curl_setopt($ch,CURLOPT_POST, count($post_fields));
            curl_setopt($ch,CURLOPT_POSTFIELDS, $post_fields_string);
        } else {
            $headers = array('Content-type: text/html; charset=utf-8');
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        $content=curl_exec( $ch );
        $err=curl_errno( $ch );
        $errmsg=curl_error( $ch );
        $header=curl_getinfo( $ch );
        curl_close( $ch );
        $header['errno']=$err;
        $header['errmsg']=$errmsg;
        $header['content']=$content;
        return $header;
    }
}