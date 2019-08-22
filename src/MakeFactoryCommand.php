<?php

namespace Unum\Maker;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MakeFactoryCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:factory {table}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add factory to ModelFactory file from table name.';

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
        $file_content =
"
\$factory->define(App\\$model::class, function (Faker\Generator \$faker) {
    return [
";
        foreach($columns as $column){
            $file_content .= $this->factoryColumn($table, $column);
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
                $str .= "randomDigitNotNull";
                break;
            case 'text':
                $str .= "text";
                break;
            default:
                $str .= "word";
        }
        $str .= ",\n";
        return $str;
    }

}
