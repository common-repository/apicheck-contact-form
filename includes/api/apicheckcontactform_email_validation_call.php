<?php

add_action('wp_ajax_apicheckcontactform_email_validation_call', 'apicheckcontactform_email_validation_call');
add_action('wp_ajax_nopriv_apicheckcontactform_email_validation_call', 'apicheckcontactform_email_validation_call');

function apicheckcontactform_email_validation_call()
{
    $api_key = get_option('apicheckcontactform_api_key');
    $email_validation_enabled = get_option('apicheckcontactform_validate_email', false);

    // Early return if email validation is disabled or email is not set in POST
    if (!$email_validation_enabled || !isset($_POST['email'])) {
        wp_send_json_error('Email validation is disabled or email is not provided.', 200);
        exit;
    }

    // Sanitize email input
    $email = sanitize_email($_POST['email']);

    $response = make_email_validation_api_call($email, $api_key);

    // Handle the API response
    if (is_wp_error($response)) {
        wp_send_json_error('Error validating email address.', 500);
        return;
    }

    $response_body = wp_remote_retrieve_body($response);
    $response_code = wp_remote_retrieve_response_code($response);
    $result = json_decode($response_body);

    wp_send_json_success($result);
}

function make_email_validation_api_call($email, $api_key)
{
    $url = 'https://api.apicheck.nl/verify/v1/email?email=' . $email;
    $args = [
        'headers' => [
            'origin' => site_url(),
            'x-api-key' => $api_key,
            'x-application-info' => APICHECKCONTACTFORM_SHORT_NAME . "@" . APICHECKCONTACTFORM_VERSION,
        ],
        'timeout' => 10, // Reduce the timeout
        'body' => ['email' => $email],
    ];
    return wp_remote_request($url, $args);
}
