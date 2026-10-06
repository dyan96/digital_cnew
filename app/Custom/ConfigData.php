<?php

namespace App\Custom;

use Illuminate\Database\Eloquent\Model;

class ConfigData extends Model
{
    //

    public static function getPetticashAccountByLocationId($location_id)
    {
        switch ($location_id)
        {
            case 1 : 
                return 7;
                break;
            case 2 : 
                return 8;
                break;
            case 3 : 
                return 9;
                break;
            case 4 : 
                return 10;
                break;
            case 5 : 
                return 11;
                break;
            case 6 : 
                return 12;
                break;
        }
        return null;
    }

}
