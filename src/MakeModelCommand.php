<?php

namespace Unum\Maker;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MakeModelCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:model {table}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make model file from existing table.';

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
        $columns = DB::getSchemaBuilder()->getColumnListing($table);
        $columns = array_diff($columns, $skip_columns);
        $columns_str = implode("',\n        '", $columns);
        $file_content =
"<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class $model extends Model
{
    protected \$fillable = [
        '$columns_str'
    ];
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
