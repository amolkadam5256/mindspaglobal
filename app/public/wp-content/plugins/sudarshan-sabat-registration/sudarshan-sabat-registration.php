<?php
/**
 * Plugin Name: Dr. Dipti Panhalkar Session Registration Form
 * Plugin URI:  https://mindspaglobal.com
 * Description: Single contact-section session form delivered through Web3Forms, with WhatsApp follow-up links.
 * Version:     1.0.9
 * Author:      Mindspaglobal
 * License:     GPL v2 or later
 * Text Domain: ss-registration
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'SS_REG_VERSION', '1.0.9' );
define( 'SS_REG_DIR', plugin_dir_path( __FILE__ ) );
define( 'SS_REG_URL', plugin_dir_url( __FILE__ ) );
define( 'SS_WEB3FORMS_ACCESS_KEY', 'f1a6a76e-5680-43f4-a8e9-d630f72a65e1' );
define( 'SS_WEB3FORMS_SUBMIT_URL', 'https://api.web3forms.com/submit' );
define( 'SS_WHATSAPP_GROUP_URL', 'https://chat.whatsapp.com/EjCpcY8EyGx3W92Yi8ZIjq?s=cl&p=a&mlu=4&ilr=4' );
define( 'SS_WHATSAPP_CHAT_URL', 'https://wa.me/919325214189?text=Hi%20Dipti%21%20I%20have%20a%20question%20about%20the%201%3A1%20Neuro%20Mind%20Session.' );
define( 'SS_WHATSAPP_URL', SS_WHATSAPP_GROUP_URL ); // backward-compat alias

/**
 * Enqueue plugin assets on frontend.
 */
function ss_reg_enqueue_assets() {
    $needs_registration_assets = is_front_page() || is_page( 1032 );

    if ( ! $needs_registration_assets ) {
        global $post;
        $needs_registration_assets = isset( $post ) && $post instanceof WP_Post && has_shortcode( $post->post_content, 'ss_registration_form' );
    }

    if ( ! $needs_registration_assets ) {
        return;
    }

    wp_enqueue_style(
        'ss-registration',
        SS_REG_URL . 'assets/css/registration.css',
        array(),
        SS_REG_VERSION
    );

    wp_enqueue_script(
        'ss-registration',
        SS_REG_URL . 'assets/js/registration.js',
        array( 'jquery' ),
        SS_REG_VERSION,
        true
    );

    wp_localize_script( 'ss-registration', 'ssRegData', array(
        'ajaxUrl'         => admin_url( 'admin-ajax.php' ),
        'nonce'           => wp_create_nonce( 'ss_registration_nonce' ),
        'whatsappUrl'     => SS_WHATSAPP_URL,
        'whatsappChatUrl' => SS_WHATSAPP_CHAT_URL,
        'submitting'      => esc_html__( 'Submitting…', 'ss-registration' ),
        'submitText'      => esc_html__( 'SUBMIT', 'ss-registration' ),
        'errorMsg'        => esc_html__( 'Something went wrong. Please try again.', 'ss-registration' ),
    ) );
}
add_action( 'wp_enqueue_scripts', 'ss_reg_enqueue_assets' );

/**
 * Register the [ss_registration_form] shortcode.
 */
function ss_reg_form_shortcode( $atts ) {
    ob_start();
    require SS_REG_DIR . 'templates/form.php';
    return ob_get_clean();
}
add_shortcode( 'ss_registration_form', 'ss_reg_form_shortcode' );

/**
 * AJAX handler — processes the registration form submission.
 * Runs for both logged-in and guest users.
 */
function ss_reg_handle_submission() {
    // Verify nonce.
    if ( ! isset( $_POST['ss_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ss_nonce'] ) ), 'ss_registration_nonce' ) ) {
        wp_send_json_error( array( 'message' => esc_html__( 'Security check failed. Please refresh the page and try again.', 'ss-registration' ) ), 403 );
    }

    // Sanitize & validate inputs.
    $full_name   = sanitize_text_field( wp_unslash( $_POST['full_name'] ?? '' ) );
    $phone       = sanitize_text_field( wp_unslash( $_POST['phone'] ?? '' ) );
    $profession  = sanitize_text_field( wp_unslash( $_POST['profession'] ?? '' ) );
    $location    = sanitize_text_field( wp_unslash( $_POST['location'] ?? '' ) );
    $seriousness = sanitize_text_field( wp_unslash( $_POST['seriousness'] ?? '' ) );

    $errors = array();

    if ( empty( $full_name ) ) {
        $errors[] = esc_html__( 'Full Name is required.', 'ss-registration' );
    }

    if ( empty( $phone ) || ! preg_match( '/^\+?[0-9\s\-\(\)]{7,20}$/', $phone ) ) {
        $errors[] = esc_html__( 'A valid Phone Number is required.', 'ss-registration' );
    }

    $allowed_professions = array( 'Business Owner', 'Working Professional', 'Trainer or Coach', 'Student', 'Entrepreneur', 'Freelancer', 'Other' );
    if ( empty( $profession ) || ! in_array( $profession, $allowed_professions, true ) ) {
        $errors[] = esc_html__( 'Please select a valid Profession.', 'ss-registration' );
    }

    if ( empty( $location ) ) {
        $errors[] = esc_html__( 'Your Location is required.', 'ss-registration' );
    }

    $allowed_seriousness = array( 'YES', 'NO', 'OTHER' );
    if ( empty( $seriousness ) || ! in_array( $seriousness, $allowed_seriousness, true ) ) {
        $errors[] = esc_html__( 'Please select your level of seriousness.', 'ss-registration' );
    }

    if ( ! empty( $errors ) ) {
        wp_send_json_error( array( 'message' => implode( ' ', $errors ) ), 422 );
    }

    // Deliver the registration through the Web3Forms endpoint configured by the site owner.
    $web3forms_response = wp_remote_post( SS_WEB3FORMS_SUBMIT_URL, array(
        'timeout' => 20,
        'headers' => array( 'Content-Type' => 'application/json; charset=utf-8' ),
        'body'    => wp_json_encode( array(
            'access_key' => SS_WEB3FORMS_ACCESS_KEY,
            'subject'    => sprintf( 'New session enquiry — %s', $full_name ),
            'from_name'  => get_bloginfo( 'name' ),
            'name'       => $full_name,
            'phone'      => $phone,
            'profession' => $profession,
            'location'   => $location,
            'seriousness'=> $seriousness,
        ) ),
    ) );

    if ( is_wp_error( $web3forms_response ) ) {
        wp_send_json_error( array( 'message' => esc_html__( 'Your details could not be submitted right now. Please try again.', 'ss-registration' ) ), 502 );
    }

    $web3forms_status = wp_remote_retrieve_response_code( $web3forms_response );
    $web3forms_body   = json_decode( wp_remote_retrieve_body( $web3forms_response ), true );
    if ( $web3forms_status < 200 || $web3forms_status >= 300 || empty( $web3forms_body['success'] ) ) {
        wp_send_json_error( array( 'message' => esc_html__( 'Your details could not be submitted right now. Please try again.', 'ss-registration' ) ), 502 );
    }

    wp_send_json_success( array(
        'message'     => esc_html__( 'Thank you for submitting this form!', 'ss-registration' ),
        'whatsappUrl' => SS_WHATSAPP_URL,
    ) );
}
add_action( 'wp_ajax_ss_registration_submit', 'ss_reg_handle_submission' );
add_action( 'wp_ajax_nopriv_ss_registration_submit', 'ss_reg_handle_submission' );


