<?php

namespace Unum\Maker;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MakeModelCommand extends MakeCommand
{
    protected $signature = 'make:model {table}';
    protected $description = 'Make model file from existing table.';
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
        $columns_str = implode("',\n        '", $columns);
        $foreign_keys = $this->getForeignKeys($table);
        $file_content =
"<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class $model extends Model
{
    protected \$fillable = [
        '$columns_str'
    ];";
        foreach($foreign_keys as $foreign_key => $foreign_table){
            $foreign_single = Str::singular($foreign_table);
            $foreign_model = Str::studly($foreign_single);
            $file_content .=
"

    public function $foreign_single()
    {
        return \$this->belongsTo('App\\$foreign_model');
    }";
        }
        $file_content .=
"
}
";
        $result = file_put_contents("app/$model.php", $file_content);
        if($result){
            print "app/$model.php created\n";
        }
        else{
            print "failed to create app/$model.php\n";
        }
    }

}
