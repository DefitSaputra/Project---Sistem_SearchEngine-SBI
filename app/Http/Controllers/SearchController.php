<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SearchController extends Controller
{
    public function index()
    {
        return view('search.index');
    }

    public function search(Request $request)
    {
        $query = $request->input('q');
        
        if (empty($query)) {
            return redirect()->route('dashboard')->with('error', 'Please enter a search query.');
        }

        try {
            $apiKey = env('GOOGLE_API_KEY');
            $searchEngineId = env('SEARCH_ENGINE_ID');
            
            $response = Http::get('https://www.googleapis.com/customsearch/v1', [
                'key' => $apiKey,
                'cx' => $searchEngineId,
                'q' => $query,
                'num' => 10, // Number of results to return
                'start' => $request->input('start', 1), // For pagination
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                return view('search.results', [
                    'results' => $data['items'] ?? [],
                    'searchInfo' => $data['searchInformation'] ?? [],
                    'query' => $query,
                    'currentPage' => intval($request->input('start', 1)),
                    'totalResults' => $data['searchInformation']['totalResults'] ?? 0,
                ]);
            } else {
                Log::error('Google Search API Error', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                
                return redirect()->route('dashboard')->with('error', 'Search service temporarily unavailable. Please try again later.');
            }
        } catch (\Exception $e) {
            Log::error('Search Error: ' . $e->getMessage());
            return redirect()->route('dashboard')->with('error', 'An error occurred while searching. Please try again.');
        }
    }
}