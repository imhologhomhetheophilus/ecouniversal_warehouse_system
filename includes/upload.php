<?php

function uploadToCloudinary($filePath)
{
    $config = include __DIR__ . '/../config/cloudinary.php';

    $url = "https://api.cloudinary.com/v1_1/{$config['cloud_name']}/image/upload";

    $postData = [
        'file' => new CURLFile($filePath),
        'upload_preset' => 'unsigned_upload'
    ];

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    curl_close($ch);

    $result = json_decode($response, true);

    return $result['secure_url'] ?? null;
}