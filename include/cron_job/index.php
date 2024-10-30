<?php 
if (!defined('ABSPATH')) exit;

class MM_GoogleReview_Cron {

    public function __construct() {
        add_filter('cron_schedules', array($this, 'GoogleReview_intervals'));
        add_action( 'wp',  array($this, 'GoogleReview_scheduler'));
        add_action( 'GoogleReview_schedule_event', array( $this, 'GoogleReview_schedule_event_function' ) );
    }

    public function GoogleReview_intervals($schedules)
    {
        $schedules['GoogleReview_everyday'] = array(
            'interval' => 86400,
            'display' => 'Once a day to get Google Reviews.'
        );
        return $schedules;
    }

    function GoogleReview_scheduler() {
        if ( ! wp_next_scheduled( 'GoogleReview_schedule_event' ) ) {
            $time = strtotime('today'); //returns today midnight
            // $time = $time + 36000;
            //strtotime('16:20:00');
            wp_schedule_event( $time, 'GoogleReview_everyday', 'GoogleReview_schedule_event');
        }
    }

    function GoogleReview_schedule_event_function(){
        $getapiplacekeys = get_option( 'google_reviews_name' );
        $apikey = $getapiplacekeys['google_reviews_api_key'];
        $placeid = $getapiplacekeys['google_reviews_place_id'];
        if (empty($apikey) || empty($placeid)) {
            update_option('review_cron_status', 'API Key And Place Id Is Required');
            exit();
        }
        $getreviews = wp_remote_retrieve_body( wp_remote_get('https://maps.googleapis.com/maps/api/place/details/json?place_id='.$placeid.'&key='.$apikey.'&reviews_sort=newest'));
        $getalldata = json_decode($getreviews, true);
        if ($getalldata['status'] == 'OK') {
            $getallreviews = $getalldata['result']['reviews'];
            if (!empty($getallreviews)) {
                foreach ($getallreviews as $reviewkey => $reviewvalue) {
                    $reviewtitle = $reviewvalue['author_name'];
                    if (!post_exists($reviewtitle)) {
                        // insert new review
                        $review_data = array(
                            'post_title' => $reviewtitle,
                            'post_content' => $reviewvalue['text'],
                            'post_status'   => 'publish',
                            'post_type' => 'google_reviews',
                        );
                        $review_id = wp_insert_post( $review_data );
                        // update review post meta
                        update_post_meta( $review_id, 'author_url', sanitize_url($reviewvalue['author_url']));
                        update_post_meta( $review_id, 'rating', sanitize_text_field($reviewvalue['rating']));
                        update_post_meta( $review_id, 'rating_time', sanitize_text_field($reviewvalue['time']));

                        // Add Featured Image to Post
                        $image_url        = $reviewvalue['profile_photo_url'];
                        $imagename =  str_replace(" ", "-", $reviewtitle);
                        $image_name       = $imagename.'.png';
                        $upload_dir       = wp_upload_dir(); // Set upload folder
                        $image_data       = file_get_contents($image_url); // Get image data
                        $unique_file_name = wp_unique_filename( $upload_dir['path'], $image_name ); // Generate unique name
                        $filename         = basename( $unique_file_name ); // Create image file name

                        // Check folder permission and define file location
                        if( wp_mkdir_p( $upload_dir['path'] ) ) {
                          $file = $upload_dir['path'] . '/' . $filename;
                        } else {
                          $file = $upload_dir['basedir'] . '/' . $filename;
                        }

                        // Create the image  file on the server
                        file_put_contents( $file, $image_data );

                        // Check image file type
                        $wp_filetype = wp_check_filetype( $filename, null );

                        // Set attachment data
                        $attachment = array(
                            'post_mime_type' => $wp_filetype['type'],
                            'post_title'     => sanitize_file_name( $filename ),
                            'post_content'   => '',
                            'post_status'    => 'inherit'
                        );

                        // Create the attachment
                        $attach_id = wp_insert_attachment( $attachment, $file, $review_id );

                        // Include image.php
                        require_once(ABSPATH . 'wp-admin/includes/image.php');

                        // Define attachment metadata
                        $attach_data = wp_generate_attachment_metadata( $attach_id, $file );

                        // Assign metadata to attachment
                        wp_update_attachment_metadata( $attach_id, $attach_data );

                        // And finally assign featured image to post
                        set_post_thumbnail( $review_id, $attach_id );
                    }
                }
            }
            update_option('review_cron_status', 'OK');
        }else{
            update_option('review_cron_status', $getalldata['error_message']);
        }
    }
}


new MM_GoogleReview_Cron();