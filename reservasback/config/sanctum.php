<?php

return [

/*
|----------------------------------------------------------------------
| Stateful Domains
|----------------------------------------------------------------------
|
| Requests from the following domains / hosts will receive stateful API
| authentication cookies. Typically, these should include your local
| and production domains which access your API via a frontend SPA.
|
*/
// config/sanctum.php

//'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', 'localhost:4200')),
'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', 'localhost:5173')),

/*
|----------------------------------------------------------------------
| Sanctum Guards
|----------------------------------------------------------------------
|
| This array contains the authentication guards that will be checked when
| Sanctum is trying to authenticate a request. If none of these guards
| are able to authenticate the request, Sanctum will use the bearer
| token that's present on an incoming request for authentication.
|
*/
'guard' => ['web'],

/*
|----------------------------------------------------------------------
| Expiration Minutes
|----------------------------------------------------------------------
|
| This value controls the number of minutes until an issued token will be
| considered expired. This will override any values set in the token's
| "expires_at" attribute, but first-party sessions are not affected.
|
*/
'expiration' => null,

/*
|----------------------------------------------------------------------
| Sanctum Middleware
|----------------------------------------------------------------------
|
| When authenticating your first-party SPA with Sanctum you may need to
| customize some of the middleware Sanctum uses while processing the
| request. You may change the middleware listed below as required.
|
*/
'middleware' => [
    'verify_csrf_token' => App\Http\Middleware\VerifyCsrfToken::class,
    'encrypt_cookies' => App\Http\Middleware\EncryptCookies::class,
],
];
