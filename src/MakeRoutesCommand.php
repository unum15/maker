<?php

namespace Unum\Maker;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MakeRoutesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:routes {table}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add routes to web routes file from table name.';

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
        $skip_columns = ['id', 'created_at', 'updated_at'];
        $single = str_singular($table);
        $model = studly_case(str_singular($table));
        $columns = DB::getSchemaBuilder()->getColumnListing($table);
        $columns = array_diff($columns, $skip_columns);
        $file_content =
"
\$router->get('/$table', ['uses' => '${model}Controller@index', 'as' => '$single.index']);
\$router->post('/$single', ['uses' => '${model}Controller@create', 'as' => '$single.create']);
\$router->get('/$single/{id:[0-9]+}', ['uses' => '${model}Controller@read', 'as' => '$single.read']);
\$router->patch('/$single/{id:[0-9]+}', ['uses' => '${model}Controller@update', 'as' => '$single.update']);
\$router->delete('/$single/{id:[0-9]+}', ['uses' => '${model}Controller@delete', 'as' => '$single.delete']);";
        $filename = "routes/web.php";
        $result = file_put_contents($filename, $file_content, FILE_APPEND);
        if($result){
            print "$filename updated\n";
        }
        else{
            print "failed to update $filename\n";
        }
    }

}
