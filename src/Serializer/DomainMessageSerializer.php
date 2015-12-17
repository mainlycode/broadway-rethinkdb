<?php

namespace MainlyCode\BroadwayRethinkDb\Serializer;

use Broadway\Domain\DateTime;
use Broadway\Domain\DomainMessage;
use Broadway\Serializer\SerializationException;
use Broadway\Serializer\SerializerInterface;

class DomainMessageSerializer implements SerializerInterface
{
    private $metadataSerializer;
    private $payloadSerializer;

    /**
     * @param SerializerInterface $metadataSerializer
     * @param SerializerInterface $payloadSerializer
     */
    public function __construct(
        SerializerInterface $metadataSerializer,
        SerializerInterface $payloadSerializer
    ) {
        $this->metadataSerializer = $metadataSerializer;
        $this->payloadSerializer = $payloadSerializer;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize($object)
    {
        if (!$this->supports($object)) {
            throw new SerializationException(sprintf(
                'Object \'%s\' is not instance of Broadway\Domain\DomainMessage',
                get_class($object)
            ));
        }

        return [
            'uuid' => (string) $object->getId(),
            'playhead' => $object->getPlayhead(),
            'metadata' => json_encode($this->metadataSerializer->serialize($object->getMetadata())),
            'payload' => json_encode($this->payloadSerializer->serialize($object->getPayload())),
            'recorded_on' => $object->getRecordedOn()->toString(),
            'type' => $object->getType(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function deserialize(array $serializedObject)
    {
        return new DomainMessage(
            $serializedObject['uuid'],
            $serializedObject['playhead'],
            $this->metadataSerializer->deserialize(json_decode($serializedObject['metadata'], true)),
            $this->payloadSerializer->deserialize(json_decode($serializedObject['payload'], true)),
            DateTime::fromString($serializedObject['recorded_on'])
        );
    }

    /**
     * @param mixed $object
     *
     * @return true
     */
    public function supports($object)
    {
        return $object instanceof DomainMessage;
    }
}
