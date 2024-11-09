<?php

namespace App\Http\Controllers;

use App\Models\Card;
use App\Models\CardLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class LinksController extends Controller
{
    public function store(Request $request)
    {
        // Validate the incoming request
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'url' => 'required|url',
            'icon' => 'required|string|max:255',
            'card' => 'nullable|exists:cards,id',
            // FontAwesome class (string)
        ]);

        // Create a new CardLink entry
        $cardLink = CardLink::create([
            'title' => $validated['title'],
            'link' => $validated['url'],
            'logo' => $validated['icon'], // Store the FontAwesome class
            'card_id' =>$validated['card'] ?? Auth::user()->card->id,
        ]);
        $card = Card::with('links')->where('id',$cardLink->card_id)->first();
        Log::info('card : ' . json_encode($card));  // You can use toArray() on the model instance
        return response()->json([
            'success'=>true,
            'card'=> $card,
        ]);
    }


    public function update(Request $request, CardLink $link)
    {
        // Validate the input
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'url' => 'required|url|max:255',
            'icon' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors(),
            ], 400);
        }

        // Find the link by ID
        // $link = CardLink::find($id);
        
        if (!$link) {
            return response()->json([
                'error' => 'Link not found.',
            ], 404);
        }

        // Update the link's details
        $link->title = $request->input('title');
        $link->link = $request->input('url');
        $link->logo = $request->input('icon') ?? $link->icon;  // If no icon is provided, keep the existing one

        // Save the changes
        $link->save();
        $card = Card::with('links')->where('id',$link->card_id)->first();
        return response()->json([
            'success' => true,
            'card' => $card,  
        ], 200);
    }

    public function destroy(CardLink $link)
{
    // Find the link by ID

    // Check if the link exists
    if (!$link) {
        return response()->json(['message' => 'Link not found.'], 404);
    }

    // Delete the link
    $link->delete();

    // Return a success response
    $card = Card::with('links')->where('id',$link->card_id)->first();
    return response()->json([
        'success' => true,
        'card' => $card,  
    ], 200);}

}
