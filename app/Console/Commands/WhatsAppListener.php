<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class WhatsAppListener extends Command
{
    protected $signature = 'whatsapp:listen';
    protected $description = 'Start WhatsApp Listener';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting WhatsApp Client...');

        $output = null;
        $retval = null;
        exec('node ' . base_path('whatsapp.cjs'), $output, $retval);

        foreach ($output as $line) {
            $this->info($line);
        }

        return 0;
    }
}
