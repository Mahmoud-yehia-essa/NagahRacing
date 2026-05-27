<?php


namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Firebase\JWT\JWT;


class FirebaseNotificationService
{
    protected $projectId = 'fantasyalhajin-5b94a'; // e.g., wiselookfinal

    public function sendNotification($deviceToken, $title, $body, $postId, $status,$getNotificationsByUserCount)
    {

        /*
        $message = [
            'message' => [
                'token' => $deviceToken,
                'notification' => [
                    'title' => $title,
                    'body'  => $body,
                    'sound' => 'default', // 🔔 Enable sound

                ],
                'data' => [
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                    'title' => $postId,
                    'status' => $status,
                    'body' => $body,
                ],
            ],
        ];
        */

        $message = [
    'message' => [
        'token' => $deviceToken,
        'notification' => [
            'title' => $title,
            'body'  => $body,
        ],
        'data' => [
            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
            'title' => $postId,
            'status' => $status,
            'body' => $body,
        ],
        'apns' => [
            'headers' => [
                'apns-priority' => '10',
            ],
            'payload' => [
                'aps' => [
                    'alert' => [
                        'title' => $title,
                        'body' => $body,
                    ],
                    'sound' => 'default',
                    'badge' => $getNotificationsByUserCount,
                ],
            ],
        ],
        'android' => [
            'priority' => 'high',
            'notification' => [
                'sound' => 'default',
                'channel_id' => 'high_importance_channel', // ✔️ Must match Flutter

            ],
        ],
    ],
];


        $accessToken = $this->getAccessToken();

        $response = Http::withToken($accessToken)
            ->post("https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send", $message);

        return $response->json();
    }

    private function getAccessToken()
    {
        $jsonKey = Storage::path('firebase/firebase-credentials.json');

        $credentials = json_decode(file_get_contents($jsonKey), true);

        $jwt = new \Firebase\JWT\JWT;
        $now = time();
        $expires = $now + 3600;

        $payload = [
            'iss' => $credentials['client_email'],
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud' => $credentials['token_uri'],
            'iat' => $now,
            'exp' => $expires,
        ];

        $privateKey = $credentials['private_key'];

        $jwtAssertion = $jwt::encode($payload, $privateKey, 'RS256');

        $response = Http::asForm()->post($credentials['token_uri'], [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwtAssertion,
        ]);

        return $response->json()['access_token'];
    }
}

