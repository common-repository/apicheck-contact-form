<?php

// Define constants for allowed AJAX fields and search types.
define('APICHECKCONTACTFORM_ALLOWED_AJAX_STRINGS_SEARCH', [
    'name', 'postalcode_id', 'street_id', 'number', 'numberAddition', 'searchType', 'country', 'action', 'query', 'city_id'
]);
define('APICHECKCONTACTFORM_ALLOWED_AJAX_INTS_SEARCH', [
    'number', 'name', 'limit'
]);
define('APICHECKCONTACTFORM_ALLOWED_SEARCH_TYPES_SEARCH', [
    'city', 'postalcode', 'street', 'address', 'global', 'address',
]);

// Register the AJAX actions for logged-in and non-logged-in users.
add_action('wp_ajax_apicheckcontactform_search_call', 'apicheckcontactform_search_call');
add_action('wp_ajax_nopriv_apicheckcontactform_search_call', 'apicheckcontactform_search_call');

/**
 * Handles the 'search' AJAX request.
 */
function apicheckcontactform_search_call() {
    try {
        $api_key = get_option('apicheckcontactform_api_key');
        validate_country($_POST);

        $country = strtolower(sanitize_text_field($_POST['country']));
        $search_type = validate_search_type($_POST['searchType']);

        // Prepare the request parameters.
        $params = prepare_request_params($_POST, APICHECKCONTACTFORM_ALLOWED_AJAX_STRINGS_SEARCH, APICHECKCONTACTFORM_ALLOWED_AJAX_INTS_SEARCH);
        
        // Build the API endpoint URL.
        $url = build_api_url('search', $search_type, $country, $params);
        $response = execute_api_call($url, $api_key);

        // Handle and respond with the API call results.
        handle_api_response($response);
    } catch (Exception $exception) {
        write_log($exception->getMessage());
        wp_send_json_error('An exception occurred.');
    }
}

/**
 * Validates the 'country' parameter and ensures it's supported.
 */
function validate_country($post_data) {
    if (!isset($post_data['country']) || !in_array(strtoupper($post_data['country']), APICHECKCONTACTFORM_SEARCH_COUNTRIES)) {
        wp_die('Invalid or unsupported country.');
    }
}

/**
 * Validates the 'searchType' parameter.
 */
function validate_search_type($search_type) {
    $search_type = sanitize_text_field($search_type);
    if (!in_array($search_type, APICHECKCONTACTFORM_ALLOWED_SEARCH_TYPES_SEARCH)) {
        wp_die('Invalid search type.');
    }
    return $search_type;
}

/**
 * Prepares the request parameters by sanitizing and ensuring they're allowed.
 */
function prepare_request_params($post_data, $allowed_strings, $allowed_ints) {
    $params = [];
    foreach ($post_data as $key => $value) {
        if (in_array(strtolower($key), array_map('strtolower', $allowed_strings))) {
            $params[$key] = sanitize_text_field($value);
        } elseif (in_array($key, $allowed_ints)) {
            $params[$key] = intval($value);
        }
    }
    return $params;
}

/**
 * Builds the full API endpoint URL.
 */
function build_api_url($type, $search_type, $country, $params) {
    return "https://api.apicheck.nl/{$type}/v1/{$search_type}/{$country}?" . http_build_query($params);
}

/**
 * Executes the API call and returns the response.
 */
function execute_api_call($url, $api_key) {
    $args = [
        'headers' => [
            'origin' => site_url(),
            'x-api-key' => $api_key,
            'x-application-info' => APICHECKCONTACTFORM_SHORT_NAME . "@" . APICHECKCONTACTFORM_VERSION
        ],
        'timeout' => 600
    ];

    return wp_remote_request($url, $args);
}

/**
 * Handles the API response and sends the appropriate JSON response.
 */
function handle_api_response($response) {
    if (is_wp_error($response)) {
        wp_send_json_error('An error occurred with the request.');
    }

    $body = wp_remote_retrieve_body($response);
    $result = json_decode($body);

    wp_send_json_success($result);
}
