<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use function GuzzleHttp\Promise\rejection_for;

class ProviderController extends Controller
{
    public function redirect($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    public function callback($provider)
    {
        try {
            $SocialUser = Socialite::driver($provider)->user();
            if(User::where('email', $SocialUser->getEmail())->exists()){
                return redirect('/login')->withErrors(['email' => 'This email uses different method to login.']);
            }
            $user = User::where([
                'provider' => $provider,
                'provider_id' => $SocialUser->id
            ])->first();
            if (!$user){
                $user = User::create([
                    'name' => $SocialUser->getName(),
                    'email' => $SocialUser->getEmail(),
                    'username' => $SocialUser->getNickname() ?? str_replace('@gmail.com' , '',$SocialUser->getEmail()),
                    'password' => bcrypt('password'),
                    'provider' => $provider,
                    'provider_id' => $SocialUser->getId(),
                    'provider_token' => $SocialUser->token,
                ]);
            }
            Auth::login($user);
            return redirect('/dashboard');
        } catch (\Exception $e){
            return redirect('/login');
        }
    }
}
