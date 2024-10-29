<?php

add_action('wp_ajax_apicheckcontactform_lookup_call', 'apicheckcontactform_lookup_call');
add_action('wp_ajax_nopriv_apicheckcontactform_lookup_call', 'apicheckcontactform_lookup_call');

function apicheckcontactform_lookup_call()
{
    $api_key = get_option('apicheckcontactform_api_key');
    $country = filter_input(INPUT_POST, 'country', FILTER_SANITIZE_STRING);

    if (!$country) {
        wp_send_json_error('Country not set', 400);
        return;
    }

    $params = array_intersect_key($_POST, array_flip(get_allowed_params()));

    foreach ($params as $key => &$value) {
        if (empty($value) && $key === 'numberAddition') {
            unset($params[$key]);
        } else {
            $value = is_numeric($value) ? intval($value) : sanitize_text_field($value);
        }
    }

    $url = 'https://api.apicheck.nl/lookup/v1/postalcode/' . strtolower($country) . '?' . http_build_query($params);
    $args = [
        'headers' => [
            'origin' => site_url(),
            'x-api-key' => $api_key,
            'x-application-info' => APICHECKCONTACTFORM_SHORT_NAME . "@" . APICHECKCONTACTFORM_VERSION
        ],
        'timeout' => 600
    ];

    $response = wp_remote_request($url, $args);
    if (is_wp_error($response)) {
        wp_send_json_error($response->get_error_message(), 500);
        return;
    }

    $response_body = wp_remote_retrieve_body($response);
    $response_code = wp_remote_retrieve_response_code($response);
    $result = json_decode($response_body);
    
    wp_send_json_success($result);
}

function get_allowed_params()
{
    return [
        'postalcode', 'street', 'numberAddition', 'municipality', 'boxNumber',
        'postalcode_id', 'street_id', 'number',
    ];
}
