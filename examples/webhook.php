<?php

$secret_key = 'very secret'; // You will receive your actual secret key from CASHLINK support.

$request_body = file_get_contents('php://input');

$actual_signature = $_SERVER['HTTP_HOOK_HMAC'];
$expected_signature = base64_encode(hash_hmac('sha512', $request_body, $secret_key, true));
if ($actual_signature !== $expected_signature) {
    die('Signature mismatch');
}

$json_data = json_decode($request_body, true);
echo "Received event: {$json_data['event']}\n";
echo "Timestamp: {$json_data['timestamp']}\n";
echo "ID of payment: {$json_data['data']['public_id']}\n";
echo "ID of cashlink: {$json_data['data']['cashlink']['public_id']}\n";

?>
