<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

use Laravel\Passport\Passport;

use App\Models\SATU\AuthCode;
use App\Models\SATU\Scopes;
use App\Models\SATU\Token;
use App\Models\SATU\Client;
use App\Models\SATU\PersonalAccessClient;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Passport::useTokenModel(Token::class);
        // Passport::useRefreshTokenModel(RefreshToken::class);
        // Passport::useAuthCodeModel(AuthCode::class);
        // Passport::useClientModel(Client::class);
        // Passport::usePersonalAccessClientModel(PersonalAccessClient::class);


        // $var = array();
        // $list = Scopes::get()->toArray();

        // foreach ($list as $key => $val) 
        // {
        //     $var[$val['name']] = $val['description']; 
        // }
        // Passport::tokensCan($var);
    }
}
