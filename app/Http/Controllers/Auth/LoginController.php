<?php

namespace App\Http\Controllers\Auth;

use App\Exceptions\VerifyEmailException;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    protected function auth(Request $request)
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
            return response()->json(['data' => $p]);
        }else{
            return response()->json(['error' => 'Key is not readable!']); 
        }
    }

    protected function sign(Request $request)
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

    /**
     * Attempt to log the user into the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function attemptLogin(Request $request)
    {
        $token = $this->guard()->attempt($this->credentials($request));

        if (! $token) {
            return false;
        }

        $user = $this->guard()->user();
        if ($user instanceof MustVerifyEmail && ! $user->hasVerifiedEmail()) {
            return false;
        }

        $this->guard()->setToken($token);

        return true;
    }

    /**
     * Send the response after the user was authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    protected function sendLoginResponse(Request $request)
    {
        $this->clearLoginAttempts($request);

        $token = (string) $this->guard()->getToken();
        $expiration = $this->guard()->getPayload()->get('exp');

        return response()->json([
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $expiration - time(),
        ]);
    }

    /**
     * Get the failed login response instance.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function sendFailedLoginResponse(Request $request)
    {
        $user = $this->guard()->user();
        if ($user instanceof MustVerifyEmail && ! $user->hasVerifiedEmail()) {
            throw VerifyEmailException::forUser($user);
        }

        throw ValidationException::withMessages([
            $this->username() => [trans('auth.failed')],
        ]);
    }

    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        $this->guard()->logout();
    }
}
