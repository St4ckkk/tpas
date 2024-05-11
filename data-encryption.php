<?php
$encryptionKey = 'ThisIsASecretEncryptionKey1234567890';


function encryptData($data, $key)
{
    $method = 'aes-256-cbc';
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($method));
    $encrypted = openssl_encrypt($data, $method, $key, 0, $iv);
    $encryptedData = base64_encode($iv . $encrypted);

    return $encryptedData;
}

function decryptData($data, $key)
{
    $method = 'aes-256-cbc';
    $data = base64_decode($data);
    $ivSize = openssl_cipher_iv_length($method);
    $iv = substr($data, 0, $ivSize);
    $encrypted = substr($data, $ivSize);
    $decrypted = openssl_decrypt($encrypted, $method, $key, 0, $iv);
    return $decrypted;
}
