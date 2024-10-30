<?php 
/**
 * ShortCode For Show Data
 */
class MM_GoogleReview_shortcode
{
	
	function __construct()
	{
		add_shortcode( 'mm_google_review', [$this, 'google_review_shortcode_func'] );
	}

	public function google_review_shortcode_func( $atts ) {
    $atts = shortcode_atts( array(
        'foo' => 'no foo'
    ), $atts, 'Google_Review' );
 	ob_start();
 	$args = array(
  		'numberposts' => -1,
  		'post_type'   => 'google_reviews'
	);
	$google_reviews = get_posts( $args ); ?>
    <div id="google_review_carousel">
		<div class="single-item">
			<?php if (!empty($google_reviews)) {
				foreach ($google_reviews as $google_review) {?>
					<div class="review_item">
						<div class="review_content">
							<?php echo esc_attr( $google_review->post_content); ?>		
						</div>
						<div class="item_rating">
							<?php 
							$x = 1;
							$getactualreating = get_post_meta($google_review->ID, 'rating', true);
							while($x <= 5) {
								if ($x <= $getactualreating) {
									echo '<span class="fa fa-star checked"></span>';
								}else{
									echo '<span class="fa fa-star"></span>';
								}
							  	$x++;
							}
							?>	 
						</div>
						<div class="item_author">
							<?php $featured_img_url = get_the_post_thumbnail_url($google_review->ID,'full');  ?>
							<img src="<?php echo esc_attr($featured_img_url); ?>">
							<span><?php echo esc_attr($google_review->post_title); ?> <br/> <?php echo date('F j, Y', get_post_meta($google_review->ID, 'rating_time', true)); ?></span>
						</div>
					</div>
				<?php }
			} ?>
		</div>
	</div>
	<style type="text/css">
	.slick-prev{
	    position: absolute;
	    left: -50px;
	    top: 50%;
	    transform: translateY(-50%);
	   	text-indent: 99999999px;
		background-color: transparent;
		background-image: url('<?php echo MM_GoogleReview_ASSETS; ?>/img/chevron-left.png');
		width: 50px;
		height: 50px;
		z-index: 99999;
        cursor: pointer;
        border: 0px;
	}
	.slick-next{
	    position: absolute;
	    right: -50px;
	    top: 50%;
	    transform: translateY(-50%);
	   	text-indent: 99999999px;
		background-color: transparent;
		background-image: url('<?php echo MM_GoogleReview_ASSETS; ?>/img/chevron-right.png');
		width: 50px;
		height: 50px;
		z-index: 99999;	
        border: 0px;
        cursor: pointer;		
	}
	.slick-next:hover, .slick-prev:hover, .slick-next:focus, .slick-prev:focus{
		background-color: transparent;
	}
	.slick-dots{

	}
	.slick-dots li {
		display: inline-block;
	}
	</style>
    <?php return ob_get_clean();
	}
}