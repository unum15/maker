<?php

namespace Unum\Maker;;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MakeVRoutesCommand extends MakeCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:v-routes {table}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make VuesJs route code from table name.';

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
        $single = str_singular($table);
        $model = studly_case($single);
        $component = studly_case($table);
        $file_content =
"
import Edit$model from '@/components/Edit$model';
import View$component from '@/components/View$component';

    {
        path: '/$single',
        name: 'New$model',
        component: Edit$model,
        meta: {
            title: 'New " . $this->title($single) . "'
        }
    },
    {
        path: '/$single/:${single}_id',
        name: 'Edit$model',
        component: Edit$model,
        props: true,
        meta: {
            title: 'Edit " . $this->title($single) . "'
        }
    },
    {
      path: '/$table',
      name: 'View$component',
      component: View$component,
      meta: {
        title: 'View " . $this->title($table) . "'
      }
    },
";
        echo $file_content;
    }

}
