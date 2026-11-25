<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class ServeWhatsAppCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'whatsapp:serve {--port=3002}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start the Nagah Racing WhatsApp Baileys Microservice daemon';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $port = $this->option('port') ?: 3002;
        $serviceDir = base_path('whatsapp-service');

        if (!is_dir($serviceDir)) {
            $this->error("whatsapp-service directory not found at: {$serviceDir}");
            return 1;
        }

        $this->info("Starting Nagah Racing WhatsApp Baileys Engine on port {$port}...");
        $this->info("Press Ctrl+C to stop.");

        $process = new Process(['node', 'server.js'], $serviceDir, ['PORT' => (string) $port]);
        $process->setTimeout(null);

        $process->run(function ($type, $buffer) {
            $this->output->write($buffer);
        });

        return 0;
    }
}
