<?php
namespace Church\Monitor;

use Workerman\Connection\AsyncUdpConnection;
use Workerman\Timer;

class UDPWorker 
{
    protected $serverHost = "";

    protected $serverPort = 3000;

    protected $site = "";

    protected $packageLength = 1024;

    protected $interval = 10;

    const END_OF_DATA = "\r\n\r\n";

    public function __construct($host, $port, $interval, $site)
    {
        $this->serverHost = $host;
        $this->serverPort = $port;
        $this->interval = $interval;
        $this->site = $site;
    }

    /**
     * communicate with udp server, every package length is 1024, if gather than 1024, server will crash.
     * the protocol is based on json, double \r\n is EOF, means the request data finish transfer.
     */
    public function onWorkerStart()
    {
        echo 'current work directory is ' . getcwd() . ', if is not your project root directory, please change the work directory.' . PHP_EOL;

        Timer::add($this->interval, function() {
            $connectionsInfo = shell_exec("php bin/start connections");
            $statusInfo = shell_exec("php bin/start status");

            if ($connectionsInfo == null || $statusInfo == null) {
                //please make sure the work directory is project root directory.
                throw new CommandFailedException("php bin/start connections or status runs failed.");
            }

            $wholeData = json_encode(['site' => $this->site, 'route' => 'connections', 'data' => $connectionsInfo])
                         . self::END_OF_DATA 
                         . json_encode(['site' => $this->site, 'route' => 'status', 'data' => $statusInfo])
                         . self::END_OF_DATA;

            $conn = new AsyncUdpConnection("udp://{$this->serverHost}:{$this->serverPort}");
            $conn->onConnect = function($conn) use ($wholeData) {
                $packages = str_split($wholeData, $this->packageLength);

                foreach ($packages as $package) {
                    $conn->send($package);
                }

                $conn->close();
            };
    
            $conn->connect();
        }, null, true);
        
    }
}