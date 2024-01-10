<?php

namespace App\LoadBalancers;

class RoundRobinLoadBalancer
{
    private $backendServers;
    private $currentIndex;

    public function __construct(array $backendServers)
    {
        $this->backendServers = $backendServers;
        // Initialize currentIndex from the session, or to 0 if it's not set
        $this->currentIndex = isset($_SESSION['currentIndex']) ? $_SESSION['currentIndex'] : 0;
    }

    public function getNextServer()
    {
        $selectedServer = $this->backendServers[$this->currentIndex];

        // Move to the next server in a round-robin fashion
        $this->currentIndex = ($this->currentIndex + 1) % count($this->backendServers);
        $_SESSION['currentIndex'] = $this->currentIndex;     // Store the updated currentIndex in the session

        return $selectedServer;
    }
}
