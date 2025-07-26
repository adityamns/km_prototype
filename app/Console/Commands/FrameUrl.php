<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Models\Frame\FrameUrlModel;

class FrameUrl extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'framereg:url {function} {method} {controller}';

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
        $create = Urls::create([
                                    'name' => $this->argument('function'),
                                    'masked_name' => md5($request->name.microtime()) ,
                                    'parameters' => '/',
                                    'methods' => $this->argument('method'),
                                    'description' => '{placeholder description}',
                                    'input_description' => '{placeholder input}',
                                    'output_description' => '{placeholder output}',
                                    'controller_id' => $this->argument('controller'),
                                    'scope_id' => 'old-pegawai,old-mahasiswa',
                                    'created_by' => 'FrameCommand',
                                    'is_auth' => 1
                                ]);

        if ($create) 
        {
            $this->info('Success register URL, URL: '.$create->masked_name);
        }
        else
        {
            $this->info('Failed register controller');
        }
    }
}
