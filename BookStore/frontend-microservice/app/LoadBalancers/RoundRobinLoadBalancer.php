<?php

namespace App\LoadBalancers;

class RoundRobinLoadBalancer
{
    private $backendServers;
    private $currentIndex;

    public function __construct(array $backendServers)
    {
        $this->backendServers = $backendServers;
        $this->currentIndex = 0;
    }

    public function getNextServer()
    {
        $selectedServer = $this->backendServers[$this->currentIndex];

        // Move to the next server in a round-robin fashion
        $this->currentIndex = ($this->currentIndex + 1) % count($this->backendServers);

        return $selectedServer;
    }
}
