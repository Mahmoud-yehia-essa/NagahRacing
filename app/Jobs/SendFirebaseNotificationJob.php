<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\FirebaseNotificationService; // تأكد من المسار الصحيح

class SendFirebaseNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $token;
    public $title;
    public $body;
    public $postId;
    public $status;
    public $count;

    /**
     * Create a new job instance.
     */
    public function __construct($token, $title, $body, $postId, $status, $count)
    {
        $this->token = $token;
        $this->title = $title;
        $this->body = $body;
        $this->postId = $postId;
        $this->status = $status;
        $this->count = $count;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        // استدعي سيرفيس Firebase كما عندك الآن
        (new FirebaseNotificationService())->sendNotification(
            $this->token,
            $this->title,
            $this->body,
            $this->postId,
            $this->status,
            $this->count
        );
    }
}
