<?php

namespace MainlyCode\BroadwayRethinkDb\EventStore;

use Broadway\Domain\DomainEventStream;
use Broadway\Domain\DomainEventStreamInterface;
use Broadway\EventStore\EventStoreInterface;
use Broadway\EventStore\EventStreamNotFoundException;
use MainlyCode\BroadwayRethinkDb\Serializer\DomainMessageSerializer;
use r\Connection;
use r\Queries\Tables\Table;

class RethinkDbEventStore implements EventStoreInterface
{
    private $connection;
    private $table;
    private $serializer;

    /**
     * @param Connection              $connection,
     * @param Table                   $table
     * @param DomainMessageSerializer $serializer
     */
    public function __construct(
        Connection $connection,
        Table $table,
        DomainMessageSerializer $serializer
    ) {
        $this->connection = $connection;
        $this->table = $table;
        $this->serializer = $serializer;
    }

    /**
     * {@inheritdoc}
     */
    public function load($id)
    {
        $id = (string) $id;

        $messages = $this->table
            ->getAll($id, ['index' => 'uuid'])
            ->orderBy('playhead')
            ->run($this->connection);

        if (empty($messages)) {
            throw new EventStreamNotFoundException(sprintf('EventStream not found for aggregate with id %s', $id));
        }
        $events = [];
        foreach ($messages as $message) {
            $events[] = $this->serializer->deserialize($message->getArrayCopy());
        }

        return new DomainEventStream($events);
    }

    /**
     * {@inheritdoc}
     */
    public function append($id, DomainEventStreamInterface $eventStream)
    {
        $id = (string) $id;

        $documents = [];

        foreach ($eventStream as $domainMessage) {
            $documents[] = $this->serializer->serialize($domainMessage);
        }

        $this->table
            ->insert($documents)
            ->run($this->connection);
    }
}
