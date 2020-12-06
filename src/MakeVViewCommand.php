<?php

namespace Unum\Maker;;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MakeVViewCommand extends MakeCommand
{
    protected $signature = 'make:v-view {table}';
    protected $description = 'Make VuesJs view resource list file from table name.';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->validateTable();
        $table = $this->argument('table');
        $single = Str::singular($table);
        $model = Str::studly($single);
        $component = Str::studly($table);
        $title = $this->title($table);
        $columns = DB::getSchemaBuilder()->getColumnListing($table);
        $foreign_keys = $this->getForeignKeys($table);
        $includes = "";
        if(!empty($foreign_keys)){
            $includes = "?includes=".implode(',',array_map('Str::singular',array_reverse($foreign_keys)));
        }
        $file_content =
"<template>
    <div>
        <TopMenu></TopMenu>
        <head>
            View $title
        </head>
        <main>
            <b-container fluid>
                <b-row>
                  <b-col md=\"6\" class=\"my-1\">
                    <b-form-group label=\"Filter\" class=\"mb-0\">
                      <b-input-group>
                        <b-form-input v-model=\"filter\" placeholder=\"Type to Search\" />
                        <b-input-group-append>
                          <b-btn :disabled=\"!filter\" @click=\"filter = ''\">Clear</b-btn>
                        </b-input-group-append>
                      </b-input-group>
                    </b-form-group>
                  </b-col>
                </b-row>
            </b-container>
            <b-table
                small
                striped
                hover
                :filter=\"filter\"
                :items=\"$table\"
                :fields=\"fields\"
            >
                <template v-slot:cell(id)=\"data\">
                    <a :href=\"'/$single/' + data.value\"> {{ data.value }} </a>
                </template>
            </b-table>
        </main>
    </div>
</template>
<script>
import TopMenu from './TopMenu';
export default {
    name: 'View$component',
    components: {
        'TopMenu': TopMenu,
    },
    data() {
        return {
            $table: [],
            filter: null,
            fields: [";
    foreach($columns as $column){
        $file_content .= "
                    {
";
        if(!isset($foreign_keys[$column])){
            $file_content .= "                        key: '$column',\n";
            $file_content .= "                        label: '" . $this->title($column) . "',";
        }
        else{
            $file_content .= "                        key: '" . Str::singular($foreign_keys[$column]) . ".name',\n";
            $file_content .= "                        label: '" . $this->title(Str::singular($foreign_keys[$column])) . "',";
        }
        $file_content .= "
                        sortable: true
                    },";
    };
    $file_content .= "
            ]
        }
    },
    created() {
        this.\$http.get('/$table$includes').then(response => {
            this.$table = response.data.data;
        });
    }
}
</script>

";
        echo $file_content;
    }

}
