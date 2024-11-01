<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class QuoteController extends Controller
{
    public function getQuotes()
    {
        $uniqueQuotes = [];
        $attempts = 0;

        while (count($uniqueQuotes) < 5 && $attempts < 10) {
            try {
                $response = Http::get('https://api.kanye.rest/');
                $quote = $response->json('quote');

                if (!in_array($quote, $uniqueQuotes)) {
                    $uniqueQuotes[] = $quote;
                }
            } catch (RequestException $e) {
                return response()->json(['error' => 'Failed to fetch quotes'], 500);
            }
            $attempts++;
        }

        return response()->json($uniqueQuotes);
    }



    public function getToken(Request $request)
    {
        /*$user = $request->user();

        $user->tokens()->delete();

        $token = $user->createToken('API Token')->plainTextToken;*/

        return response()->json(['token' => 'sdfsdf']);
    }
}
