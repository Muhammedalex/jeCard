<?php

namespace App\Http\Controllers;

use App\Models\Card;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CardController extends Controller
{
    public function index()
    {
        $records = card::all();
        return response()->json($records);
    }

    // Store a newly created record in storage
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title_color' => 'required|string|max:50',
            'background_color' => 'required|string|max:50',
            'icon_color' => 'required|string|max:50',
            'share_color' => 'required|string|max:50',
            'qr_image' => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
            'image' => 'required|image|mimes:png,jpg,jpeg|max:2048',
        ]);

        // Handle file uploads
        if ($request->hasFile('qr_image')) {
            $validated['qr_image'] = $request->file('qr_image')->store('qr_images');
        }

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('images');
        }

        $card = card::create($validated);
        return response()->json($card, 201); // Return the created record with status 201
    }

    // Display a specific record
    public function show($slug)
    {
        $user = User::where('slug', $slug)->first();
        if($user){
            $card = Card::with('links')->where('user_id',$user->id)->first();
            return response()->json([
                'success'=>true,
                'card'=>$card,
            ]);
        } else{
            return response()->json([
                'success'=>false,
                'card'=>'Failed',
            ]);
        }
        
    }

    public function update(Request $request , Card $card)
    {
        Log::info($request->all());
        $card = Card::with('links')->where('id',$card->id)->first();

        if (Auth::id() !== $card->user_id && !Auth::user()->is_admin) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $validated = $request->validate([
            'title_color' => 'nullable|string|max:20',
            'background_color' => 'nullable|string|max:20',
            'icon_color' => 'nullable|string|max:20',
            'share_color' => 'nullable|string|max:20',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
        ]);

        if ($request->hasFile('image')) {
            if ($card->image) {
                Storage::delete($card->image);
            }
            $imagePath = $request->file('image')->store('cards/images', 'public'); 
            $card->image = asset('storage/' . $imagePath); 
        }

        // Update color fields
        $card->update([
            'title_color' => $validated['title_color'] ?? $card->title_color,
            'background_color' => $validated['background_color'] ?? $card->background_color,
            'icon_color' => $validated['icon_color'] ?? $card->icon_color,
            'share_color' => $validated['share_color'] ?? $card->share_color,
        ]);
        $card->save();

        return response()->json([
            'message' => 'Card updated successfully!',
            'card' => $card
        ], 200);
    }

    // Remove a specific record from storage
    public function destroy(Card $card)
    {
        // Delete related files if they exist
        if ($card->qr_image) {
            Storage::delete($card->qr_image);
        }

        if ($card->image) {
            Storage::delete($card->image);
        }

        $card->delete();
        return response()->json(null, 204); // No content, deleted successfully
    }

}
