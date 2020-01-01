<?php

namespace nickurt\PostcodeApi\tests\Providers\nl_NL;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use nickurt\PostcodeApi\Entity\Address;
use nickurt\PostcodeApi\Providers\nl_NL\PostcodesNL;
use nickurt\PostcodeApi\tests\Providers\BaseProviderTest;

class PostcodesNLTest extends BaseProviderTest
{
    /** @var PostcodesNL */
    protected $postcodesNL;

    /** @var \nickurt\PostcodeApi\Http\Guzzle6HttpClient */
    protected $httpClient;

    public function setUp(): void
    {
        $this->postcodesNL = (new PostcodesNL($this->httpClient = new \nickurt\PostcodeApi\Http\Guzzle6HttpClient()))
            ->setApiKey('qwertyuiopasdfghjklzxcvbnmqwertyuiopasdfghjklzxcvbnmqwertyuiopas');
    }

    /** @test */
    public function it_can_get_the_default_config_values_for_this_provider()
    {
        $this->assertSame('qwertyuiopasdfghjklzxcvbnmqwertyuiopasdfghjklzxcvbnmqwertyuiopas', $this->postcodesNL->getApiKey());
        $this->assertSame('https://api.postcodes.nl/1.0/address', $this->postcodesNL->getRequestUrl());
    }

    /** @test */
    public function it_can_get_the_correct_values_for_find_a_valid_postal_code()
    {
        $this->httpClient->setHttpClient(new Client([
            'handler' => new MockHandler([
                new Response(200, [], '{"status":"ok","results":[{"nlzip6":"1118CP","streetname":"Evert van de Beekstraat","city":"Schiphol","municipality":"Haarlemmermeer","province":"Noord-Holland","latitude":"52.303047","longitude":"4.746179","phoneareacode":"020"}]}')
            ]),
        ]));

        $address = $this->postcodesNL->find('1118CP');

        $this->assertSame('https://api.postcodes.nl/1.0/address?apikey=qwertyuiopasdfghjklzxcvbnmqwertyuiopasdfghjklzxcvbnmqwertyuiopas&nlzip6=1118CP', (string)$this->httpClient->getHttpClient()->getConfig('handler')->getLastRequest()->getUri());

        $this->assertInstanceOf(Address::class, $address);

        $this->assertSame([
            'street' => 'Evert van de Beekstraat',
            'house_no' => null,
            'town' => 'Schiphol',
            'municipality' => 'Haarlemmermeer',
            'province' => 'Noord-Holland',
            'latitude' => 52.303047,
            'longitude' => 4.746179
        ], $address->toArray());
    }

    /** @test */
    public function it_can_get_the_correct_values_for_find_an_invalid_postal_code()
    {
        $this->httpClient->setHttpClient(new Client([
            'handler' => new MockHandler([
                new Response(200, [], '{"status":"error","errorcode":103,"errormessage":"invalid nlzip6"}')
            ]),
        ]));

        $address = $this->postcodesNL->find('XXXXAB');

        $this->assertInstanceOf(Address::class, $address);

        $this->assertSame([
            'street' => null,
            'house_no' => null,
            'town' => null,
            'municipality' => null,
            'province' => null,
            'latitude' => null,
            'longitude' => null,
        ], $address->toArray());
    }

    /** @test */
    public function it_can_get_the_correct_values_for_find_by_postcode_and_house_number_a_valid_postal_code()
    {
        $this->httpClient->setHttpClient(new Client([
            'handler' => new MockHandler([
                new Response(200, [], '{"status":"ok","results":[{"nlzip6":"1118CP","streetname":"Evert van de Beekstraat","city":"Schiphol","municipality":"Haarlemmermeer","province":"Noord-Holland","latitude":"52.303894","longitude":"4.747910","phoneareacode":"020"}]}')
            ]),
        ]));

        $address = $this->postcodesNL->findByPostcodeAndHouseNumber('1118CP', '202');

        $this->assertSame('https://api.postcodes.nl/1.0/address?apikey=qwertyuiopasdfghjklzxcvbnmqwertyuiopasdfghjklzxcvbnmqwertyuiopas&nlzip6=1118CP&streetnumber=202', (string)$this->httpClient->getHttpClient()->getConfig('handler')->getLastRequest()->getUri());

        $this->assertInstanceOf(Address::class, $address);

        $this->assertSame([
            'street' => 'Evert van de Beekstraat',
            'house_no' => '202',
            'town' => 'Schiphol',
            'municipality' => 'Haarlemmermeer',
            'province' => 'Noord-Holland',
            'latitude' => 52.303894,
            'longitude' => 4.74791
        ], $address->toArray());
    }

    /** @test */
    public function it_can_get_the_correct_values_for_find_by_postcode_and_house_number_an_invalid_postal_code()
    {
        $this->httpClient->setHttpClient(new Client([
            'handler' => new MockHandler([
                new Response(200, [], '{"status":"error","errorcode":11,"errormessage":"no results"}')
            ]),
        ]));

        $address = $this->postcodesNL->findByPostcodeAndHouseNumber('1118CP', '1');

        $this->assertInstanceOf(Address::class, $address);

        $this->assertSame([
            'street' => null,
            'house_no' => null,
            'town' => null,
            'municipality' => null,
            'province' => null,
            'latitude' => null,
            'longitude' => null,
        ], $address->toArray());
    }
}
