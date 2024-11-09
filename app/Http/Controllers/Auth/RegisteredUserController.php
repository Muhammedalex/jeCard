<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Card;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class RegisteredUserController extends Controller
{
    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): Response
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', Rules\Password::defaults()],
            'domin_name' => ['required', 'unique:users,slug'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->string('password')),
            'slug' => $request->domin_name
        ]);
        if ($user) {
            Card::create([
                'image' => 'add-profile-bigger.jpg',
                'user_id' => $user->id,
                'title_color' => '#076e29',
                'background_color' => '#1ca044',
                'icon_color' => '#f47171',
                'share_color' => '#1411df',
            ]);
        }
        event(new Registered($user));

        Auth::login($user);

        return response()->noContent();
    }
}
