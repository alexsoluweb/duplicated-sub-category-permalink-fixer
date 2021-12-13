<?php
/*
* ###########################################################################
* 				   DESCRIPTION
* ###########################################################################
*
* Not duplicated categories example:
* 				One hierarchical: 		category/maincat1/
* 				Two hierarchical: 		category/maincat1/subcat
* 				Three hierarchical: 		category/maincat1/subcat/subsubcat
* Duplicated categories structures slug must be like so:
* 				One hierarchical: 		category/maincat2/
* 				Two hierarchical: 		category/maincat2/subcat-maincat2
* 				Three hierarchical: 		category/maincat2/subcat-maincat2/subsubcat-subcat-maincat2
*
* New permalinks on duplicated categories will generate this:
* 				One hierarchical: 		category/maincat2/
* 				Two hierarchical: 		category/maincat2/subcat
* 				Three hierarchical: 		category/maincat2/subcat/subsubcat	
*/


add_action('init', 'asw_add_rewrite_rules');
function asw_add_rewrite_rules(){
	// ADJUST HERE THE PERMALINKS PREFIX
	$PREFIX_PERMALINK = "category";
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

add_filter( 'term_link', 'asw_new_permalinks', 10, 3 );
function asw_new_permalinks( $permalink, $term, $taxonomy ){	
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
	}
	return $new_permalink;    
}

add_filter('rewrite_rules_array', function($rules){
    do_action('logger', $rules);
    return $rules;
});
