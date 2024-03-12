<?php

use App\Models\Water;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Psy\Readline\Hoa\Console;

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

Route::post('/login', function () {

    $user = User::query()->where("email", request("email"))->first();

    auth()->login($user);

    // Generate a token for the user
	$token = $user->createToken("api")->plainTextToken;

    return response([
        "message" => "Successfully logged in!",
        "token" => $token
    ]);
});

Route::post('/register', function () {
    $user = User::query()->create(request()->all());

    // generate token for user
    $token = $user->createToken("api")->plainTextToken;

    return response([
        "message" => "Successfully registered!",
        "token" => $token
    ]);
});

Route::middleware('auth:sanctum')->post('/logout', function () {
    auth()->user()->currentAccessToken()->delete();

    return response([
        "message" => "logged out successfully"
    ]);
});

Route::middleware('auth:sanctum')->get('/water', function () {
    $query = Water::query();
    $waterConsumptionData = $query->get()->where([
        "user_id" => Auth::id()
    ]);

    return response([
        "message" => "success",
        "waterConsumptionData" => $waterConsumptionData
    ]);
});

Route::middleware('auth:sanctum')->post('/water', function () {
    request()->validate([
        "drank_amount" => "required|integer",
        "drank_at" => "required"
    ]);

    $jsonBody = json_decode(request()->getContent(), true);

    $response = Water::query()->create([
        "drank_amount" => $jsonBody["drank_amount"],
        "drank_at" => Carbon::parse($jsonBody["drank_at"]),
        "user_id" => Auth::id()
    ]);

    return response([
        "message" => "success",
        "waterConsumptionData" => $response
    ]);
});

Route::middleware('auth:sanctum')->put('/water/{id}', function ($id) {
    request()->validate([
        "drank_amount" => "integer",
        "drank_at" => "date"
    ]);

    $requestData = request()->all();
    if ($requestData['drank_at']) {
        $requestData['drank_at'] = Carbon::parse($requestData['drank_at']);
    }

    Water::query()->where('id', $id)->update($requestData);

    return response([
        "message" => "success"
    ]);
});

Route::middleware('auth:sanctum')->delete('/water/{id}', function ($id) {
    Water::query()->findOrFail($id)->delete();

    return response([
        "message" => "success"
    ]);
});
