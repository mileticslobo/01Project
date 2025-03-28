<?php
if (!defined('ABSPATH')) {
    exit;
}

function chatgpt_get_response($message) {
    $api_key = get_option('chatgpt_api_key');

    if (!$api_key) {
        return 'API ključ nije podešen!';
    }

    $url = 'https://api.openai.com/v1/chat/completions';

    $data = array(
        'model' => 'gpt-4',
        'messages' => array(
            array('role' => 'system', 'content' => 'Ti si korisnička podrška.'),
            array('role' => 'user', 'content' => $message)
        ),
        'temperature' => 0.7
    );

    $args = array(
        'body'    => json_encode($data),
        'headers' => array(
            'Content-Type'  => 'application/json',
            'Authorization' => 'Bearer ' . $api_key
        ),
        'method'  => 'POST'
    );

    $response = wp_remote_post($url, $args);

    if (is_wp_error($response)) {
        return 'Greška u komunikaciji sa API-jem!';
    }

    $body = wp_remote_retrieve_body($response);
    $result = json_decode($body, true);

    return $result['choices'][0]['message']['content'] ?? 'Nema odgovora!';
}

// AJAX handler
function chatgpt_ajax_handler() {
    if (!isset($_POST['message'])) {
        wp_send_json_error('Nema poruke!');
    }

    $response = chatgpt_get_response(sanitize_text_field($_POST['message']));
    wp_send_json_success($response);
}
add_action('wp_ajax_chatgpt_request', 'chatgpt_ajax_handler');
add_action('wp_ajax_nopriv_chatgpt_request', 'chatgpt_ajax_handler');