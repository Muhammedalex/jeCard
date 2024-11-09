<?php

namespace App\Http\Controllers;

use App\Models\Card;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules;

class UserController extends Controller
{

    public function index(Request $request)
    {
        // Fetch users except the user with ID 1, paginate the results (50 users per page)
        $users = User::with('card.links')
                    ->where('id', '<>', 1) // Exclude user with ID 1
                    ->paginate(50);
    
        // Return a JSON response with the paginated users data
        return response()->json([
            'success' => true,
            'users' => $users
        ]);
    }

    // store 

    public function store(Request $request)
    {
        if (!Auth::user()->role == 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'You Are Not An Admin',
            ], 403);
        }
        // Validate the incoming request
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', Rules\Password::defaults()],
            'domin_name' => ['required', 'unique:users,slug'],
        ]);

        // Create the user without logging them in
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'slug' => $request->domin_name,
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

        return response()->json([
            'success' => true,
            'user' => $user,
        ]);
    }

    public function update(Request $request, User $user)
    {
        // Check if the authenticated user is an admin or the user being updated
        if ((!Auth::user()->role == 'admin') && Auth::id() !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'You Are Not An Admin',
            ], 403);
        }
    
        // Validate the incoming request
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,' . $user->id], 
            'password' => ['nullable', 'string', 'min:8'],
            'domin_name' => ['required', 'unique:users,slug,' . $user->id], 
        ]);
    
        // Update the user's details
        $user->name = $request->name;
        $user->email = $request->email;
        $user->slug = $request->domin_name;
    
        // If a new password is provided, hash and update it
        if ($request->filled('password')) {
            Log::info($request->password);
            $user->password = Hash::make($request->password);
        }
    
        // Save the updated user
        $user->save();
    
        return response()->json([
            'success' => true,
            'message' => 'User updated successfully.',
            'userData' => $user
        ]);
    }
    
    public function destroy(User $user){
        if($user->id == 1){
            return response()->json([
                'message'=>'Something Went Wrong',
            ]);
        }

        $user->delete();
        return response()->json([
            'message'=>'user deleted successfully',
        ]);
    }

    public function getUser(Request $request , User $user=null)
    {
        // Get the currently authenticated user
        $userToGet = $user;
        $currentUser = Auth::user();
        $card = Card::with('links')->where('user_id', $currentUser->id)->first();
        // Return the user data
        return response()->json([
            'userData'=>$userToGet,
            'user' => $currentUser,
            'card' => $card,
        ]);
    }
}
