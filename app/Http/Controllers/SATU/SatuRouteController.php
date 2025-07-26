<?php

namespace App\Http\Controllers\SATU;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Route;

use App\Models\SATU\Routes;

class SatuRouteController extends Controller
{
    public static function SatuRouteGet()
    {
        $list = Routes::GetRoute('GET');

        foreach ($list as $key => $val) 
        {
            Route::get(''.$val['url_masked_name'].''.$val['url_parameter'].'', ''.$val['controller_namespaces'].'@'.$val['function_name'].'')->middleware('verify.token.scope:'.$val['scope_name'].'')->name(''.$val['url_name'].'');
        }
    }

    public static function SatuRoutePost()
    {
        $list = Routes::GetRoute('POST');

        foreach ($list as $key => $val) 
        {
            Route::post(''.$val['url_masked_name'].''.$val['url_parameter'].'', ''.$val['controller_namespaces'].'@'.$val['function_name'].'')->name(''.$val['url_name'].'')->middleware('verify.token.scope:'.$val['scope_name'].'')->name(''.$val['url_name'].'');
        }
    }

    public static function SatuRoutePut()
    {
        $list = Routes::GetRoute('PUT');

        foreach ($list as $key => $val) 
        {
            Route::put(''.$val['url_masked_name'].''.$val['url_parameter'].'', ''.$val['controller_namespaces'].'@'.$val['function_name'].'')->name(''.$val['url_name'].'')->middleware('verify.token.scope:'.$val['scope_name'].'')->name(''.$val['url_name'].'');
        }
    }

    public static function SatuRouteDelete()
    {
        $list = Routes::GetRoute('DELETE');

        foreach ($list as $key => $val) 
        {
            Route::delete(''.$val['url_masked_name'].''.$val['url_parameter'].'', ''.$val['controller_namespaces'].'@'.$val['function_name'].'')->name(''.$val['url_name'].'')->middleware('verify.token.scope:'.$val['scope_name'].'')->name(''.$val['url_name'].'');
        }
    }
}
