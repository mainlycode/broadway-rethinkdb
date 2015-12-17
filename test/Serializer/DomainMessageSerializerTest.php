<?php

namespace MainlyCode\BroadwayRethinkDb\Serializer;

use Broadway\Domain\DateTime;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use Broadway\Serializer\SerializableInterface;
use Broadway\Serializer\SimpleInterfaceSerializer;

class DomainMessageSerializerTest extends \PHPUnit_Framework_TestCase
{
    private $serializer;

    public function setUp()
    {
        $this->serializer = new DomainMessageSerializer(
            new SimpleInterfaceSerializer(),
            new SimpleInterfaceSerializer()
        );
    }

    /**
     * @test
     */
    public function it_serializes_a_domain_message()
    {
        $domainMessage = $this->createDomainMessage();

        $expectedSerializedDomainMessage = [
            'uuid' => '1337',
            'playhead' => 42,
            'metadata' => '{"class":"Broadway\\\\Domain\\\\Metadata","payload":[]}',
            'payload' => '{"class":"MainlyCode\\\\BroadwayRethinkDb\\\\Serializer\\\\Event","payload":[]}',
            'recorded_on' => '2015-03-14T13:37:42.000000+00:00',
            'type' => 'MainlyCode.BroadwayRethinkDb.Serializer.Event',
        ];

        $this->assertEquals($expectedSerializedDomainMessage, $this->serializer->serialize($domainMessage));
    }

    /**
     * @test
     */
    public function it_deserializes_a_serialized_domain_message()
    {
        $serializedDomainMessage = [
            'uuid' => '1337',
            'playhead' => 42,
            'metadata' => '{"class":"Broadway\\\\Domain\\\\Metadata","payload":[]}',
            'payload' => '{"class":"MainlyCode\\\\BroadwayRethinkDb\\\\Serializer\\\\Event","payload":[]}',
            'recorded_on' => '2015-03-14T13:37:42.000000+00:00',
            'type' => 'MainlyCode.BroadwayRethinkDb.Serializer.Event',
        ];

        $expectedDomainMessage = $this->createDomainMessage();

        $this->assertEquals($expectedDomainMessage, $this->serializer->deserialize($serializedDomainMessage));
    }

    /**
     * @test
     * @expectedException \Broadway\Serializer\SerializationException
     * @expectedExceptionMessage Object 'stdClass' is not instance of Broadway\Domain\DomainMessage
     */
    public function it_throws_when_serializing_an_unsupported_object()
    {
        $this->serializer->serialize(new \stdClass());
    }

    private function createDomainMessage()
    {
        return new DomainMessage(
            1337,
            42,
            new Metadata([]),
            new Event(),
            DateTime::fromString('2015-03-14 13:37:42')
        );
    }
}

class Event implements SerializableInterface
{
    public static function deserialize(array $data)
    {
        return new self();
    }

    public function serialize()
    {
        return [];
    }
}
