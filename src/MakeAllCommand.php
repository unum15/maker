<?php

namespace Unum\Maker;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MakeAllCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:all {table}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make model, controller, routes, factory, and tests from table name.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $table = $this->argument('table');
        $this->call('make:model', ['table' => $table]);
        $this->call('make:controller', ['table' => $table]);
        $this->call('make:routes', ['table' => $table]);
        $this->call('make:factory', ['table' => $table]);
        $this->call('make:test', ['table' => $table]);
    }

}
