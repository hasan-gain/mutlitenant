<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/', function () {
    return ['success' => true, 'action' => 'home page'];
});

Route::post('login', function (Request $request) {
    $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required'],
    ]);
    if (Auth::attempt($credentials)) {
//            $request->session()->regenerate();

        $token = auth()->user()->createToken('device-1');

        return ['success' => true, 'token' => $token->plainTextToken, 'type' => 'landlord'];
    }

    return ['success' => false, 'message' => 'Credentials not matched'];
});

Route::post('register', function (Request $request) {
    $credentials = $request->validate([
        'name' => ['required', 'string'],
        'email' => ['required', 'email', 'unique'],
        'password' => ['required', 'confirmed'],
    ]);

    \App\Models\User::create([
        'name' => request('name'),
        'email' => request('email'),
        'password' => bcrypt(request('password')),
    ]);

    if (Auth::attempt($credentials)) {
        $token = auth()->user()->createToken('device-1');

        return ['success' => true, 'token' => $token->plainTextToken, 'type' => 'landlord'];
    }

    return ['success' => false, 'message' => 'Could not create User'];
});



Route::middleware([
    'auth:sanctum'
])->group(function () {
//    Route::get('/', function () {
//        return ['success' => true, 'action' => 'landlod logged in'];
//    });

    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('create-tenant', function (Request $request) {
        $request->validate([
            'tenant' => ['required', 'string'],
        ]);
        $tenant = App\Models\Tenant::create(['id' => request('tenant')]);
        $tenant->domains()->create(['domain' => request('tenant').'.multitenant.test']);
        return ['success' => true, 'domain' => request('tenant').'.multitenant.test'];
    });

//    Route::get('delete-tenant', function () {
//        return App\Models\Tenant::find(request('tenant'))->delete();
//    });
});
