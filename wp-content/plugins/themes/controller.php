<?php
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
            case 'fwpthemes' : {
                $theme = new Fwpthemes();
                $theme->loadNewPagesWithThemes();
                break;
            }
            case 'load_fwpthemes' : {
                $theme = new Fwpthemes();
                $theme->LoadThemesFromPages();
                break;
            }
            
            case 'smthemes_get_themes' : {
                $smt = new Smthemes();
                $smt->getThemes();
                break;
            }
            
            case 'rockkitty_get_themes' : {
                $rk = new Rockkitty();
                $rk->getThemes();
                break;
            }

            case 'wordpress_get_filters' : {
                $wp = new Wordpress();
                $wp->_getFilters();
                break;
            }
            case 'wordpress_get_themes' : {
                $wp = new Wordpress();
                $wp->_getThemes();
                break;
            }
        }
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
?>