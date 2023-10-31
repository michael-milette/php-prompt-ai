<?php
/**
 * promptAI function
 *
 * Example of how to use PHP to prompt gpt-3.5 language model using OpenAI API and get the result.
 *
 * @author Michael Milette
 * @copyright 2023 TNG Consulting Inc.
 * @link www.tngconsulting.ca
 * @version 0.3
 * @since 2023-08-13
 * @license GPL-3.0-or-later
 */

$prompt = 'Hello, who are you?';
$response = promptAI($prompt);
print_r($response);

function promptAI($prompt) {
    $prompt = trim($prompt);
    if (empty($prompt)) {
        return null;
    }

    // Set up the data to pass to cURL.
    $model = 'gpt-3.5-turbo';
    $apikey = 'sk-ReplaceThisFakeKey1234567890abcdefghijklmnopqrst'; // Replace the key in this line.
    $url = 'https://api.openai.com/v1/chat/completions';

    $curlhttpheader = [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apikey,
    ];
    $curlbody = [
        "model" => $model,
        "temperature" => (float) '0.7',
        "stop" => '',
        "top_p" => 1,
        "frequency_penalty" => 0,
        "presence_penalty" => 0,
    ];

    // Build message.
    $newmessage = new \stdClass();
    $newmessage->role = 'user';
    $newmessage->content = substr(json_encode($prompt), 1, -1); // Trim extra opening and closing quotes.
    $curlbody['messages'] = [$newmessage]; // An array because it can contain the history of prompts and responses.
    $curlbody['max_tokens'] = 4 * 1024 - strlen($newmessage->content); // For 4k models.

    // Submit the request.
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($curlbody));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $curlhttpheader);
    $completion = curl_exec($ch);
    curl_close($ch);

    // Decode the completion.
    $response = json_decode($completion);

    // Check if there was an error in the response.
    if (!empty($response->error->type)) {
        echo 'Error ' . '(' . $response->error->type . ') : ' . $response->error->message;
        return null;
    }

    // Check if we received a valid response.
    if (isset($response->choices[0]->message->content)) {
        $result = $response->choices[0]->message->content;
    } else {
        echo 'Something unexpected went wrong.';
        $result = null;
    }

    // Return the results from our query.
    return $result;
}
