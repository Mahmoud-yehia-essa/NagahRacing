<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class FirebaseNotificationService
{
    protected $projectId = 'najahracing-app';

    public function sendNotification($deviceToken, $title, $body, $postId, $status, $getNotificationsByUserCount = 0)
    {
        if (empty($deviceToken)) {
            \Illuminate\Support\Facades\Log::warning("FCM sendNotification skipped: deviceToken is empty.");
            return ['error' => 'Device token is empty'];
        }

        $message = [
            'message' => [
                'token' => $deviceToken,
                'notification' => [
                    'title' => $title,
                    'body'  => $body,
                ],
                'data' => [
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                    'session_id'   => (string)$postId,
                    'title'        => (string)$title,
                    'status'       => (string)$status,
                    'body'         => (string)$body,
                ],
                'apns' => [
                    'headers' => [
                        'apns-priority' => '10',
                    ],
                    'payload' => [
                        'aps' => [
                            'alert' => [
                                'title' => $title,
                                'body'  => $body,
                            ],
                            'sound' => 'default',
                            'badge' => (int)$getNotificationsByUserCount,
                        ],
                    ],
                ],
                'android' => [
                    'priority' => 'high',
                    'notification' => [
                        'sound' => 'default',
                        'channel_id' => 'high_importance_channel',
                    ],
                ],
            ],
        ];

        try {
            $tokenData = $this->getAccessTokenAndProjectId();
            $accessToken = $tokenData['access_token'];
            $projectId = $tokenData['project_id'] ?? $this->projectId;

            $response = Http::withToken($accessToken)
                ->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", $message);

            $result = $response->json();
            if ($response->successful()) {
                \Illuminate\Support\Facades\Log::info("FCM Notification sent successfully to: " . substr($deviceToken, 0, 15) . "...");
            } else {
                \Illuminate\Support\Facades\Log::error("FCM Send failed: " . json_encode($result));
            }
            return $result;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("FCM Notification Exception: " . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    
    public function sendCallNotification($deviceToken, $callerName, $channelName, $agoraToken, $callerId, $callerPhoto = null)
    {
        if (empty($deviceToken)) {
            \Illuminate\Support\Facades\Log::warning("FCM sendCallNotification skipped: deviceToken is empty.");
            return ['error' => 'Device token is empty'];
        }

        $title = "مكالمة واردة من " . ($callerName ?: "المالك");
        $body = "اضغط هنا للرد على المكالمة الصوتية 📞";

        $message = [
            'message' => [
                'token' => $deviceToken,
                'notification' => [
                    'title' => $title,
                    'body'  => $body,
                ],
                'data' => [
                    'click_action'  => 'FLUTTER_NOTIFICATION_CLICK',
                    'type'          => 'incoming_call',
                    'channel_name'  => (string)$channelName,
                    'caller_name'   => (string)$callerName,
                    'caller_id'     => (string)$callerId,
                    'caller_photo'  => (string)($callerPhoto ?? ''),
                    'token'         => (string)($agoraToken ?? ''),
                    'title'         => (string)$title,
                    'body'          => (string)$body,
                ],
                'apns' => [
                    'headers' => [
                        'apns-priority'  => '10',
                        'apns-push-type' => 'alert',
                    ],
                    'payload' => [
                        'aps' => [
                            'alert' => [
                                'title' => $title,
                                'body'  => $body,
                            ],
                            'sound' => 'default',
                            'badge' => 1,
                            'content-available' => 1,
                        ],
                    ],
                ],
                'android' => [
                    'priority' => 'HIGH',
                    'notification' => [
                        'sound'                 => 'default',
                        'channel_id'            => 'incoming_calls_channel',
                        'notification_priority' => 'PRIORITY_MAX',
                        'visibility'            => 'PUBLIC',
                        'default_sound'         => true,
                        'default_vibrate_timings' => true,
                    ],
                ],
            ],
        ];

        try {
            $tokenData = $this->getAccessTokenAndProjectId();
            $accessToken = $tokenData['access_token'];
            $projectId = $tokenData['project_id'] ?? $this->projectId;

            $response = Http::withToken($accessToken)
                ->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", $message);

            $result = $response->json();
            if ($response->successful()) {
                \Illuminate\Support\Facades\Log::info("FCM Call Notification sent successfully to: " . substr($deviceToken, 0, 15) . "...");
            } else {
                \Illuminate\Support\Facades\Log::error("FCM Call Send failed: " . json_encode($result));
            }
            return $result;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("FCM Call Notification Exception: " . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    public function sendCallCancelNotification($deviceToken, $channelName)
    {
        if (empty($deviceToken)) return;

        $message = [
            'message' => [
                'token' => $deviceToken,
                'data' => [
                    'type'         => 'call_hangup',
                    'channel_name' => (string)$channelName,
                ],
                'android' => [
                    'priority' => 'HIGH',
                ],
                'apns' => [
                    'headers' => [
                        'apns-priority'  => '5',
                        'apns-push-type' => 'background',
                    ],
                    'payload' => [
                        'aps' => [
                            'content-available' => 1,
                        ],
                    ],
                ],
            ],
        ];

        try {
            $tokenData = $this->getAccessTokenAndProjectId();
            $accessToken = $tokenData['access_token'];
            $projectId = $tokenData['project_id'] ?? $this->projectId;

            Http::withToken($accessToken)
                ->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", $message);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning("FCM Call Cancel Exception: " . $e->getMessage());
        }
    }

    private function getAccessTokenAndProjectId()
    {
        $candidates = [
            Storage::path('firebase/firebase-credentials.json'),
            storage_path('app/firebase/firebase-credentials.json'),
            storage_path('app/firebase_credentials.json'),
            base_path(env('FIREBASE_CREDENTIALS', 'storage/app/firebase/firebase-credentials.json')),
        ];

        $jsonKey = null;
        foreach ($candidates as $path) {
            if ($path && file_exists($path)) {
                $jsonKey = $path;
                break;
            }
        }

        if (!$jsonKey) {
            throw new \Exception("Firebase credentials JSON file not found in storage/app/firebase/firebase-credentials.json");
        }

        $credentials = json_decode(file_get_contents($jsonKey), true);
        if (!$credentials || empty($credentials['client_email']) || empty($credentials['private_key'])) {
            throw new \Exception("Invalid Firebase credentials JSON format in: $jsonKey");
        }

        $projectId = $credentials['project_id'] ?? env('FIREBASE_PROJECT_ID', $this->projectId);

        $jwt = new \Firebase\JWT\JWT;
        $now = time();
        $expires = $now + 3600;

        $payload = [
            'iss'   => $credentials['client_email'],
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud'   => $credentials['token_uri'] ?? 'https://oauth2.googleapis.com/token',
            'iat'   => $now,
            'exp'   => $expires,
        ];

        $privateKey = $credentials['private_key'];
        $jwtAssertion = $jwt::encode($payload, $privateKey, 'RS256');

        $response = Http::asForm()->post($credentials['token_uri'] ?? 'https://oauth2.googleapis.com/token', [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion'  => $jwtAssertion,
        ]);

        $responseData = $response->json();
        if (empty($responseData['access_token'])) {
            throw new \Exception("Could not obtain Google Access Token: " . json_encode($responseData));
        }

        return [
            'access_token' => $responseData['access_token'],
            'project_id'   => $projectId,
        ];
    }
}
