<?php

namespace MainlyCode\BroadwayRethinkDb\EventStore;

use Broadway\EventStore\EventStoreTest;
use Broadway\Serializer\SimpleInterfaceSerializer;
use MainlyCode\BroadwayRethinkDb\Serializer\DomainMessageSerializer;

/**
 * @group functional
 */
class RethinkDbEventStoreTest extends EventStoreTest
{
    private $connection;
    private $databaseName = 'test';
    private $tableName = 'BroadwayRethinkDbEventStoreTest';

    public function setUp()
    {
        $this->connection = \r\connect('localhost');

        // create table if not exists
        if (!$this->hasTable()) {
            $this->createTable();
        }

        $this->eventStore = new RethinkDbEventStore(
            $this->connection,
            \r\table($this->tableName),
            new DomainMessageSerializer(
                new SimpleInterfaceSerializer(),
                new SimpleInterfaceSerializer()
            )
        );
    }

    public function tearDown()
    {
        $this->dropTable();
    }

    private function hasTable()
    {
        return \r\db($this->databaseName)
            ->tableList()
            ->contains($this->tableName)
            ->run($this->connection);
    }

    private function createTable()
    {
        \r\db($this->databaseName)
            ->tableCreate($this->tableName)
            ->run($this->connection);

        \r\table($this->tableName)
            ->indexCreate('uuid')
            ->run($this->connection);

        sleep(1);
    }

    private function dropTable()
    {
        \r\db($this->databaseName)
            ->tableDrop($this->tableName)
            ->run($this->connection);
    }

    /**
     * @test
     * @dataProvider idDataProvider
     * @expectedException \Broadway\EventStore\EventStoreException
     */
    public function it_throws_an_exception_when_appending_a_duplicate_playhead($id)
    {
        $this->markTestSkipped('RethinkDB does not support unique secondary indexes');
    }
}
