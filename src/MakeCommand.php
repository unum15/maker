<?php

namespace Unum\Maker;;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MakeCommand extends Command
{
     /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }
    
    protected function title($word){
        return ucwords(preg_replace('/_/',' ', $word));
    }
    
    
    public function getColumnData($table, $column){
        $sql = "
            SELECT
                is_nullable,
                data_type,
                character_octet_length
                
            FROM 
                information_schema.columns
            WHERE
                table_schema = 'public'
                AND
                table_name = '$table'
                AND
                column_name = '$column'
            ORDER BY
                ordinal_position;
        ";
        $col_info = DB::select($sql);
        return $col_info[0];
    }
    
    public function getForeignKeys($table){
        $sql = "
            SELECT
                kcu.column_name,
                ccu.table_name
            FROM 
                information_schema.table_constraints AS tc 
                LEFT JOIN information_schema.key_column_usage AS kcu ON (tc.constraint_name = kcu.constraint_name AND tc.table_schema = kcu.table_schema)
                LEFT JOIN information_schema.constraint_column_usage AS ccu ON (ccu.constraint_name = tc.constraint_name AND ccu.table_schema = tc.table_schema)
            WHERE 
                tc.constraint_type = 'FOREIGN KEY' 
                AND tc.table_name='$table' 
                AND tc.table_schema = 'public' 
        ";
        $foreign_keys_array = DB::select($sql);
        $foreign_keys = [];
        foreach($foreign_keys_array as $foreign_key){
            $foreign_keys[$foreign_key->column_name] = $foreign_key->table_name;
        }
        return $foreign_keys;
    }
    

}
