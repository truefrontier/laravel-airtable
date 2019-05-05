<?php

namespace Tapp\Airtable\Test;

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client as GuzzleClient;
use Psr\Http\Message\ResponseInterface;
use Tapp\Airtable\Api\AirtableApiClient as Client;

class ClientTest extends TestCase
{
    /** @test */
    public function it_can_be_instantiated()
    {
        $client = new Client('test_base', 'test_table', 'test_key');
        $this->assertInstanceOf(Client::class, $client);
    }

    /** @test */
    public function it_can_post()
    {
        $expectedResponse = [
            'id' => 'randomlygenerated',
            'fields' => ['Company Name' => 'Tapp Network'],
            'createdTime' => 'timestamp',
        ];

        $postData = ['Company Name' => 'Tapp Network'];

        $mockGuzzle = $this->mock_guzzle_request(
            json_encode($expectedResponse),
            '/v0/test_base/companies',
            [
                'json' => [
                    'fields' => (object) $postData,
                ],
            ]
        );

        $client = $this->build_client($mockGuzzle);

        $actualResponse = $client->table('companies')
            ->post($postData);

        $this->assertEquals($expectedResponse['fields'], $actualResponse['fields']);
    }

    private function build_client($mockGuzzle = null)
    {
        return new Client(
            $mockGuzzle ? 'test_base' : env('AIRTABLE_BASE', 'test_base'),
            $mockGuzzle ? 'test_table' : env('AIRTABLE_TABLE', 'test_table'),
            $mockGuzzle ? 'test_key' : env('AIRTABLE_KEY', 'test_key'),
            $mockGuzzle
        );
    }

    private function mock_guzzle_request($expectedResponse, $expectedEndpoint, $expectedParams)
    {
        if (getenv('TEST_AIRTABLE_API')) {
            return;
        }

        $mockResponse = $this->getMockBuilder(ResponseInterface::class)
            ->getMock();

        if ($expectedResponse) {
            $mockResponse->expects($this->once())
                ->method('getBody')
                ->willReturn($expectedResponse);
        }

        $mockGuzzle = $this->getMockBuilder(GuzzleClient::class)
            ->setMethods(['post'])
            ->getMock();

        $mockGuzzle->expects($this->once())
            ->method('post')
            ->with($expectedEndpoint, $expectedParams)
            ->willReturn($mockResponse);

        return $mockGuzzle;
    }
}
