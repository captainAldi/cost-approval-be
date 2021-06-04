<?php

namespace App\Http\Controllers;

use App\Models\User;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{

    /**
     * Redirect the user to the Facebook authentication page.
     *
     * @return Response
     */
    public function redirectToProvider()
    {
        /**
         * Use this if you want to do the redirect portion from your Lumen App.  You can also do this portion from your frontend app... for example you
         * could be using https://github.com/sahat/satellizer in angular for the redirect portion, and then have it CALLBACK to your lumen app.
         * In other words, this "redirectToProvider" method is optional on the backend (you can do it from your frontend)
         */

        // $data = Socialite::driver('google')->stateless()->redirect();
        $data = Socialite::driver('google')->stateless()->redirect()->getTargetUrl();

        return response()->json([
          'data' => $data
        ]);
    }

    /**
     * Obtain the user information from Facebook.
     *
     * @return JsonResponse
     */
    public function handleProviderCallback()
    {

        // this user we get back is not our user model, but a special user object that has all the information we need
        $providerUser = Socialite::driver('google')->stateless()->user();

        // we have successfully authenticated via google at this point and can use the provider user to log us in.

        $domain = explode("@", $providerUser->getEmail())[1];

        if( $domain === 'alterra.id' || $domain === 'bsa.id'){

          $dataUser = User::where('email', $providerUser->getEmail())->first();

          if (!$dataUser) {
            $createDataUser = new User();

            $createDataUser->name               = $providerUser->getName();
            $createDataUser->photo              = $providerUser->getAvatar();
            $createDataUser->role               = 'user';
            $createDataUser->provider_name      = 'google';
            $createDataUser->provider_id        = $providerUser->getId();
            $createDataUser->email_verified_at  = date("Y-m-d H:i:s");
            $createDataUser->api_token          = $providerUser->token;
            $createDataUser->email              = $providerUser->getEmail();

            $createDataUser->save();

            return response()->json([
              'user' => $createDataUser,
              'data' => $providerUser
            ], 200);

          } else {

            $dataUser->name               = $providerUser->getName();
            $dataUser->photo              = $providerUser->getAvatar();
            $dataUser->provider_id        = $providerUser->getId();
            $dataUser->api_token          = $providerUser->token;

            $dataUser->save();

            return response()->json([
              'user' => $dataUser,
              'data' => $providerUser
            ], 200);
          }
          
        } else {

          return response()->json([
            'message' => 'Gunakan Email Alterra / BSA !'
          ], 401);

        } 
    }

}