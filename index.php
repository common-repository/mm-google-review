<?php
/*
Plugin Name: MM Google Review
Plugin URI: https://thedailywp.com/
Description: Show your Google Review on your Website
Version: 1.0
Author: Hamidul
Author URI: https://profiles.wordpress.org/studentbd/
Text Domain: mm-google-review
*/

if(!defined('ABSPATH')){
	exit;
}

require_once "include/index.php";

/**
 * The Main Class Of Plugin
 */
final class MM_GoogleReview
{
	// Class construction
	private function __construct()
	{
		$this->define_function();

		add_action('plugins_loaded', [$this, 'init_plugin']);
		/**
 			* Load plugin textdomain.
		*/
		add_action( 'init', [$this, 'MM_GoogleReview_load_textdomain'] );		

		add_action('wp_enqueue_scripts', [$this, 'mm_google_review_scripts']);
	}

	/*
		Single instence 
	*/
	public static function init(){
		static $instance = false;

		if (!$instance) {
			$instance = new self();
		}

		return $instance;
	}


	public function define_function(){
		define("MM_GoogleReview_FILE", __FILE__);
		define("MM_GoogleReview_PATH", __DIR__);
		define("MM_GoogleReview_URL", plugins_url('', MM_GoogleReview_FILE));
		define("MM_GoogleReview_ASSETS", MM_GoogleReview_URL.'/assets');
	}

	public function init_plugin(){
		new MM_GoogleReviews_SettingsPage();						
		new MM_GoogleReviewsPostType();						
		new MM_GoogleReview_shortcode();						
	}

	public function MM_GoogleReview_load_textdomain() {
	  load_plugin_textdomain( 'mm-google-review', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' ); 
	}

	public function mm_google_review_scripts(){
		wp_enqueue_script( 'slick_js', plugins_url( 'assets/js/slick.min.js', __FILE__ ), array('jquery'), '1.8', true );
		wp_enqueue_script( 'google_review', plugins_url( 'assets/js/google_review.js', __FILE__ ), array('jquery'), '1.0', true );
		wp_enqueue_style( 'slick_css', plugins_url( 'assets/css/slick.css', __FILE__ ));		
		wp_enqueue_style( 'font_awesome', plugins_url( 'assets/css/font-awesome.min.css', __FILE__ ));
		wp_enqueue_style( 'google_review_style', 	plugins_url( 'assets/css/google_review.css', __FILE__ ) );
	}	
}

/*
Initialize the main plugin
*/
function MM_GoogleReview_init(){
	return MM_GoogleReview::init();
}

/*
Active Plugin
*/
MM_GoogleReview_init();