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
*
* ###########################################################################
* 				   HOW TO USE
* ###########################################################################
*
* STEP 1) Add your duplicated sub categories in the array $ASW_CAT_SLUG_REWRITED
* 				ex: array("duplicated_slug", "new_permalink", "term_id"),
*				array("subcat-maincat2", "category/maincat2/subcat", "10"),
*
* STEP 2) Adjust the permalinks prefix in the variable $PREFIX_PERMALINK
*				Default prefix is "category" 
*				If there is no prefix, then put an empty string like so ""
*
* STEP 3) Go to Wordpress menu in settings/permalink and hit the save button to rewrite the rules of Wordpress.
*
* DONE!
*
* ##########################################################################
* 				   TODO 
* ##########################################################################
*
* Make this fully automated without adding duplicated categories manually
* 0) for each categories do:
* 1) find slugs that has "-"
* 2) construct new permalink
* 3) get the term id
*/


add_action('init', 'asw_add_rewrite_rules');
function asw_add_rewrite_rules(){
	// ADJUST HERE THE PERMALINKS PREFIX
	$PREFIX_PERMALINK = "category";
	$ASW_CAT_SLUG_REWRITED = array(
		// ADD HERE YOUR DUPLICATED SUB-CATEGORIES
		// array("duplicated_slug", "new_permalink", "slug_id"),
	);
	
	foreach($ASW_CAT_SLUG_REWRITED as $cat){
		add_rewrite_rule($PREFIX_PERMALINK . $cat[1] . '/?$', 'index.php?cat='.$cat[2] ,'top');
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
