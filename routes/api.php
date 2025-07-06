<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Cookie;
use Tymon\JWTAuth\Facades\JWTAuth;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::group(['namespace'=>'App\Http\Controllers'], function() {
    Route::group(['prefix'=>'test', 'as'=>'test.'], function() {
        Route::get('/',['as'=>'index', 'uses'=>'TestController@index']);
        Route::post('/create',['as'=>'store','uses'=>'TestController@store']);
    });

    Route::group(['prefix'=>'auth', 'as'=>'auth.'], function() {
        Route::post('/login',['as'=>'login','uses'=>'AuthController@auth']);
        Route::get('/user',['as'=>'user','uses'=>'AuthController@checkUser']);
        Route::post('/logout',['as'=>'logout','uses'=>'AuthController@logout']);
        Route::post('/register',['uses'=>'AuthController@register']);
    });


});

//This route is the default for implementing default laravel email verification
Route::get('/email/verify/{id}/{hash}', function (Request $request, $id, $hash) {
    // $request->fulfill(); This one is used if the user is currently logged in, but if not just manually find the User through id
    $user = User::findOrFail($id);

    if (!$user) {
        return response()->json(['message' => 'User not found'], 404);
    }

    // if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
    //     return response()->json(['message' => 'Invalid verification link'], 400);
    // }

    // if ($user->hasVerifiedEmail()) {
    //     return response()->json(['message' => 'Email already verified'], 400);
    // }

    // $user->markEmailAsVerified();
    // event(new Verified($user));
    $user->email_verified_at = now();
    $user->save();

    return response()->json(['message' => 'Email verified successfully!']); //returns json response that the email is verified
})->middleware(['signed'])->name('verification.verify');


Route::post('/email/verify',['as'=>'api.verify.email','uses'=>'App\Http\Controllers\AuthController@verify_email']);

Route::group(['prefix'=>'freelances','as'=>'freelances.','namespace'=>'App\Http\Controllers'], function() {

    Route::group(['prefix'=>'client','as'=>'client'], function() {  
        Route::get('/',['uses'=>'FreelanceController@getClientFreelanceProjects']);
        Route::post('/',['uses'=>'FreelanceController@store']);
        Route::put('/{slug?}',['uses'=>'FreelanceController@update']);
        Route::get('/{slug?}/checkIfClientCanApprove',['uses'=>'FreelanceController@checkIfClientCanApprove']);
    });    
    
    Route::get('/',['uses'=>'FreelanceController@index']);
    Route::get('/{slug?}',['uses'=>'FreelanceController@show']);
    Route::get('/{slug?}/proposals',['uses'=>'FreelanceController@showProposals']);
    Route::put('/{id?}',['uses'=>'FreelanceController@processProposal']);
});

Route::group(['prefix'=>'proposals','as'=>'proposals.','namespace'=>'App\Http\Controllers'], function() {
    Route::group(['prefix'=>'freelancer','as'=>'freelancer.'], function () {
        Route::get('/',['uses'=>'ProposalController@getFreelancerProposals']);
        Route::put('/{id?}',['uses'=>'ProposalController@updateProposalStatus']);
    });
    Route::post('/{slug?}',['uses'=>'ProposalController@store', 'middleware'=>['api.auth','api.check_if_freelancer']]);
    Route::get('/{slug?}/check',['uses'=>'ProposalController@checkIfFreelancerCanApply']);
});

Route::group(['prefix'=>'profile','as'=>'profile.','namespace'=>'App\Http\Controllers'], function() {
    Route::get('/{username?}',['uses'=>'ProfileController@view']);
    Route::post('/edit/update_profile',['uses'=>'ProfileController@update_profile']);
});

Route::group(['prefix'=>'message','as'=>'message.','namespace'=>'App\Http\Controllers'], function() {
    Route::get('/inbox',['uses'=>'MessageController@inbox']);
    Route::get('/inbox/{username?}',['uses'=>'MessageController@retrieveMessages']);
    Route::post('/inbox/{username?}',['uses'=>'MessageController@sendMessage']);
    Route::get('/recent-contacts',['uses'=>'MessageController@retrieveRecentContacts']);
});

Route::group(['namespace'=>'App\Http\Controllers'], function() {
    Route::post('/broadcasting/auth', ['uses'=>'BroadcastController@authenticate']);
});
