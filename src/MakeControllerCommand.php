<?php

namespace Unum\Maker;;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MakeControllerCommand extends MakeCommand
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
        $model = studly_case(str_singular($table));
        $single = str_singular($table);
        $columns = DB::getSchemaBuilder()->getColumnListing($table);
        $skip_columns = ['id', 'created_at', 'updated_at'];
        $columns = array_diff($columns, $skip_columns);
        $foreign_keys = $this->getForeignKeys($table);
        $file_content =
"<?php

namespace App\\Http\\Controllers;

use App\\$model;
use Illuminate\\Http\\Request;
use Illuminate\\Support\\Facades\\Log;

class ${model}Controller extends Controller
{
    public function __construct()
    {
        Log::debug('${model}Controller Constructed');
        \$this->middleware('auth');
    }

    public function index(Request \$request)
    {
        \$includes = \$this->validateIncludes(\$request->input('includes'));
        \$values = \$this->validateModel(\$request);
        \$items_query = $model::with(\$includes);
        foreach(\$values as \$field => \$value){
            \$items_query->where(\$field, \$value);
        }
        \$items = \$items_query->get();
        return ['data' => \$items];
    }

    public function create(Request \$request)
    {
        \$values = \$this->validateModel(\$request, true);
        \$item = $model::create(\$values);
        return response(['data' => \$item], 201, ['Location' => route('$single.read', ['id' => \$item->id])]);
    }

    public function read(\$id, Request \$request)
    {
        \$includes = \$this->validateIncludes(\$request->input('includes'));
        \$item = $model::with(\$includes)->find(\$id);
        return ['data' => \$item];
    }

    public function update(\$id, Request \$request)
    {
        \$item = $model::findOrFail(\$id);
        \$values = \$this->validateModel(\$request);
        \$item->update(\$values);
        return ['data' => \$item];
    }

    public function delete(Request \$request, \$id)
    {
        \$item = $model::findOrFail(\$id);
        \$item->delete();
        return response([], 204);
    }
    
    protected \$model_validation = [";
        foreach($columns as $column){
            $col_info = $this->getColumnData($table, $column);
            $validators = [];
            switch($col_info->data_type){
                case 'bigint':
                case 'integer':
                    array_push($validators, 'integer');
                    break;
                case 'character varying':
                case 'text':
                    array_push($validators, 'string');
                    if($col_info->character_octet_length != ""){
                        array_push($validators, 'max:'.$col_info->character_octet_length);
                    }
                    break;
                default:
                    array_push($validators, $col_info->data_type);
                    break;
            }
            if($col_info->is_nullable == 'YES'){
                array_push($validators, 'nullable');
            }
            if(isset($foreign_keys[$column])){
                array_push($validators, 'exists:' . $foreign_keys[$column] . ',id');
            }
            $file_content .= "\n       '$column' => '" . implode('|', $validators) . "',";
        }
        $file_content .=
"
    ];
    
    protected \$model_validation_required = [";
        foreach($columns as $column){
            $col_info = $this->getColumnData($table, $column);
            if($col_info->is_nullable == 'NO'){
                $file_content .= "\n       '$column' => 'required',";
            }
        }
        $file_content .=
"
    ];";
        if(!empty($foreign_keys)){
            $file_content .=
"

    protected \$model_includes = [
";

        $file_content .= "       '".implode("',\n       '", array_map('str_singular',array_reverse($foreign_keys)))."'";
        $file_content .=
"
    ];
    ";
        }
        $file_content .=
"
}";
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
