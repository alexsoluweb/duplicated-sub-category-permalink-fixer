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
* Version:           1.0.0
* Author:            Alexsoluweb
* Author URI:        alexsoluweb.digital
* License:           GPL-2.0+
* License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
* Text Domain:       DSCPF	
**/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define('PREFIX_PLUGIN', 'dscpf');
require_once __DIR__ . '/admin/settings.php';

function dscpf_run(){
	if ( is_admin()  && current_user_can( 'manage_options')){
		$configuration = new DSCPF_Settings();
	}
	add_action('init', 'dscpf_add_rewrite_rules', 100);
	add_filter( 'term_link', 'dscpf_new_permalinks', 10, 3 );
}

function dscpf_add_rewrite_rules(){
	$prefix = get_option( 'dscpf_option_name' )["permalink_prefix"];
	$PREFIX_PERMALINK = isset($prefix)? $prefix : "";

	$cats = get_categories();
	
	foreach($cats as $cat){
		if($cat->parent != 0 && strpos($cat->slug, '-') != false){
				$splitted_slug = explode("-", $cat->slug);
			$new_permalink = "/";
			$slugs	= array_reverse($splitted_slug);
			for($i =0; $i < count($slugs); $i++){$new_permalink .= $slugs[$i] . "/";}
			add_rewrite_rule($PREFIX_PERMALINK . $new_permalink .'?$', 'index.php?cat='.$cat->term_id ,'top');
		}
	}	
}

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


add_action('init', 'dscpf_run');
