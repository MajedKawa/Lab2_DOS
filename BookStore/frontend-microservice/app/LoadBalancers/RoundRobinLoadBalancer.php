<?php

namespace App\LoadBalancers;

use Illuminate\Support\Facades\Cache;

class RoundRobinLoadBalancer
{
    private $backendServers;
    private $currentIndex;

    public function __construct(array $backendServers)
    {
        $this->backendServers = $backendServers;
        // Initialize currentIndex from the cache, or to 0 if it's not set
        $this->currentIndex = Cache::get('currentIndex', 0);
    }

    public function getNextServer()
    {
        $selectedServer = $this->backendServers[$this->currentIndex];

        // Move to the next server in a round-robin fashion
        $this->currentIndex = ($this->currentIndex + 1) % count($this->backendServers);
        Cache::put('currentIndex', $this->currentIndex, 60); // Store the updated currentIndex in the cache for 60 minutes

        return $selectedServer;
    }
}
