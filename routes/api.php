<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\FaceRegistrationController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::post('/login', function (Request $request) {
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
        'device_name' => 'required|string',
    ]);

    $user = User::where('email', $request->email)->first();

    if (! $user || ! Hash::check($request->password, $user->password)) {
        return response()->json([
            'success' => false,
            'message' => 'The provided credentials are incorrect.',
        ], 401);
    }

    // Revoke existing tokens (optional security measure)
    $user->tokens()->delete();

    $token = $user->createToken($request->device_name)->plainTextToken;

    return response()->json([
        'success' => true,
        'token' => $token,
        'user' => $user->only(['id', 'name', 'email']), // Only return necessary user data
    ]);
});

Route::post('/register', function (Request $request) {
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|min:8|confirmed', // Increased minimum password length
        'device_name' => 'required|string',
    ]);

    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
    ]);

    $token = $user->createToken($request->device_name)->plainTextToken;

    return response()->json([
        'success' => true,
        'token' => $token,
        'user' => $user->only(['id', 'name', 'email']),
    ]);
});

// Public student registration endpoint
Route::post('/students/register', [StudentController::class, 'registerStudent']);

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    // User management
    Route::get('/user', function (Request $request) {
        return $request->user()->only(['id', 'name', 'email']); // Only return necessary data
    });
    
    Route::post('/logout', function (Request $request) {
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    });

    // Student routes
    Route::prefix('students')->group(function () {
        Route::get('/{id}', [StudentController::class, 'getStudent'])
            ->where('id', '[0-9]+'); // Ensure ID is numeric
        
        Route::put('/{id}', [StudentController::class, 'updateStudent'])
            ->where('id', '[0-9]+');
    });

    // Face registration
    Route::post('/face/register', [FaceRegistrationController::class, 'register'])
        ->middleware('throttle:10,1'); // Rate limiting (10 requests per minute)
});