<?php

namespace App\Http\Middleware;

use Closure;

class CorsMiddleware
{
    /**
     * Run the request filter.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
       //RULE HEADERSNYA HARUS KITA SET SECARA SPESIFIK SEPERTI INI 
        $headers = [
            'Access-Control-Allow-Origin'      => env('VUE_APP_URL'),
            'Access-Control-Allow-Methods'     => 'POST, GET, OPTIONS, PUT, DELETE',
            'Access-Control-Allow-Credentials' => 'true',
            'Access-Control-Max-Age'           => '86400',
            'Access-Control-Allow-Headers'     => 'Content-Type, Accept, Authorization, X-Requested-With, Application'
        ];
        
        //TAPI JIKA METHOD YANG MASUK ADALAH OPTIONS
        if ($request->isMethod('OPTIONS')) {
            //MAKA KITA KEMBALIKAN BAHWA METHOD TERSEBUT ADALAH OPTIONS
            return response()->json('{"method": "OPTIONS"}', 200, $headers);
        }

        //SELAIN ITU, KITA AKAN MENERUSKAN RESPONSE SEPERTI BIASA DENGAN MENGIKUT SERTAKAN HEADERS YANG SUDAH DITETAPKAN.
        $response = $next($request);
        foreach ($headers as $key => $row) {
            $response->headers->set($key, $row);
        }
        return $response;
    }

}