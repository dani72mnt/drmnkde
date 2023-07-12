<?php
/**
 * @package Darmankade
 * @version 1.0.0
 */
/*
Plugin Name: Darmankade
Description: This product is an exclusive plugin designed by <a href="https://instagram.com/vishar.web">Danial Montazeri</a>.
Author: Danial Montazeri
Version: 1.0.0
Author URI: https://instagram.com/vishar.web
 */

// Init Session
function wpse16119876_init_session()
{
	if (session_status() !== PHP_SESSION_ACTIVE) {
		@session_start();
	}
}
// Start session on init hook.
add_action('init', 'wpse16119876_init_session');

/**
 * Enqueue scripts and styles.
 */
add_action('wp_enqueue_scripts', 'theme_external_styles');
function theme_external_styles()
{
	wp_enqueue_script('main_js_darmankade', plugin_dir_url(__DIR__) . 'darmankade/assets/js/main.js', array('jquery'));
	wp_enqueue_style('style_css_darmankade', plugin_dir_url(__DIR__) . 'darmankade/assets/css/style.css', array());

    // Set AJAX URL
    wp_localize_script( 'main_js_darmankade', 'darmankade_plugin_ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );

}

/**
 * Display Doctors Data as Shortcode
 * Usage: [shortcode-dr id=dr_id]
 */
function fetch_doctor_data($atts) {
    // Extract shortcode attributes
    $atts = shortcode_atts(array(
        'id' => '',
    ), $atts, 'shortcode-dr');

    // Check if id attribute is provided
    if (empty($atts['id'])) {
        return 'Please provide a valid id attribute.';
    }

    // Prepare API URL with the provided id
    $api_url = 'https://gw.drmnkde.ir/Providers/GetProviderById?id=' . $atts['id'];

    // Make a request to the API
    $response = wp_remote_get($api_url);

    // Check if the request was successful
    if (is_wp_error($response)) {
        return 'Error retrieving doctor data.';
    }

    // Get the API response body
    $body = wp_remote_retrieve_body($response);

    // Parse the JSON response
    $data = json_decode($body, true);

    // Check if the API response contains doctor data
    if (empty($data)) {
        return 'Doctor data not found.';
    }

    // Extract the doctor details from the API response
    $doctor_image = $data['image'];
    $doctor_name = $data['firstName'];
    $doctor_expertise = $data['fieldName'];

    // Create the doctor information HTML
    $output = '<div class="card doctor-profile text-center bg-primary text-white p-3 w-sm-25 mx-auto">';
    $output .= '<img class="card-img-top rounded-circle w-sm-25 w-50 mx-auto" src="https://www.darmankade.com/UploadFiles/Doctor/' . $doctor_image . '" alt="' . $doctor_name . '">';
    $output .= '<div class="card-body">';
    $output .= '<h3 class="card-title">' . $doctor_name . '</h3>';
    $output .= '<p class="card-text">' . $doctor_expertise . '</p>';
    $output .= '<a href="https://drmnkde.ir/doctor/' . $atts['id'] . '" class="btn btn-warning w-100 rounded-5">دریافت نوبت</a>';
    $output .= '</div>';
    $output .= '</div>';

    return $output;
}
add_shortcode('shortcode-dr', 'fetch_doctor_data');

/**
 * Create doctor profile preview page
 * to display this page just using site_url . '/doctor-profile-preview/'
*/
function create_product_link_form_page() {
    $page_slug = 'doctor-profile-preview';
    $page_title = 'نمایش پروفایل پزشک';

    // Check if the page exists
    $page = get_page_by_path($page_slug);

    // If the page doesn't exist, create it
    if (!$page) {
        // Set the page attributes
        $page_attributes = array(
            'post_title'    => $page_title,
            'post_name'     => $page_slug,
            'post_status'   => 'publish',
            'post_type'     => 'page',
        );

        // Insert the page into the database
        $page_id = wp_insert_post($page_attributes);

        // Add the shortcode to the page content
        $shortcode = '[shortcode-dr id=373]';
		$description = '';
		$page_content = $description;
        $page_content .= '<br><hr>';
        $page_content .= $shortcode;
        $page_content .= '<br>';

        // Update the page content
        $page_attributes['ID'] = $page_id;
        $page_attributes['post_content'] = $page_content;
        wp_update_post($page_attributes);

        // Optionally, set a custom template for the page
        // $template = 'path/to/your/custom-template.php';
        // update_post_meta($page_id, '_wp_page_template', $template);
    }
}
// Hook the function to a specific action
add_action('init', 'create_product_link_form_page');