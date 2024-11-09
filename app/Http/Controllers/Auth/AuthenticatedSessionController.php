<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Card;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        // Attempt to authenticate the user
        if (Auth::attempt($credentials)) {
            // Authentication successful, get the authenticated user
            $user = Auth::user();
            $card = Card::with('links')->where('user_id', $user->id)->first();

            // Create a new API token for the user (you can name it whatever you want)
            $token = $user->createToken('jeCardToken')->plainTextToken;

            // Return the token and user data in the response
            return response()->json([
                'token' => $token,
                'card' => $card,
                'user' => $user,
            ]);
        }

        // Return an unauthorized response if credentials don't match
        return response()->json([
            'message' => 'Invalid credentials',
        ], 401);
    }

    /**
     * Logout the user (revoke the token).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        // Revoke the user's current token
        $request->user()->tokens->each(function ($token) {
            $token->delete();
        });

        return response()->json([
            'message' => 'Successfully logged out.',
        ]);
    }

}
