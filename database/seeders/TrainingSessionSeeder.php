<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\CamelWorker;
use App\Models\TrainingSession;
use App\Models\SessionSpeedLog;
use App\Models\SessionInstruction;
use App\Models\SessionChat;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TrainingSessionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear old sessions and related logs/chats to prevent duplicates
        \Schema::disableForeignKeyConstraints();
        TrainingSession::truncate();
        SessionSpeedLog::truncate();
        SessionChat::truncate();
        SessionInstruction::truncate();
        \Schema::enableForeignKeyConstraints();

        // 1. Create or get Owner
        $owner = User::where('role', 'owner')->first();
        if (!$owner) {
            $owner = User::create([
                'fname' => 'سالم',
                'lname' => 'الكتبي',
                'username' => 'salem_owner',
                'email' => 'owner@nagahracing.com',
                'password' => Hash::make('password'),
                'phone' => '+971501234567',
                'role' => 'owner',
                'status' => 'active',
            ]);
        }

        // 2. Create or get Camel Workers
        $worker1 = CamelWorker::where('full_name', 'محمد السوداني')->first();
        if (!$worker1) {
            $worker1 = CamelWorker::create([
                'owner_id' => $owner->id,
                'full_name' => 'محمد السوداني',
                'login_code' => '112233',
                'phone' => '+971509876543',
                'status' => 'active',
                'is_online' => true,
            ]);
        }

        $worker2 = CamelWorker::where('full_name', 'أحمد البلوشي')->first();
        if (!$worker2) {
            $worker2 = CamelWorker::create([
                'owner_id' => $owner->id,
                'full_name' => 'أحمد البلوشي',
                'login_code' => '445566',
                'phone' => '+971501112223',
                'status' => 'active',
                'is_online' => false,
            ]);
        }

        // 3. Create Session 1: Active/Working
        $session1 = TrainingSession::create([
            'camel_worker_id' => $worker1->id,
            'location_name' => 'ميدان المرموم، دبي',
            'latitude' => 24.96300000,
            'longitude' => 55.48000000,
            'round_status' => 'working',
            'speed' => 42.50,
            'average_speed' => 38.20,
            'round_distance_km' => 4.50,
            'round_time' => '00:07:12',
            'performance' => 85.00,
        ]);

        // Speed Logs for Session 1 (Real Al Marmoom Camel Race Track loop)
        $coords1 = [
            ['lat' => 24.96300000, 'lng' => 55.48000000, 'speed' => 32.00, 'name' => 'نقطة الانطلاق'],
            ['lat' => 24.96200000, 'lng' => 55.48500000, 'speed' => 34.50, 'name' => 'المنعطف الجنوبي'],
            ['lat' => 24.96400000, 'lng' => 55.49200000, 'speed' => 37.00, 'name' => 'المنعطف الشرقي'],
            ['lat' => 24.97000000, 'lng' => 55.49300000, 'speed' => 41.20, 'name' => 'المستقيم الشرقي'],
            ['lat' => 24.97600000, 'lng' => 55.49000000, 'speed' => 39.80, 'name' => 'المنعطف الشمالي الشرقي'],
            ['lat' => 24.97800000, 'lng' => 55.48400000, 'speed' => 43.50, 'name' => 'المنعطف الشمالي'],
            ['lat' => 24.97500000, 'lng' => 55.47600000, 'speed' => 40.00, 'name' => 'المنعطف الغربي'],
            ['lat' => 24.96900000, 'lng' => 55.47700000, 'speed' => 42.50, 'name' => 'الموقع الحالي للهجن'],
        ];

        foreach ($coords1 as $idx => $coord) {
            SessionSpeedLog::create([
                'training_session_id' => $session1->id,
                'speed' => $coord['speed'],
                'latitude' => $coord['lat'],
                'longitude' => $coord['lng'],
                'location_name' => $coord['name'],
                'created_at' => now()->subMinutes(10 - $idx),
            ]);
        }

        // Session Chats for Session 1
        SessionChat::create([
            'training_session_id' => $session1->id,
            'owner_id' => $owner->id,
            'camel_worker_id' => null,
            'content' => 'حافظ على سرعة 40 كم/س في الخط المستقيم ولا تضغط على المطية.',
            'content_type' => 'text',
            'created_at' => now()->subMinutes(8),
        ]);

        SessionChat::create([
            'training_session_id' => $session1->id,
            'owner_id' => null,
            'camel_worker_id' => $worker1->id,
            'content' => 'علم يا طويل العمر، المطية ممتازة ومستجيبة للتعليمات.',
            'content_type' => 'text',
            'created_at' => now()->subMinutes(7),
        ]);

        SessionChat::create([
            'training_session_id' => $session1->id,
            'owner_id' => $owner->id,
            'camel_worker_id' => null,
            'content' => 'زد السرعة تدريجياً لـ 45 كم/س في الكيلومتر الأخير.',
            'content_type' => 'text',
            'created_at' => now()->subMinutes(6),
        ]);

        SessionChat::create([
            'training_session_id' => $session1->id,
            'owner_id' => null,
            'camel_worker_id' => $worker1->id,
            'content' => 'بدأت في زيادة السرعة الآن.',
            'content_type' => 'text',
            'created_at' => now()->subMinutes(5),
        ]);


        // 4. Create Session 2: Ended
        $session2 = TrainingSession::create([
            'camel_worker_id' => $worker2->id,
            'location_name' => 'ميدان الوثبة، أبوظبي',
            'latitude' => 24.18000000,
            'longitude' => 54.62500000,
            'round_status' => 'end',
            'speed' => 0.00,
            'average_speed' => 39.80,
            'round_distance_km' => 6.00,
            'round_time' => '00:10:05',
            'performance' => 92.50,
            'session_ended_at' => now()->subHours(2),
            'summary_text' => 'تم الانتهاء من الشوط التدريبي بنجاح. أظهرت الهجن استجابة رائعة في النصف الثاني وتم قياس مؤشر اللياقة والنبض وهما في الحدود الممتازة.',
            'summary_audio' => 'upload/audio/sample_summary.mp3',
            'summary_image' => 'upload/camel_workers/sample_camel.jpg',
        ]);

        // Speed Logs for Session 2 (Real Al Wathba Camel Race Track loop)
        $coords2 = [
            ['lat' => 24.18000000, 'lng' => 54.62500000, 'speed' => 35.00, 'name' => 'نقطة الانطلاق (ميدان الوثبة)'],
            ['lat' => 24.17900000, 'lng' => 54.63000000, 'speed' => 37.50, 'name' => 'المنعطف الجنوبي'],
            ['lat' => 24.18100000, 'lng' => 54.63600000, 'speed' => 39.00, 'name' => 'المنعطف الشرقي'],
            ['lat' => 24.18600000, 'lng' => 54.63700000, 'speed' => 42.00, 'name' => 'المستقيم الشرقي'],
            ['lat' => 24.19100000, 'lng' => 54.63500000, 'speed' => 41.50, 'name' => 'المنعطف الشمالي'],
            ['lat' => 24.19200000, 'lng' => 54.63000000, 'speed' => 44.20, 'name' => 'الموقع النهائي'],
        ];

        foreach ($coords2 as $idx => $coord) {
            SessionSpeedLog::create([
                'training_session_id' => $session2->id,
                'speed' => $coord['speed'],
                'latitude' => $coord['lat'],
                'longitude' => $coord['lng'],
                'location_name' => $coord['name'],
                'created_at' => now()->subHours(2)->subMinutes(15 - $idx),
            ]);
        }

        // Session Chats for Session 2
        SessionChat::create([
            'training_session_id' => $session2->id,
            'owner_id' => $owner->id,
            'camel_worker_id' => null,
            'content' => 'كيف أداء المطية في المنعطف الأخير؟',
            'content_type' => 'text',
            'created_at' => now()->subHours(2)->subMinutes(10),
        ]);

        SessionChat::create([
            'training_session_id' => $session2->id,
            'owner_id' => null,
            'camel_worker_id' => $worker2->id,
            'content' => 'أدائها قوي وتوازنها ممتاز بدون إجهاد.',
            'content_type' => 'text',
            'created_at' => now()->subHours(2)->subMinutes(8),
        ]);

        // 5. Create Session 3: Active/Working in Al Sawan Camel Racetrack, Ras Al Khaimah (Scenic track)
        TrainingSession::create([
            'camel_worker_id' => $worker1->id,
            'location_name' => 'ميدان السوان، رأس الخيمة',
            'latitude' => 25.67453000,
            'longitude' => 55.92542000,
            'round_status' => 'working',
            'speed' => 0.00,
            'average_speed' => 0.00,
            'round_distance_km' => 0.00,
            'round_time' => '00:00:00',
            'performance' => 88.00,
        ]);
    }
}
