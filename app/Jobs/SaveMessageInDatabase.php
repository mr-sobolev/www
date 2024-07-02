<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\WritableTask;

class SaveMessageInDatabase implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    protected  $message;

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
            $this->error("Message not decoded': '{$this->message}'");
            return;
        }
        try {
            DB::transaction(function () use ($decodedMessage) {
                $task = WritableTask::create($decodedMessage);
                $this->info("Message save to db '{decodedMessage}'");
                // if create success we can use websocket and say this
            }, 3);
        } catch (ExternalServiceException $exception) {
            // if need, we can send to rabbitmq in line
            $this->error("Message not saved in database': '{$this->message}'");
        }

    }
}
