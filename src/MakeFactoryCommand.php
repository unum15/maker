<?php

namespace Unum\Maker;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MakeFactoryCommand extends MakeCommand
{
    protected $signature = 'make:factory {table}';
    protected $description = 'Add factory to ModelFactory file from table name.';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->validateTable();
        $table = $this->argument('table');
        $skip_columns = ['id', 'created_at', 'updated_at'];
        $model = Str::studly(Str::singular($table));
        $columns = DB::getSchemaBuilder()->getColumnListing($table);
        $columns = array_diff($columns, $skip_columns);
        $foreign_keys = $this->getForeignKeys($table);
        $file_content = "\n\$factory->define(App\\$model::class, function (Faker\Generator \$faker) {\n";
        foreach($foreign_keys as $foreign_key => $foreign_table){
            $foreign_single = Str::singular($foreign_table);
            $foreign_model = Str::studly($foreign_single);
            $file_content .= "    \$$foreign_single = factory(App\\$foreign_model::class)->create();\n";
        }
        $file_content .= "    return [\n";
        foreach($columns as $column){
            if(isset($foreign_keys[$column])){
                $foreign_single = Str::singular($foreign_keys[$column]);
                $file_content .= "        '$column' => \$${foreign_single}->id,\n";
            }
            else{
                $file_content .= $this->factoryColumn($table, $column);
            }
        }
        $file_content = substr($file_content,0,-2);
        $file_content .=
"
    ];
});
";
        $filename = "database/factories/ModelFactory.php";
        $result = file_put_contents($filename, $file_content, FILE_APPEND);
        if($result){
            print "$filename updated\n";
        }
        else{
            print "failed to update $filename\n";
        }
    }
    
    public function factoryColumn($table, $column){
        $str = "        '$column' => \$faker->";
        $type = DB::getSchemaBuilder()->getColumnType($table, $column);
        switch($type){
            case 'integer':
            case 'bigint':
                $str .= "randomDigitNotNull";
                break;
            case 'text':
                $str .= "text";
                break;
            case 'boolean':
                $str .= "boolean";
                break;
            case 'date':
                $str .= "date";
                break;
            case 'timestamp':
                $str .= "dateTime";
                break;
            case 'float':
                $str .= "randomFloat";
                break;
            
            default:
                $str .= "word";
        }
        $str .= ",\n";
        return $str;
    }

}
