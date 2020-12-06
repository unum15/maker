<?php

namespace Unum\Maker;;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MakeVEditCommand extends MakeCommand
{
    protected $signature = 'make:v-edit {table}';
    protected $description = 'Make VuesJs edit resource file from table name.';

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
        $columns = DB::getSchemaBuilder()->getColumnListing($table);
        $skip_columns = ['id', 'created_at', 'updated_at'];
        $columns = array_diff($columns, $skip_columns);
        $foreign_keys = $this->getForeignKeys($table);
        $required_columns = $this->getRequiredColumns($table);
        $required_columns = array_diff($required_columns, $skip_columns);
        $file_content =
"<template>
    <div>
        <TopMenu></TopMenu>
        <h1>
            {{ $single.name }}
        </h1>
        <main>
            <b-container fluid=\"md\">";
    foreach($columns as $column){
        $col_info = $this->getColumnData($table, $column);
        $file_content .=
"
                <b-form-row>
                    <b-col md=\"6\">
                        <b-form-group label=\"" . $this->getLabel($column,isset($foreign_keys[$column])) . "\" label-cols=\"4\" label-align=\"right\">";
        if(!isset($foreign_keys[$column])){
            $file_content .="
                            <b-form-input
                                v-model=\"$single.$column\"
                                @change=\"save\"
";
            switch ($col_info->data_type){
                case 'bigint':
                case 'integer':
                    $file_content .= "                                type=\"number\"";
                    break;
                case 'date':
                    $file_content .= "                                type=\"date\"";
                    break;
                default:
                    $file_content .= "                                type=\"text\"";
                    break;
            }
                $file_content .= $this->isRequired($single, $column, $col_info);
                $file_content .=
"
                            >
                            </b-form-input>
                        ";
            }
            else{
                $key_table = $foreign_keys[$column];
                $file_content .=
"
                            <b-form-select
                                v-model=\"$single.$column\"
                                @change=\"save\"
                                :options=\"$key_table\"
                                value-field=\"id\"
                                text-field=\"name\"";
                $file_content .= $this->isRequired($single, $column, $col_info);
                $file_content .="
                            >
                            </b-form-select>";
            }
            $file_content .=
"
                        </b-form-group>
                    </b-col>
                </b-form-row>
";
    }
    $file_content .="
                <b-form-row>
                    <b-col>
                        <b-button @click=\"\$router.push('/$table')\">Done</b-button>
                    </b-col>
                </b-form-row>
            </b-container>
        </main>
    </div>
</template>
<script>
import TopMenu from './TopMenu'
export default {
    name: 'Edit$model',
    components: {
        'TopMenu': TopMenu
    },
    props: {
        ${single}_id: {default: null}
    },
    data () {
        return {
            $single: { id: null },";
    foreach($foreign_keys as $foreign_key => $foreign_table){
        $file_content .= "\n            $foreign_table: [],";
    }
    $file_content .=
"
        };
    },
    created () {";
    foreach($foreign_keys as $foreign_key => $foreign_table){
        $file_content .=
"
        this.\$http.get('/$foreign_table').then(response => {
            this.$foreign_table = response.data.data;
        });";
    }
    $file_content .=
"
        if(this.${single}_id !== null) {
            this.\$http.get('/$single/' + this.${single}_id).then(response => {
                this.$single = response.data.data;
            });
        }
    },
    methods: {
        save () {";
            if(!empty($required_columns)){
                $file_content .="\n            if("."(!this.$single." . implode(")||(!this.$single.", $required_columns) . ")){\n                return;\n            }";
            }
            $file_content .="
            if(this.$single.id === null){
                this.\$http.post('/$single',this.$single)
                    .then((results) => {
                        this.$single.id = results.data.data.id;
                    });
            }
            else{
                this.\$http.patch('/$single/' + this.$single.id, this.$single);
            }
        }
    }
};
</script>

";
        echo $file_content;
    }

    public function isRequired($single, $column, $col_info){
        $required = "";
        if($col_info->is_nullable == "NO"){
            $required = "
                                :state=\"$single.$column != null\"
                                required";
        }
        return $required;
    }

    public function getLabel($column,$id){
        $label = ucwords(preg_replace('/_/',' ', $column));
        if($id){
            $label = preg_replace('/ Id$/','',$label);
        }
        return $label;
    }
}
