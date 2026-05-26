<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

/**
 * MqttService — Integrasi dengan HiveMQ broker
 *
 * Karena PHP/Laravel tidak bisa subscribe MQTT secara native,
 * kita gunakan HiveMQ REST API untuk publish pesan ke broker.
 * Untuk subscribe (menerima data dari ESP32 via MQTT), gunakan
 * endpoint /api/mqtt/ingest yang dipanggil oleh MQTT bridge/webhook.
 */
class MqttService
{
    protected string $broker;
    protected int    $port;
    protected string $restApiBase;
    protected ?string $username;
    protected ?string $password;

    // Topic constants — harus sama dengan yang ada di ESP32
    const TOPIC_PIR       = 'keamanan/pir';
    const TOPIC_REED      = 'keamanan/reed';
    const TOPIC_VIBRATION = 'keamanan/vibration';
    const TOPIC_HEARTBEAT = 'keamanan/heartbeat';
    const TOPIC_COMMAND   = 'keamanan/command';
    const TOPIC_STATUS    = 'keamanan/status';

    public function __construct()
    {
        $this->broker      = config('mqtt.broker', 'broker.hivemq.com');
        $this->port        = config('mqtt.port', 1883);
        $this->restApiBase = config('mqtt.rest_api', 'https://broker.hivemq.com:8888/api/v1');
        $this->username    = config('mqtt.username');
        $this->password    = config('mqtt.password');
    }

    /**
     * Publish pesan ke MQTT broker via HiveMQ REST API
     * Digunakan untuk mengirim command/status dari backend ke ESP32
     */
    public function publish(string $topic, array $payload, int $qos = 0, bool $retain = false): bool
    {
        try {
            $message = json_encode($payload);

            // Coba via HiveMQ REST API (jika tersedia)
            $result = $this->publishViaRestApi($topic, $message, $qos, $retain);

            if ($result) {
                Log::info('[MQTT] Published', ['topic' => $topic, 'payload' => $payload]);
                return true;
            }

            // Fallback: log saja (untuk development)
            Log::info('[MQTT] Publish (simulated - no REST API)', [
                'topic'   => $topic,
                'payload' => $payload,
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('[MQTT] Publish failed: ' . $e->getMessage(), [
                'topic' => $topic,
            ]);
            return false;
        }
    }

    /**
     * Publish via HiveMQ REST API
     */
    private function publishViaRestApi(string $topic, string $message, int $qos, bool $retain): bool
    {
        try {
            $url = $this->restApiBase . '/mqtt/publish';

            $body = [
                'topic'   => $topic,
                'payload' => base64_encode($message),
                'qos'     => $qos,
                'retain'  => $retain,
            ];

            $request = Http::timeout(5);

            if ($this->username && $this->password) {
                $request = $request->withBasicAuth($this->username, $this->password);
            }

            $response = $request->post($url, $body);

            return $response->successful();

        } catch (\Exception $e) {
            Log::warning('[MQTT] REST API publish failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Kirim command ke ESP32 node
     */
    public function sendCommand(string $nodeId, string $command, array $params = []): bool
    {
        $payload = [
            'node_id'   => $nodeId,
            'command'   => $command,
            'params'    => $params,
            'timestamp' => now()->toISOString(),
        ];

        return $this->publish(self::TOPIC_COMMAND, $payload, qos: 1);
    }

    /**
     * Kirim status update ke semua subscriber
     */
    public function broadcastStatus(array $status): bool
    {
        return $this->publish(self::TOPIC_STATUS, $status);
    }

    /**
     * Parse payload MQTT yang masuk dari ESP32
     * Payload dari ESP32 sudah dalam format JSON
     */
    public function parseIncomingPayload(string $topic, string $rawPayload): ?array
    {
        try {
            $data = json_decode($rawPayload, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::warning('[MQTT] Invalid JSON payload', [
                    'topic'   => $topic,
                    'payload' => $rawPayload,
                ]);
                return null;
            }

            // Tambahkan metadata topic
            $data['_mqtt_topic'] = $topic;
            $data['_received_at'] = now()->toISOString();

            return $data;

        } catch (\Exception $e) {
            Log::error('[MQTT] Parse payload failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Tentukan tipe sensor dari topic MQTT
     */
    public function getSensorTypeFromTopic(string $topic): ?string
    {
        return match ($topic) {
            self::TOPIC_PIR       => 'PIR',
            self::TOPIC_REED      => 'REED',
            self::TOPIC_VIBRATION => 'VIBRATION',
            self::TOPIC_HEARTBEAT => 'HEARTBEAT',
            default               => null,
        };
    }
}
