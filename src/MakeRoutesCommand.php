<?php

namespace Unum\Maker;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MakeRoutesCommand extends Command
{
    protected $signature = 'make:routes {table}';
    protected $description = 'Add routes to web routes file from table name.';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $table = $this->argument('table');
        $skip_columns = ['id', 'created_at', 'updated_at'];
        $single = Str::singular($table);
        $model = Str::studly(Str::singular($table));
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
