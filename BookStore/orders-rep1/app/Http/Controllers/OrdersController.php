<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use App\LoadBalancersOrderRep1\RoundRobinLoadBalancer;


class OrdersController extends Controller
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
    public function purchase($id)
    {
        global $catalogLoadBalancer;
        $catalogServer = $catalogLoadBalancer->getNextServer();

        // Validate and process the purchase
        $purchaseResult = $this->processPurchase($id, $catalogServer);

        if ($purchaseResult['success']) {
            return response()->json(['message' => 'Purchase successful! You bought a book with this id: ' . $id]);
        } else {
            return response()->json(['error' => $purchaseResult['message']], 400);
        }
    }

    private function processPurchase($id, $catalogServer)
    {
        // Query the catalog microservice to check item availability
        $catalogResponse = Http::get("$catalogServer/catalog/$id");

        if ($catalogResponse->successful()) {
            $catalogData = $catalogResponse->json();
            $quantity = $catalogData['book']['quantity'];

            // Check if the item is in stock
            if ($quantity > 0) {
                // Perform the purchase
                // decrement the in-stock count in the catalog microservice
                $this->updateCatalog($id, $quantity - 1, $catalogServer);

                return ['success' => true];
            } else {
                return ['success' => false, 'message' => 'Item out of stock'];
            }
        } else {
            return ['success' => false, 'message' => 'Failed to check item availability'];
        }
    }

    private function updateCatalog($id, $quantity, $catalogServer)
    {
        // Update the catalog microservice with the new quantity
        $updateResponse = Http::put("$catalogServer/catalog/$id", [
            'quantity' => $quantity,
        ]);

        // Handle the response as needed
        if (!$updateResponse->successful()) {
            return ['success' => false, 'message' => 'Failed to update the item quantity'];
        }
    }
}
