<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\LoadBalancers\RoundRobinLoadBalancer;


class FrontendController extends Controller
{
    private $catalogLoadBalancer;
    private $ordersLoadBalancer;

    public function __construct()
    {
        $con = [
            'catalogServers' => ['http://192.168.1.71:8000', 'http://192.168.1.71:8010', 'http://192.168.1.71:8020'],
            'ordersServers' => ['http://192.168.1.71:8001', 'http://192.168.1.71:8002', 'http://192.168.1.71:8003'],
        ];

        $this->catalogLoadBalancer = new RoundRobinLoadBalancer($con['catalogServers']);
        $this->ordersLoadBalancer = new RoundRobinLoadBalancer($con['ordersServers']);
    }

    public function search($topic)
    {
        $startTimestamp = microtime(true); // Record the start time

        $catalogServer = $this->catalogLoadBalancer->getNextServer();

        try {
            $response = Http::get("$catalogServer/catalog/search/$topic");

            if ($response->successful()) {
                $data = $response->json();
                return response()->json($data, 200);
            } else {
                throw new \Exception('Failed to retrieve data from catalog');
            }
        } catch (\Exception $e) {
            Log::error("Error in search: " . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        } finally {
            $endTimestamp = microtime(true); // Record the end time
            $responseTime = $endTimestamp - $startTimestamp;
            // Log the response time
            Log::info("Search request took {$responseTime} seconds, the request was from $catalogServer");
        }
    }

    public function info($id)
    {
        $catalogServer = $this->catalogLoadBalancer->getNextServer();

        try {
            $response = Http::get("$catalogServer/catalog/$id");

            if ($response->successful()) {
                $data = $response->json();
                return response()->json($data, 200);
            } else {
                throw new \Exception('Failed to retrieve item information from catalog');
            }
        } catch (\Exception $e) {
            Log::error("Error in info: " . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function purchase($id)
    {
        $ordersServer = $this->ordersLoadBalancer->getNextServer();

        try {
            $response = Http::post("$ordersServer/orders/purchase/$id");

            if ($response->successful()) {
                return response()->json(['message' => 'Purchase successful! You bought the book with this id: ' . $id]);
            } else {
                throw new \Exception('Failed to process purchase');
            }
        } catch (\Exception $e) {
            Log::error("Error in purchase: " . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

}
