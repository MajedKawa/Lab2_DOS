<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\LoadBalancers\RoundRobinLoadBalancer;

class FrontendController extends Controller
{
    private $catalogLoadBalancer;
    private $ordersLoadBalancer;

    private $cache; // The in-memory cache

    public function __construct()
    {
        $con = [
            'catalogServers' => ['http://192.168.1.71:8000', 'http://192.168.1.71:8010', 'http://192.168.1.71:8020'],
            'ordersServers' => ['http://192.168.1.71:8001', 'http://192.168.1.71:8002', 'http://192.168.1.71:8003'],
        ];

        $this->catalogLoadBalancer = new RoundRobinLoadBalancer($con['catalogServers']);
        $this->ordersLoadBalancer = new RoundRobinLoadBalancer($con['ordersServers']);
        $this->cache = []; // Initialize the cache
    }

    public function search($topic)
    {
        // Check if the topic is in the cache
        if (isset($this->cache[$topic])) {
            return response()->json($this->cache[$topic], 200);
        }
        $startTimestamp = microtime(true); // Record the start time

        $catalogServer = $this->catalogLoadBalancer->getNextServer();

        try {
            $response = Http::get("$catalogServer/catalog/search/$topic");

            if ($response->successful()) {
                $data = $response->json();
                $endTimestamp = microtime(true); // Record the end time
                $responseTime = $endTimestamp - $startTimestamp;
                $data['log'] = "Search request took {$responseTime} seconds, the request was from $catalogServer";
                // Store the data in the cache
                $this->cache[$topic] = $data;
                return response()->json($data, 200);
            } else {
                throw new \Exception('Failed to retrieve data from catalog');
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function info($id)
    {
        $startTimestamp = microtime(true); // Record the start time

        $catalogServer = $this->catalogLoadBalancer->getNextServer();

        try {
            $response = Http::get("$catalogServer/catalog/$id");

            if ($response->successful()) {
                $data = $response->json();
                $endTimestamp = microtime(true); // Record the end time
                $responseTime = $endTimestamp - $startTimestamp;
                $data['log'] = "Info request took {$responseTime} seconds, the request was from $catalogServer";
                return response()->json($data, 200);
            } else {
                throw new \Exception('Failed to retrieve item information from catalog');
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function purchase($id)
    {
        $startTimestamp = microtime(true); // Record the start time

        $ordersServer = $this->ordersLoadBalancer->getNextServer();

        try {
            $response = Http::post("$ordersServer/orders/purchase/$id");

            if ($response->successful()) {
                $endTimestamp = microtime(true); // Record the end time
                $responseTime = $endTimestamp - $startTimestamp;
                return response()->json(['message' => 'Purchase successful! You bought the book with this id: ' . $id . " <br> Purchase request took $responseTime seconds, the request was from $ordersServer"], );
            } else {
                throw new \Exception('Failed to process purchase');
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

}
