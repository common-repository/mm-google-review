<?php abstract class MM_GoogleReview_Meta_Box {
 
 
    /**
     * Set up and add the meta box.
     */
    public static function add() {
        $screens = [ 'google_reviews' ];
        foreach ( $screens as $screen ) {
            add_meta_box(
                'google_reviews_meta_box_id',          // Unique ID
                'Google Review Details', // Box title
                [ self::class, 'html' ],   // Content callback, must be of type callable
                $screen                  // Post type
            );
        }
    }
 
 
    /**
     * Save the meta box selections.
     *
     * @param int $post_id  The post ID.
     */
    public static function save( int $post_id ) {
        if ( array_key_exists( 'author_url', $_POST ) ) {
            update_post_meta(
                $post_id,
                'author_url',
                sanitize_url($_POST['author_url'])
            );
        }
        if ( array_key_exists( 'rating', $_POST ) ) {
            update_post_meta(
                $post_id,
                'rating',
                sanitize_text_field($_POST['rating'])
            );
        }
        if ( array_key_exists( 'rating_time', $_POST ) ) {
            update_post_meta(
                $post_id,
                'rating_time',
                sanitize_text_field($_POST['rating_time'])
            );
        }
    }
 
 
    /**
     * Display the meta box HTML to the user.
     *
     * @param \WP_Post $post   Post object.
     */
    public static function html( $post ) {
        $review_author_url = get_post_meta( $post->ID, 'author_url', true );
        $review_rating = get_post_meta( $post->ID, 'rating', true );
        $review_date_and_time = get_post_meta( $post->ID, 'rating_time', true );
        ?>
        <style type="text/css">
            .field_div{margin-bottom: 15px;}
            .field_div label{width: 20%;display: inline-block;font-size: 15px;}
            .field_div input{margin-top: 10px;height: 35px;font-size: 15px;width: 78%;}
        </style>
        <div class="field_div">
            <label for="author_url">Review Author URL</label>
            <input type="text" name="author_url" value="<?php echo esc_url($review_author_url); ?>">       
        </div>
        <div class="field_div">
            <label for="rating">Review Rating</label>
            <input type="text" name="rating" value="<?php echo esc_html($review_rating); ?>">       
        </div>        
        <div class="field_div">
            <label for="rating_time">Review Date and Time</label>
            <input type="text" name="rating_time" value="<?php echo esc_html($review_date_and_time); ?>" >       
        </div>
        <?php
    }
}
 
add_action( 'add_meta_boxes', [ 'MM_GoogleReview_Meta_Box', 'add' ] );
add_action( 'save_post', [ 'MM_GoogleReview_Meta_Box', 'save' ] );