<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
class FrontendController extends Controller
{
    public function search($topic)
    {
        $response = Http::get('http://192.168.1.71:8000/catalog/search/'. $topic);

        if ($response->successful()) {
            $data = $response->json();
            return response()->json($data, 200);
        } else {
            return response()->json(['error' => 'Failed to retrieve data from catalog'], 500);
        }
    }

    public function info($id)
    {
        $response = Http::get('http://192.168.1.71:8000/catalog/' . $id);

        if ($response->successful()) {
            $data = $response->json();
            return response()->json($data, 200);
        } else {
            return response()->json(['error' => 'Failed to retrieve item information from catalog'], 500);
        }
    }

    public function purchase($id)
    {
        $response = Http::post('http://192.168.1.71:8001/orders/purchase/' . $id);

        if ($response->successful()) {
            return response()->json(['message' => 'Purchase successful! You bought the book with this id: ' . $id]);
        } else {
            return response()->json(['error' => 'Failed to process purchase'], 500);
        }
    }
}
