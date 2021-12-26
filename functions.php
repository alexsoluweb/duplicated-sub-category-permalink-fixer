<?php
/**
* @link              alexsoluweb.digital
* @since             1.0.0
* @package           Duplicated_Sub_Category_Permalink_Fixer
*
* @wordpress-plugin
* Plugin Name:       DSCPF
* Plugin URI:        https://github.com/alexsoluweb/duplicated-sub-category-permalink-fixer
* Description:       Fix duplicated sub categories permalink
* Version:           1.0
* Author:            Alexsoluweb
* Author URI:        alexsoluweb.digital
* License:           GPL-2.0+
* License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
* Text Domain:       DSCPF	
**/

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	wp_die();
}

define('PLUGIN_PREFIX', 'dscpf');
require_once __DIR__ . '/admin/settings.php';

// Bootstrap for the plugin
add_action('init', 'dscpf_run');
function dscpf_run(){
	if ( is_admin()  && current_user_can( 'manage_options')){
		$DSCPF_Settings =  new DSCPF_Settings();
	}
	add_action('init', 'dscpf_add_rewrite_rules', 100);
	add_filter( 'term_link', 'dscpf_new_permalinks', 10, 3 );
	add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'dscpf_add_action_links' );
}

// Add rewrite rules for new permalinks
function dscpf_add_rewrite_rules(){
	$PREFIX_PERMALINK = get_option('category_base') ? get_option('category_base') : 'category';
	$cats = get_categories();
	
	foreach($cats as $cat){
		if($cat->parent != 0 && strpos($cat->slug, '-') != false){
			$splitted_slug = explode("-", $cat->slug);
			$new_permalink = "/";
			$slugs	= array_reverse($splitted_slug);
			for($i =0; $i < count($slugs); $i++){$new_permalink .= $slugs[$i] . "/";}
			// Add duplicated subcategories rewrite rules
			add_rewrite_rule($PREFIX_PERMALINK . $new_permalink .'?$', 'index.php?cat='.$cat->term_id ,'top');
			// Add paged rewrite rules support
			add_rewrite_rule($PREFIX_PERMALINK.$new_permalink .'page/([0-9]{1,})/?$', 'index.php?cat='.$cat->term_id.'&paged=$matches[1]','top');
		}
	}	
}

// Generate new permalinks
function dscpf_new_permalinks( $permalink, $term, $taxonomy ){
	if ($term->taxonomy == "category"){
		$slugs 		= str_replace(  home_url() , '' , $permalink);
		$slugs 		= trim($slugs, "/");
		$slugs 		= explode("/",$slugs);
		$new_permalink 	= home_url();

		foreach($slugs as $slug){
			if(strpos($slug, '-') != false){
				$splitted_slug = explode("-", $slug);	
				$new_permalink .= "/" . $splitted_slug[0];
			}else{
				$new_permalink .= "/" . $slug;
			}
		}
		return $new_permalink;
	}
	return $permalink;   
}

// Link to settings page from plugins screen
function dscpf_add_action_links ( $links ) {
    $mylinks = array(
        '<a href="' . admin_url( 'tools.php?page=dscpf-settings' ) . '">Settings</a>',
    );
    return array_merge( $links, $mylinks );
}

// Plugin activation
register_activation_hook( __FILE__, 'dscpf_activate');
function dscpf_activate(){
	
}

// Plugin activation
register_deactivation_hook( __FILE__, 'dscpf_deactivate');
function dscpf_deactivate(){

}

// plugin uninstallation
register_uninstall_hook( __FILE__, 'dscpf_uninstall'); 
function dscpf_uninstall(){
	delete_option(DSCPF_Settings::DSCPF_OPTION_NAME);
}

/*
// Maybe usefull later on future development
//add_filter( 'user_trailingslashit', 'dscpf_remove_category', 100, 2 );
function dscpf_remove_category( $string, $type ) {
    if ( 'single' !== $type && 'category' === $type && false !== strpos( $string, 'category' ) ) {
        $url_without_category = str_replace( '/category/', '/', $string );
        return trailingslashit( $url_without_category );
    }
    return $string;
}    
*/