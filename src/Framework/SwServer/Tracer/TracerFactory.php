<?php


namespace Framework\SwServer\Tracer;

use ZipkinOpenTracing\Tracer;
use Zipkin\Endpoint;
use Zipkin\Reporters\Http;
use Zipkin\Samplers\BinarySampler;
use Zipkin\TracingBuilder;
use Framework\SwServer\ServerManager;

class TracerFactory
{
    private $clientFactory;

    public function __construct(HttpClientFactory $clientFactory)
    {
        $this->clientFactory = $clientFactory;
    }

    public function getTracer(string $name = ""): \OpenTracing\Tracer
    {
        $trackerName = 'tracer';
        if (!empty($name)) {
            $trackerName = $name;
        }
        $sampler = BinarySampler::createAsAlwaysSample();
        [$app, $options, $sampler] = array_merge($this->getConfig($trackerName), [$sampler]);
        $endpoint = Endpoint::create($app['name'], $app['ipv4'], $app['ipv6'], $app['port']);
        $reporter = new Http($this->clientFactory, $options);
        $tracing = TracingBuilder::create()
            ->havingLocalEndpoint($endpoint)
            ->havingSampler($sampler)
            ->havingReporter($reporter)
            ->build();
        return new Tracer($tracing);
    }


    private function getConfig(string $key, $default = [])
    {
        return (isset(ServerManager::$config[$key]) && ServerManager::$config[$key]) ? ServerManager::$config[$key] : $default;
    }

}