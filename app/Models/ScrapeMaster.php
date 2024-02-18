<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
class ScrapeMaster extends Model
{

    protected $table = 'scrap_master';
    protected $primaryKey = 'id';

    public static function insertSrapeMaster( $request ) {
       

        try {
            $id = Self::insertGetId( $request);
            return $id;
        } catch (QueryException $e) {
            return 0;
        }
         
        die();
    }
}
