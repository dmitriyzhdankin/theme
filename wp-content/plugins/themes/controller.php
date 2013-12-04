<?php
global $avaiable_source;
$avaiable_source = array(
    'smthemes' => 1,
    'wordpress' => 0,
    'fwpthemes' => 1,
); 

$url = $_SERVER['REQUEST_URI'];
$url = trim( $url, '/');
if( $url ) {
    $path = explode('/', $url);
    if( $path && $path[0] == 'pars' && isset($path[1]) && $path[1] ) {

        error_reporting(E_ALL);
        ini_set("display_errors","1");

        switch( $path[1] ) {
            case 'download_theme' : {
                downloadTheme();
                break;
            }
            case 'parsing_source' : {
                parsingSource();
                break;
            }
            case 'parsing_page' : {
                parsingPage();
                break;
            }
//            deleted after test
//            
//            case 'fwpthemes' : {
//                $theme = new Fwpthemes();
//                $theme->loadNewPagesWithThemes();
//                break;
//            }
//            case 'load_fwpthemes' : {
//                $theme = new Fwpthemes();
//                $theme->LoadThemesFromPages();
//                break;
//            }
//            
//            case 'smthemes_get_themes' : {
//                $smt = new Smthemes();
//                $smt->LoadThemesFromPages();
//                //$smt->loadNewPagesWithThemes();
//                break;
//            }
//            
//            case 'rockkitty_get_themes' : {
//                $rk = new Rockkitty();
//                $rk->getThemes();
//                break;
//            }
//
//            case 'wordpress_get_filters' : {
//                $wp = new Wordpress();
//                $wp->_getFilters();
//                break;
//            }
//            case 'wordpress_get_themes' : {
//                $wp = new Wordpress();
//                $wp->_getThemes();
//                break;
//            }
        }
        die;
    }
}

function downloadTheme() {
    global $wpdb;
    $query = 'SELECT * FROM themes WHERE loaded = 0 LIMIT 0,1';
    $theme_options = $wpdb->get_row( $query, ARRAY_A );
    
    $tmp = eval('$parser = new '.$theme_options['site_name']."();");
    if( $parser ) {
        $parser->theme_options = $theme_options;
        $parser->createTheme();
        $parser->setThemeIsDownloaded();
        echo '<br>'.$parser->theme_options['theme_name'].' was loaded';
    }
    die;
}
function parsingPage() {
    if(!$source_for_parsing = getSourceForNewPars()) {
        die('Not have source for parsing');
    }
    $tmp = eval('$parser = new '.$source_for_parsing."();");
    if( $parser ) {
        $parser->LoadThemesFromPages();
    }    
}

function parsingSource() {
    if(!$source_for_parsing = getSourceForNewPars()) {
        die('Not have source for parsing');
    }
    
    $tmp = eval('$parser = new '.$source_for_parsing."();");
    if( $parser ) {
        $parser->loadNewPagesWithThemes();
    }    
}
     
function getLastParsedSource() {
    global $wpdb;
    $table_name = $wpdb->prefix.'options';
    $query = 'SELECT option_value FROM '.$table_name .' WHERE option_name = "last_parsed"';
    return $wpdb->get_var( $query );
}

function getSourceForNewPars() {
    global $avaiable_source;
    $last_parsed = getLastParsedSource();
    if( $last_parsed ) {
        if(isset($avaiable_source[$last_parsed])) {
            unset( $avaiable_source[$last_parsed] );
        }
    }
    
    if(!empty($avaiable_source)) {
        shuffle_assoc($avaiable_source);
        foreach( $avaiable_source as $key=>$source ) {
            if( $source ) { return $key; }
        }
    }
    return false;
}

function shuffle_assoc(&$array) {
    $keys = array_keys($array);
    shuffle($keys);
    foreach($keys as $key) {
        $new[$key] = $array[$key];
    }
    $array = $new;
    return true;
}

?>