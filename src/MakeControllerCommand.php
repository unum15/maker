<?php

namespace Unum\Maker;;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MakeControllerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:controller {table}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make controller file from table name.';

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
        $model = studly_case(str_singular($table));
        $single = str_singular($table);
        $columns = DB::getSchemaBuilder()->getColumnListing($table);
        $columns = array_diff($columns, $skip_columns);
        $file_content =
"<?php

namespace App\\Http\\Controllers;

use App\\$model;
use Illuminate\\Http\\Request;

class ${model}Controller extends Controller
{
    public function __construct(Request \$request)
    {
        \$this->middleware('auth');
    }

    public function index()
    {
        \$items = $model::all();
        return ['data' => \$items];
    }

    public function create(Request \$request)
    {
        \$item = $model::create(\$request->input());
        return response(['data' => \$item], 201, ['Location' => route('$single.read', ['id' => \$item->id])]);
    }

    public function read(\$id)
    {
        \$item = $model::findOrFail(\$id);
        return ['data' => \$item];
    }

    public function update(\$id, Request \$request)
    {
        \$item = $model::findOrFail(\$id);
        \$item->update(\$request->input());
        return ['data' => \$item];
    }

    public function delete(Request \$request, \$id)
    {
        \$item = $model::findOrFail(\$id);
        \$item->delete();
        return response([], 401);
    }
}

";
        $filename = "app/Http/Controllers/${model}Controller.php";
        $result = file_put_contents($filename, $file_content);
        if($result){
            print "$filename created\n";
        }
        else{
            print "failed to create $filename\n";
        }
    }

}
