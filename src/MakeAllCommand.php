<?php

namespace Unum\Maker;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MakeAllCommand extends MakeCommand
{
    protected $signature = 'make:all {table}';
    protected $description = 'Make model, controller, routes, factory, and tests from table name.';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->validateTable();
        $table = $this->argument('table');
        $this->call('make:model', ['table' => $table]);
        $this->call('make:controller', ['table' => $table]);
        $this->call('make:routes', ['table' => $table]);
        $this->call('make:factory', ['table' => $table]);
        $this->call('make:test', ['table' => $table]);
    }

}
