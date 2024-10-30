<?php 
/*
* Plugin Admin Option
*/
class MM_GoogleReviews_SettingsPage
{
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

    /**
     * Start up
     */
    public function __construct()
    {
        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'page_init' ) );
    }

    /**
     * Add options page
     */
    public function add_plugin_page()
    {
        // This page will be under "Settings"
        add_submenu_page(
            'options-general.php',
            'Settings Admin', 
            'Google Review', 
            'manage_options', 
            'google_reviews-setting', 
            array( $this, 'create_admin_page' )
        );
    }

    /**
     * Options page callback
     */
    public function create_admin_page()
    {   
        // On Place ID Update Fatch Reviews
        if (isset($_POST['google_reviews_name'])){
            $this->get_google_reviews();
        }
        // Set class property
        $this->options = get_option( 'google_reviews_name' );
        ?>
        <div class="wrap">
            <h1>Settings</h1>
            <form method="post" action="options.php">
            <?php
                // This prints out all hidden setting fields
                settings_fields( 'google_reviews_group' );
                do_settings_sections( 'google_reviews-setting' );
                submit_button();
                
                echo "<pre>";
                print_r(get_option('review_cron_status'));
                echo "</pre>";
            ?>
            </form>
        </div>
        <?php
    }

    /**
     * Register and add settings
     */
    public function page_init()
    {        
        register_setting(
            'google_reviews_group', // Option group
            'google_reviews_name', // Option name
            array( $this, 'sanitize' ) // Sanitize
        );

        add_settings_section(
            'setting_section_id', // ID
            'Settings', // Title
            array( $this, 'print_section_info' ), // Callback
            'google_reviews-setting' // Page
        );  

        add_settings_field(
            'google_reviews_api_key', // ID
            'Google API Key', // Title 
            array( $this, 'google_reviews_api_key_final_callback' ), // Callback
            'google_reviews-setting', // Page
            'setting_section_id' // Section           
        );

        add_settings_field(
            'google_reviews_place_id', // ID
            'Google Review Place ID', // Title 
            array( $this, 'google_reviews_place_id_final_callback' ), // Callback
            'google_reviews-setting', // Page
            'setting_section_id' // Section           
        );                             
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize( $input )
    {
        $new_input = array();
        if( isset( $input['google_reviews_api_key'] ) )
            $new_input['google_reviews_api_key'] = sanitize_text_field($input['google_reviews_api_key']);
        if( isset( $input['google_reviews_place_id'] ) )
            $new_input['google_reviews_place_id'] = sanitize_text_field($input['google_reviews_place_id']);
        return $new_input;
    }

    /** 
     * Print the Section text
     */
    public function print_section_info()
    {
        print 'Enter your settings below:';
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function google_reviews_api_key_final_callback()
    {
        printf(
            '<input type="text" id="google_reviews_api_key" name="google_reviews_name[google_reviews_api_key]" value="%s" />',
            isset( $this->options['google_reviews_api_key'] ) ? esc_attr( $this->options['google_reviews_api_key']) : ''
        );
    }

    public function google_reviews_place_id_final_callback()
    {
        printf(
            '<input type="text" id="google_reviews_place_id" name="google_reviews_name[google_reviews_place_id]" value="%s" />',
            isset( $this->options['google_reviews_place_id'] ) ? esc_attr( $this->options['google_reviews_place_id']) : ''
        );
    }

    public function get_google_reviews(){
        $getapiplacekeys = get_option( 'google_reviews_name' );
        $apikey = $getapiplacekeys['google_reviews_api_key'];
        $placeid = $getapiplacekeys['google_reviews_place_id'];
        if (empty($apikey) || empty($placeid)) {
            update_option('review_cron_status', 'API Key And Place Id Is Required');
            exit();
        }
        $getreviews =  wp_remote_retrieve_body( wp_remote_get('https://maps.googleapis.com/maps/api/place/details/json?place_id='.$placeid.'&key='.$apikey.'&reviews_sort=newest'));
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
            update_option('review_cron_status', sanitize_text_field($getalldata['error_message']));
        }
    }         
}