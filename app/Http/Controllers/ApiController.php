<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ApiController extends Controller
{
   
public function auth(Request $request)
    {
        $pem = $request->pem;
        if($pem){
            $b64 = base64_encode($pem);
            $result = array();
            $res = exec('cd /opt/ksign && LD_LIBRARY_PATH="/opt/kalkancrypt:/opt/kalkancrypt/lib/engines" php secure.php '.$b64, $result);
            if($result[0] == ''){
                $j = $result[1];
            }else{
                $j = $result[0];
            }

            $p = json_decode(base64_decode($j));
            $ok = false;
            $good = false;
            if($p){
                $ok = (strpos($p->validate_ocsp, 'OK') !== false) ? true : false;
                $good = (strpos($p->validate_ocsp, 'good') !== false) ? true : false;
            }
            
            if($ok && $good){
                return response()->json([
                    'success' => true,
                    'data' => $p
                ]);
            }else{
                if(!$ok || !$good){
                    return response()->json([
                        'success' => false,
                        'data' => 'Ваш сертификат отозван!'
                    ]); 
                }
            }
        }else{
            return response()->json([
                'success' => false,
                'data' => 'Key is not readable!'
            ]); 
        }
    }

    public function sign(Request $request)
    {
        $hash = $request->hash;
        $sign = $request->sign;

        if($hash && $sign){
            $check = array();
            exec('cd /opt/ksign && LD_LIBRARY_PATH="/opt/kalkancrypt:/opt/kalkancrypt/lib/engines" php check.php '.$hash.' '.$sign, $check);
            $j = json_decode(base64_decode($check[0]));

            if($j->status == 'SUCCESS') {
                return response()->json(['data' => $j]);
            }else{
                return response()->json(['error' => 'Подпись не прошла проверку!']);  
            }
        }else{
            return response()->json(['error' => 'Hash or Sign is not requested!']); 
        }
    }

}
