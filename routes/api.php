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

//once routes are added into api.php, they will be prefixed with /api automatically as route

// Route::get('/',['uses'=>'App\Http\Controllers\TestController@test']);

Route::group(['namespace'=>'App\Http\Controllers'], function() {
    Route::group(['prefix'=>'test', 'as'=>'test.'], function() {
        Route::get('/',['as'=>'index', 'uses'=>'TestController@index']);
        Route::post('/create',['as'=>'store','uses'=>'TestController@store']);
    });

    Route::group(['prefix'=>'auth', 'as'=>'auth.'], function() {
        Route::post('/login',['as'=>'login','uses'=>'AuthController@auth']);
        Route::get('/user',['as'=>'user','uses'=>'AuthController@checkUser']);
        Route::post('/logout',['as'=>'logout','uses'=>'AuthController@logout']);
        Route::post('/register',['as'=>'register','uses'=>'AuthController@register']);
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

// Resend verification email
// Route::post('/email/resend', function (Request $request) {
//     $user = User::findOrFail($request->user()->id);
//     if ($user->hasVerifiedEmail()) {
//         return response()->json(['message' => 'Email already verified.'], 400);
//     }
//     $user->sendEmailVerificationNotification();
//     return response()->json(['message' => 'Verification email resent.']);
// })->name('verification.send');

// Route::get('/test', [App\Http\Controllers\TestController::class, 'index']);
// Route::post('/test/create',['as'=>'test.create','uses'=>'App\Http\Controllers\TestController@store']);
// Route::post('/test/create', [App\Http\Controllers\TestController::class, 'store']);

Route::post('/email/verify',['as'=>'api.verify.email','uses'=>'App\Http\Controllers\AuthController@verify_email']);

Route::group(['prefix'=>'freelances','as'=>'freelances.','namespace'=>'App\Http\Controllers'], function() {
    //'middleware'=>['api.auth','api.check_if_client']
    Route::group(['prefix'=>'client','as'=>'client'], function() {  
        Route::get('/',['uses'=>'FreelanceController@getClientFreelanceProjects']); // VIEW ALL FREELANCES FOR THE CLIENT
        Route::post('/',['uses'=>'FreelanceController@store']);
        Route::put('/{slug?}',['uses'=>'FreelanceController@update']);
    });    

    Route::get('/',['uses'=>'FreelanceController@index']); //api/freelances - GET all freelances GENERAL
    // Route::
    Route::get('/{slug?}',['uses'=>'FreelanceController@show']); //api/freelances/{id} - GET single freelance > show 
    // Route::get('/{slug?}/proposals',['uses'=>'FreelanceController@showAllProposals']); //api/freelances/{slug}/proposals - GET all proposal for a specific project
    //PROBLEM!! the freelances/{slug?} is conflicting with freelances/client/* 
    //for client 

});

Route::group(['prefix'=>'users','as'=>'users.','namespace'=>'App\Http\Controllers'], function() {
    // remove this this is done
    Route::get('/freelances',['uses'=>'UserController@getUserFreelances']); //api/user/freelances - GET user's freelances  > getUserFreelances
    // remove this this is not yet done
    Route::get('/proposals',['uses'=>'UserController@getUserProposals']); //api/user/proposal - GET user's proposals > getUserProposals

    Route::get('/freelances/{slug?}/proposals',['uses'=>'UserController@getProposalsFromSingleFreelance','middleware'=>['api.check_if_own_freelance_project']]);
}); 

Route::group(['prefix'=>'proposals','as'=>'proposals.','namespace'=>'App\Http\Controllers'], function() {
    Route::group(['prefix'=>'freelancer','as'=>'freelancer.'], function () {
        Route::get('/',['uses'=>'ProposalController@getFreelancerProposals']);
        Route::put('/{id?}',['uses'=>'ProposalController@updateProposalStatus']);
    });
    Route::post('/{slug?}',['uses'=>'ProposalController@store', 'middleware'=>['api.auth','api.check_if_freelancer']]); // POST apply to a freelance > store
    Route::get('/{slug?}',['uses'=>'ProposalController@show']); // GET single proposal > show
    Route::get('/{slug?}/check',['uses'=>'ProposalController@checkIfFreelancerCanApply']); // GET check if user has duplicate application to a project
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

// Broadcast::routes(); 

// Route::post('/broadcasting/auth', function (Request $request) {
//     try {
//         // Grab JWT from HttpOnly cookie
//         $token = Cookie::get('auth_token');
//         if (!$token) {
//             throw new \Exception('Token not found');
//         }

//         // Authenticate user from JWT
//         $user = JWTAuth::setToken($token)->authenticate();

//         // 🔑 Set user for default Laravel auth system (which Broadcast::auth uses)
//         Auth::setUser($user);

//         \Log::info('Authenticated user for broadcasting', ['id' => $user->id]);

//         return Broadcast::auth($request);
//     } catch (\Exception $e) {
//         \Log::error('Broadcast Auth Error', ['message' => $e->getMessage()]);
//         return response()->json(['error' => 'Unauthorized'], 401);
//     }
// });




/*
1. Overall route group with name space of controller
2. Middlewares for authenticated and authorized routes
3. Grouped routes:
    > auth (login, logout, reg , checkuser , email etc)
    > freelances 
    > proposals
    > users
    > admin (data monitoring, dashboard etc)
    BELOW !!
*/

// Route::group(['namespace'=>'App\Http\Controllers'], function() {
//     Route::group(['as'=>'auth','prefix'=>'auth.','middleware'=>''], function() { //guest middleware
//         //
//     });

//     Route::group(['middleware'=>''], function() { //auth middleware
//         Route::group(['as'=>'freelances','prefix'=>'freelances.','middleware'=>''], function() {
//             //
//         });
    
//         Route::group(['as'=>'proposals','prefix'=>'proposals.','middleware'=>''], function() {
//             //
//         });
    
//         Route::group(['as'=>'users','prefix'=>'users.','middleware'=>''], function() {
//             //
//         });
    
        
//         Route::group(['as'=>'admins','uses'=>'admins.','middleware'=>''], function() {
//             //
//         });
//     });
// });