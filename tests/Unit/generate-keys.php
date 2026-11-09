<?php

$config = [
    "private_key_bits" => 4096,
    "private_key_type" => OPENSSL_KEYTYPE_RSA,
];

$key = openssl_pkey_new($config);

if (!$key) {
    die("Could not create key: " . openssl_error_string() . PHP_EOL);
}

if (!openssl_pkey_export($key, $privateKey)) {
    die("Could not export private key." . PHP_EOL);
}

$details = openssl_pkey_get_details($key);

if (!$details || empty($details['key'])) {
    die("Could not get public key." . PHP_EOL);
}

$publicKey = $details['key'];

file_put_contents(
    __DIR__ . "/stocky-private.pem",
    $privateKey
);

file_put_contents(
    __DIR__ . "/stocky-public.pem",
    $publicKey
);

echo PHP_EOL;
echo "====================================" . PHP_EOL;
echo " STOCKY KEYS CREATED SUCCESSFULLY" . PHP_EOL;
echo "====================================" . PHP_EOL;
echo PHP_EOL;

echo "Private key:" . PHP_EOL;
echo __DIR__ . "/stocky-private.pem" . PHP_EOL;
echo PHP_EOL;

echo "Public key:" . PHP_EOL;
echo __DIR__ . "/stocky-public.pem" . PHP_EOL;
echo PHP_EOL;

echo "IMPORTANT:" . PHP_EOL;
echo "NEVER give stocky-private.pem to customers." . PHP_EOL;