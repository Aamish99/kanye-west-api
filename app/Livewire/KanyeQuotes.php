<?php

namespace App\Livewire;

use Illuminate\Http\Request;
use Livewire\Component;
use Illuminate\Support\Facades\Http;

class KanyeQuotes extends Component
{


    public $quotes = [];

    public function mount()
    {
        $this->fetchQuotes();
    }

    public function fetchQuotes()
    {

        $token = session('api_token');

        if(!$token){
            $token = $this->getToken();
        }

        $response = Http::withToken($token)
            ->get(url('/api/quotes'));

        if ($response->successful()) {
            $this->quotes = $response->json();
        } else {
            $this->quotes = ['Error fetching quotes.'];
        }
    }

    public function refreshQuotes()
    {
        $this->fetchQuotes();
    }

    public function render()
    {
        return view('livewire.kanye-quotes');
    }



    public function getToken()
    {
        $user = auth()->user();
        $user->tokens()->delete();
        $token = $user->createToken('API Token')->plainTextToken;

        session(['api_token' => $token]);

        return $token;
    }


}
