<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Models\Frame\FrameControllerModel;

class FrameController extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'framereg:controller {namespace}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $create = FrameController::create([
                                                'namespaces' => $this->argument('namespace'),
                                                'application_id' => env('APP_ID'),
                                                'created_by' => 1 //sementara diisi 1 = master SA account
                                            ]);

        if ($create) 
        {
            $this->info('Success register controller, controller ID: '.$create->id);
        }
        else
        {
            $this->info('Failed register controller');
        }
    }
}
