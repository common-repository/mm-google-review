<?php 
/*
Register Post Type
*/
class MM_GoogleReviewsPostType{

	public function __construct(){

		add_action('init', [$this, 'registerPostType']);
	}

	public function registerPostType(){
		$labels = array(
			        'name'                  => _x( 'Google Reviews', 'Post type general name', 'mm-google-review' ),
			        'singular_name'         => _x( 'Google Review', 'Post type singular name', 'mm-google-review' ),
			        'menu_name'             => _x( 'Google Reviews', 'Admin Menu text', 'mm-google-review' ),
			        'name_admin_bar'        => _x( 'Google Reviews', 'Add New on Toolbar', 'mm-google-review' ),
			        'add_new'               => __( 'Add New', 'mm-google-review' ),
			        'add_new_item'          => __( 'Add New', 'mm-google-review' ),
			        'new_item'              => __( 'New Review', 'mm-google-review' ),
			        'edit_item'             => __( 'Edit Review', 'mm-google-review' ),
			        'view_item'             => __( 'View Review', 'mm-google-review' ),
			        'all_items'             => __( 'All Reviews', 'mm-google-review' ),
			        'search_items'          => __( 'Search', 'mm-google-review' ),
			        'parent_item_colon'     => __( 'Parent:', 'mm-google-review' ),
			        'not_found'             => __( 'Not found.', 'mm-google-review' ),
			        'not_found_in_trash'    => __( 'Not found in Trash.', 'mm-google-review' ),
    			);
 
			    $args = array(
			        'labels'             => $labels,
			        'public'             => true,
			        'publicly_queryable' => true,
			        'show_ui'            => true,
			        'show_in_menu'       => true,
			        'query_var'          => true,
			        'rewrite'            => array( 'slug' => 'mm_google_reviews' ),
			        'capability_type'    => 'post',
			        'has_archive'        => true,
			        'hierarchical'       => false,
			        'menu_position'      => null,
			        'supports'           => array( 'title', 'editor','thumbnail'),
			    );
		register_post_type( 'google_reviews', $args);
	}
}
?>