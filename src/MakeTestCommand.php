<?php

namespace Unum\Maker;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MakeTestCommand extends Command
{
    protected $signature = 'make:test {table}';
    protected $description = 'Make test file from table name.';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $table = $this->argument('table');
        $skip_columns = ['id', 'created_at', 'updated_at'];
        $model = Str::studly(Str::singular($table));
        $single = Str::singular($table);
        $columns = DB::getSchemaBuilder()->getColumnListing($table);
        $columns = array_diff($columns, $skip_columns);
        $file_content =
"<?php

use App\\$model;
use Laravel\\Lumen\\Testing\\WithoutMiddleware;

class ${model}ControllerTest extends TestCase
{

    use WithoutMiddleware;
   
    public function testIndex()
    {
        \$items = factory('App\\$model', 2)->create();
        \$response = \$this->get('/$table');
        \$response->seeStatusCode(200);
        \$response->seeJson(\$items[0]->toArray());
        \$response->seeJson(\$items[1]->toArray());
    }    
    
    public function testCreate()
    {
        \$item = factory('App\\$model')->make();
        \$response = \$this->post('/$single', \$item->toArray());
        \$response->seeStatusCode(201);
        \$response->seeJson(\$item->toArray());
        \$this->seeInDatabase('$table', \$item->toArray());
    }
    
    public function testRead()
    {
        \$item = factory('App\\$model')->create();
        \$response = \$this->get('/$single/' . \$item->id);
        \$response->seeStatusCode(200);
        \$response->seeJsonEquals(['data' => \$item->toArray()]);
        \$this->seeInDatabase('$table', \$item->toArray());
    }
    
    public function testUpdate()
    {
        \$item = factory('App\\$model')->create();
        \$update = ['name' => 'test'];
        \$response = \$this->patch('/$single/' . \$item->id, \$update);
        \$response->seeStatusCode(200);
        \$item = \$item->find(\$item->id);
        \$updated_array = array_merge(\$item->toArray(), \$update);
        \$response->seeJsonEquals(['data' => \$updated_array]);
        \$this->seeInDatabase('$table', \$updated_array);
    }
    
    public function testDelete()
    {
        \$item = factory('App\\$model')->create();
        \$response = \$this->delete('/$single/' . \$item->id);
        \$response->seeStatusCode(204);
        \$this->notSeeInDatabase('$table', \$item->toArray());
    }
}

";
        $filename = "tests/${model}ControllerTest.php";
        $result = file_put_contents($filename, $file_content);
        if($result){
            print "$filename created\n";
        }
        else{
            print "failed to create $filename\n";
        }
    }

}
