<?php

require_once __DIR__.'/../stubs/User.php';

use Trucker\Facades\Response;
use Trucker\Facades\Config;
use Mockery as m;

class ResponseTest extends TruckerTests
{

    public function testDynamicFunctionCallOnResponse()
    {
        $gResponse = m::mock('Guzzle\Http\Message\Response');
        $gResponse->shouldReceive('getStatusCode')
            ->once()
            ->andReturn(200);
        $response = new \Trucker\Responses\Response($this->app, $gResponse);
        $this->assertEquals(200, $response->getStatusCode());
    }



    public function testGetOption()
    {
        $config = m::mock('Illuminate\Config\Repository');
        $config->shouldIgnoreMissing();
        $config->shouldReceive('get')->with('trucker::transporter.driver')
            ->andReturn('json');

        $app = m::mock('Illuminate\Container\Container');
        $app->shouldIgnoreMissing();
        $app->shouldReceive('offsetGet')->with('config')->andReturn($config);

        $response = new \Trucker\Responses\Response($app);
        $transporter = Config::get('transporter.driver');

        $this->assertEquals('json', $transporter);
    }



    public function testNewInstanceCreator()
    {
        $gResponse = m::mock('Guzzle\Http\Message\Response');
        $gResponse->shouldReceive('getStatusCode')
            ->times(2)
            ->andReturn(200);

        $i = Response::newInstance($this->app, $gResponse);
        $this->assertTrue(
            $i instanceof Trucker\Responses\Response
        );
        $this->assertEquals(
            $this->app,
            $i->getApp()
        );
        $this->assertEquals(200, $i->getStatusCode());
        $this->assertEquals(200, $i->__call('getStatusCode', []));
    }



    public function testParseJsonResponseToData()
    {
        $config = m::mock('Illuminate\Config\Repository');
        $config->shouldIgnoreMissing();
        $config->shouldReceive('get')->with('trucker::transporter.driver')
            ->andReturn('json');

        $app = m::mock('Illuminate\Container\Container');
        $app->shouldIgnoreMissing();
        $app->shouldReceive('offsetGet')->with('config')->andReturn($config);

        $data = ['foo' => 'bar'];

        $gResponse = m::mock('Guzzle\Http\Message\Response');
        $gResponse->shouldReceive('json')
            ->once()
            ->andReturn($data);
        $response = new \Trucker\Responses\Response($app, $gResponse);
        $this->assertTrue(
            $this->arraysAreSimilar($data, $response->parseResponseToData())
        );
    }



    public function testParseJsonResponseStringToObject()
    {
        $config = m::mock('Illuminate\Config\Repository');
        $config->shouldIgnoreMissing();
        $config->shouldReceive('get')->with('trucker::transporter.driver')
            ->andReturn('json');

        $app = m::mock('Illuminate\Container\Container');
        $app->shouldIgnoreMissing();
        $app->shouldReceive('offsetGet')->with('config')->andReturn($config);

        $data = ['foo' => 'bar'];
        $dataJson = json_encode($data);
        $decodedJson = json_decode($dataJson);

        $gResponse = m::mock('Guzzle\Http\Message\Response');
        $gResponse->shouldReceive('getBody')
            ->with(true)
            ->once()
            ->andReturn($dataJson);
        $response = new \Trucker\Responses\Response($app, $gResponse);
        $this->assertEquals(
            $decodedJson,
            $response->parseResponseStringToObject()
        );
    }
}
