<?php

return [
    /*
    |--------------------------------------------------------------------------
    | MQTT Broker Configuration
    |--------------------------------------------------------------------------
    |
    | Konfigurasi untuk koneksi ke MQTT broker (HiveMQ).
    | ESP32 gateway menggunakan broker.hivemq.com port 1883 (tanpa auth).
    |
    */

    'broker'   => env('MQTT_BROKER', 'broker.hivemq.com'),
    'port'     => env('MQTT_PORT', 1883),
    'username' => env('MQTT_USERNAME', null),
    'password' => env('MQTT_PASSWORD', null),

    /*
    |--------------------------------------------------------------------------
    | HiveMQ REST API
    |--------------------------------------------------------------------------
    |
    | HiveMQ menyediakan REST API untuk publish pesan tanpa koneksi MQTT.
    | Untuk HiveMQ Cloud, gunakan endpoint yang sesuai.
    | Untuk broker.hivemq.com (public), REST API tidak tersedia —
    | publish akan di-log saja (simulasi).
    |
    */
    'rest_api' => env('MQTT_REST_API', 'https://broker.hivemq.com:8888/api/v1'),

    /*
    |--------------------------------------------------------------------------
    | MQTT Topics
    |--------------------------------------------------------------------------
    */
    'topics' => [
        'pir'       => env('MQTT_TOPIC_PIR', 'keamanan/pir'),
        'reed'      => env('MQTT_TOPIC_REED', 'keamanan/reed'),
        'vibration' => env('MQTT_TOPIC_VIBRATION', 'keamanan/vibration'),
        'heartbeat' => env('MQTT_TOPIC_HEARTBEAT', 'keamanan/heartbeat'),
        'command'   => env('MQTT_TOPIC_COMMAND', 'keamanan/command'),
        'status'    => env('MQTT_TOPIC_STATUS', 'keamanan/status'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Webhook Secret
    |--------------------------------------------------------------------------
    |
    | Secret key untuk memverifikasi bahwa request ke /api/mqtt/ingest
    | benar-benar berasal dari MQTT bridge yang terpercaya.
    | Kosongkan untuk menonaktifkan verifikasi (tidak disarankan di production).
    |
    */
    'webhook_secret' => env('MQTT_WEBHOOK_SECRET', null),
];
