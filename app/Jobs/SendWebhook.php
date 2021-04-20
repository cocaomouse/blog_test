<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\Service;
use Illuminate\Support\Facades\Http;

class SendWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $queue = 'service';

    public Service $service;
    public array $data;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Service $service,array $data)
    {
        //
        $this->service = $service;
        $this->data    = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //
        Http::timeout(5)->post($this->service->url,$this->data);
    }
}
