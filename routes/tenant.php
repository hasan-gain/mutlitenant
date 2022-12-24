<?php

declare(strict_types=1);

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Http\Controllers\CsrfCookieController;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenantRouteServiceProvider.
|
| Feel free to customize them however you want. Good luck!
|
*/
Route::group(['prefix' => config('sanctum.prefix', 'sanctum')], static function () {
    Route::get('/csrf-cookie', [CsrfCookieController::class, 'show'])
        ->middleware([
            'web',
            'universal',
            InitializeTenancyByDomain::class // Use tenancy initialization middleware of your choice
        ])->name('sanctum.csrf-cookie');
});

Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {
    Route::get('/', function () {
        return tenant()->toArray();
    });
});

Route::middleware([
    'api',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->prefix('api')->group(function () {
    Route::post('login', function (Request $request) {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);
        if (Auth::attempt($credentials)) {
//            $request->session()->regenerate();

            $token = auth()->user()->createToken('device-1');

            return response()->json(['success' => true, 'token' => $token->plainTextToken, 'type' => 'tenant'], 200);
        }

        return response()->json(['success' => false, 'message' => 'Credentials not matched'], 422);
    });

    Route::middleware([
        'auth:sanctum'
    ])->group(function () {
        Route::get('/', function () {
//        print_r('This is your multi-tenant application. The id of the current tenant is ' . tenant('id')."\n\n");
            return Setting::all()->toArray();
        });

        Route::get('/user', function (Request $request) {
            return $request->user();
        });

        Route::get('create-setting', function () {
            return Setting::create(['key'=> request('key'), 'value' => request('value')]);
        });
    });
});

