<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LoRaMessage;
use App\Models\Device;
use App\Services\LoRaProcessingService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class LoRaController extends Controller
{
    protected $loraProcessor;

    public function __construct(LoRaProcessingService $loraProcessor)
    {
        $this->loraProcessor = $loraProcessor;
    }

    /**
     * Terima data LoRa dari gateway
     */
    public function receiveMessage(Request $request): JsonResponse
    {
        try {
            // Validasi input
            $validator = Validator::make($request->all(), [
                'node_id' => 'required|string|max:50',
                'gateway_id' => 'nullable|string|max:50',
                'payload' => 'required|string',
                'rssi' => 'nullable|numeric',
                'snr' => 'nullable|numeric',
                'spreading_factor' => 'nullable|integer|min:7|max:12',
                'frequency' => 'nullable|numeric',
                'bandwidth' => 'nullable|integer',
                'received_at' => 'nullable|date',
                'metadata' => 'nullable|array'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $validator->validated();
            
            // Tentukan message type berdasarkan payload
            $messageType = $this->determineMessageType($data['payload']);
            
            // Cari device berdasarkan node_id atau device_id
            $device = Device::where('device_id', $data['node_id'])
                ->orWhere('name', 'like', '%' . $data['node_id'] . '%')
                ->first();
            
            // Jika tidak ketemu, pakai device pertama yang ada
            if (!$device) {
                $device = Device::where('type', 'sensor_node')->first();
            }

            // Simpan LoRa message
            $loraMessage = LoRaMessage::create([
                'device_id' => $device?->id,
                'node_id' => $data['node_id'],
                'gateway_id' => $data['gateway_id'] ?? 'GATEWAY_001',
                'direction' => 'inbound',
                'message_type' => $messageType,
                'payload' => $data['payload'],
                'rssi' => $data['rssi'] ?? null,
                'snr' => $data['snr'] ?? null,
                'spreading_factor' => $data['spreading_factor'] ?? null,
                'frequency' => $data['frequency'] ?? null,
                'bandwidth' => $data['bandwidth'] ?? null,
                'is_processed' => false,
                'status' => 'received',
                'metadata' => $data['metadata'] ?? null,
                'received_at' => isset($data['received_at']) ? Carbon::parse($data['received_at']) : now()
            ]);

            // Process message secara asynchronous
            $processingResult = $this->loraProcessor->processInboundMessage($loraMessage);

            return response()->json([
                'success' => true,
                'message' => 'LoRa message received successfully',
                'data' => [
                    'message_id' => $loraMessage->id,
                    'node_id' => $loraMessage->node_id,
                    'message_type' => $messageType,
                    'signal_quality' => $loraMessage->getSignalQuality(),
                    'estimated_distance' => $loraMessage->estimateDistance(),
                    'processing_result' => $processingResult,
                    'received_at' => $loraMessage->received_at->toISOString()
                ]
            ], 201);

        } catch (\Exception $e) {
            Log::error('LoRa message receive error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to process LoRa message',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Kirim command ke LoRa node
     */
    public function sendCommand(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'node_id' => 'required|string|max:50',
                'action' => 'required|string|in:reboot,config,read_sensors,set_threshold,sleep,wake',
                'parameters' => 'nullable|array',
                'gateway_id' => 'nullable|string|max:50',
                'priority' => 'nullable|string|in:low,medium,high,urgent'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $validator->validated();
            
            // Generate command payload
            $commandId = uniqid('CMD_');
            $payload = LoRaMessage::generateCommandPayload(
                $data['action'],
                $data['parameters'] ?? [],
                $commandId
            );

            // Simpan outbound message
            $loraMessage = LoRaMessage::create([
                'node_id' => $data['node_id'],
                'gateway_id' => $data['gateway_id'] ?? 'GATEWAY_001',
                'direction' => 'outbound',
                'message_type' => 'command',
                'payload' => $payload,
                'is_processed' => false,
                'status' => 'pending',
                'metadata' => [
                    'command_id' => $commandId,
                    'action' => $data['action'],
                    'priority' => $data['priority'] ?? 'medium'
                ],
                'transmitted_at' => now()
            ]);

            // Kirim ke LoRa gateway (implementasi tergantung gateway yang digunakan)
            $transmissionResult = $this->loraProcessor->transmitMessage($loraMessage);

            return response()->json([
                'success' => true,
                'message' => 'Command sent to LoRa node',
                'data' => [
                    'message_id' => $loraMessage->id,
                    'command_id' => $commandId,
                    'node_id' => $data['node_id'],
                    'action' => $data['action'],
                    'payload' => $payload,
                    'transmission_result' => $transmissionResult,
                    'transmitted_at' => $loraMessage->transmitted_at->toISOString()
                ]
            ], 201);

        } catch (\Exception $e) {
            Log::error('LoRa command send error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to send LoRa command',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Kirim konfigurasi ke LoRa node
     */
    public function sendConfig(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'node_id' => 'required|string|max:50',
                'parameter' => 'required|string|in:threshold,interval,sleep_time,tx_power,spreading_factor',
                'value' => 'required',
                'gateway_id' => 'nullable|string|max:50'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $validator->validated();
            
            // Generate config payload
            $configId = uniqid('CFG_');
            $payload = LoRaMessage::generateConfigPayload(
                $data['parameter'],
                $data['value'],
                $configId
            );

            // Simpan outbound message
            $loraMessage = LoRaMessage::create([
                'node_id' => $data['node_id'],
                'gateway_id' => $data['gateway_id'] ?? 'GATEWAY_001',
                'direction' => 'outbound',
                'message_type' => 'config',
                'payload' => $payload,
                'is_processed' => false,
                'status' => 'pending',
                'metadata' => [
                    'config_id' => $configId,
                    'parameter' => $data['parameter'],
                    'value' => $data['value']
                ],
                'transmitted_at' => now()
            ]);

            // Kirim ke LoRa gateway
            $transmissionResult = $this->loraProcessor->transmitMessage($loraMessage);

            return response()->json([
                'success' => true,
                'message' => 'Configuration sent to LoRa node',
                'data' => [
                    'message_id' => $loraMessage->id,
                    'config_id' => $configId,
                    'node_id' => $data['node_id'],
                    'parameter' => $data['parameter'],
                    'value' => $data['value'],
                    'payload' => $payload,
                    'transmission_result' => $transmissionResult,
                    'transmitted_at' => $loraMessage->transmitted_at->toISOString()
                ]
            ], 201);

        } catch (\Exception $e) {
            Log::error('LoRa config send error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to send LoRa configuration',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ambil messages LoRa terbaru
     */
    public function getMessages(Request $request): JsonResponse
    {
        try {
            $nodeId = $request->get('node_id');
            $direction = $request->get('direction');
            $messageType = $request->get('message_type');
            $limit = $request->get('limit', 50);

            $query = LoRaMessage::with('device')
                ->orderBy('created_at', 'desc');

            if ($nodeId) {
                $query->where('node_id', $nodeId);
            }

            if ($direction) {
                $query->where('direction', $direction);
            }

            if ($messageType) {
                $query->where('message_type', $messageType);
            }

            $messages = $query->limit($limit)->get();

            return response()->json([
                'success' => true,
                'data' => $messages->map(function ($message) {
                    return array_merge($message->toArray(), [
                        'signal_quality' => $message->getSignalQuality(),
                        'estimated_distance' => $message->estimateDistance(),
                        'parsed_data' => $message->parsed_data
                    ]);
                }),
                'count' => $messages->count()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch LoRa messages',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ambil statistik LoRa communication
     */
    public function getStatistics(Request $request): JsonResponse
    {
        try {
            $nodeId = $request->get('node_id');
            $hours = $request->get('hours', 24);

            $query = LoRaMessage::where('created_at', '>=', now()->subHours($hours));

            if ($nodeId) {
                $query->where('node_id', $nodeId);
            }

            $stats = [
                'total_messages' => $query->count(),
                'inbound_messages' => $query->where('direction', 'inbound')->count(),
                'outbound_messages' => $query->where('direction', 'outbound')->count(),
                'processed_messages' => $query->where('is_processed', true)->count(),
                'failed_messages' => $query->where('status', 'failed')->count(),
                'acknowledged_messages' => $query->where('is_acknowledged', true)->count(),
                'avg_rssi' => $query->whereNotNull('rssi')->avg('rssi'),
                'avg_snr' => $query->whereNotNull('snr')->avg('snr'),
                'message_types' => $this->getMessageTypeStatistics($query),
                'node_statistics' => $this->getNodeStatistics($query),
                'signal_quality_distribution' => $this->getSignalQualityDistribution($query),
                'latest_message' => $query->orderBy('created_at', 'desc')->first()
            ];

            // Hitung success rate
            if ($stats['total_messages'] > 0) {
                $stats['success_rate'] = round(($stats['processed_messages'] / $stats['total_messages']) * 100, 2);
            } else {
                $stats['success_rate'] = 0;
            }

            return response()->json([
                'success' => true,
                'data' => $stats,
                'period_hours' => $hours
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process unprocessed messages (untuk manual processing)
     */
    public function processUnprocessedMessages(): JsonResponse
    {
        try {
            $unprocessedMessages = LoRaMessage::unprocessed()
                ->inbound()
                ->orderBy('received_at')
                ->limit(100)
                ->get();

            $results = [];
            foreach ($unprocessedMessages as $message) {
                $result = $this->loraProcessor->processInboundMessage($message);
                $results[] = [
                    'message_id' => $message->id,
                    'node_id' => $message->node_id,
                    'result' => $result
                ];
            }

            return response()->json([
                'success' => true,
                'message' => 'Processed unprocessed messages',
                'data' => [
                    'processed_count' => count($results),
                    'results' => $results
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to process messages',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Tentukan message type berdasarkan payload
     */
    private function determineMessageType(string $payload): string
    {
        $upperPayload = strtoupper($payload);
        
        if (str_starts_with($upperPayload, 'SENSOR|')) {
            return 'sensor_data';
        } elseif (str_starts_with($upperPayload, 'HEARTBEAT|')) {
            return 'heartbeat';
        } elseif (str_starts_with($upperPayload, 'COMMAND|')) {
            return 'command';
        } elseif (str_starts_with($upperPayload, 'ACK|')) {
            return 'ack';
        } elseif (str_starts_with($upperPayload, 'CONFIG|')) {
            return 'config';
        } else {
            return 'sensor_data'; // Default assumption
        }
    }

    /**
     * Statistik per message type
     */
    private function getMessageTypeStatistics($query): array
    {
        $types = ['sensor_data', 'heartbeat', 'command', 'ack', 'config'];
        $typeStats = [];
        
        foreach ($types as $type) {
            $typeStats[$type] = $query->where('message_type', $type)->count();
        }
        
        return $typeStats;
    }

    /**
     * Statistik per node
     */
    private function getNodeStatistics($query): array
    {
        return $query->select('node_id')
            ->selectRaw('COUNT(*) as message_count')
            ->selectRaw('AVG(rssi) as avg_rssi')
            ->selectRaw('MAX(created_at) as last_seen')
            ->groupBy('node_id')
            ->get()
            ->keyBy('node_id')
            ->toArray();
    }

    /**
     * Distribusi kualitas sinyal
     */
    private function getSignalQualityDistribution($query): array
    {
        $messages = $query->whereNotNull('rssi')->get();
        $distribution = ['excellent' => 0, 'good' => 0, 'fair' => 0, 'poor' => 0, 'unknown' => 0];
        
        foreach ($messages as $message) {
            $quality = $message->getSignalQuality();
            $distribution[$quality]++;
        }
        
        return $distribution;
    }
}
