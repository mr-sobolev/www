<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\WritableTask;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SaveMessageInDatabase implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $message;

    /**
     * Create a new job instance.
     */
    public function __construct(string $message)
    {
        $this->message = $message;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $decodedMessage = json_decode($this->message, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error("Message not decoded", ['message' => $this->message]);
            return;
        }

        try {
            DB::transaction(function () use ($decodedMessage) {
                $task = WritableTask::create($decodedMessage);
                Log::info("Message saved to database", ['decodedMessage' => $decodedMessage]);
                // if create success we can use websocket and notify this
            }, 3);
        } catch (\Exception $exception) {
            // if needed, we can send to rabbitmq in line
            Log::error("Message not saved in database", ['message' => $this->message, 'error' => $exception->getMessage()]);
        }
    }
}
