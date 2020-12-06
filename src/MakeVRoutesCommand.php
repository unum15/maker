<?php

namespace Unum\Maker;;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MakeVRoutesCommand extends MakeCommand
{
    protected $signature = 'make:v-routes {table}';
    protected $description = 'Make VuesJs route code from table name.';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $table = $this->argument('table');
        $single = Str::singular($table);
        $model = Str::studly($single);
        $component = Str::studly($table);
        $file_content =
"

                    <b-dropdown-item href=\"/$single\">New " . $this->title($single) . "</b-dropdown-item>
                    <b-dropdown-item href=\"/$table\">View " . $this->title($table) . "</b-dropdown-item>
                    
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
