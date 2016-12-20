<?php

namespace Boerl\Zf1LogPsr3;

/**
 * @covers MemoryWriter
 */
class MemoryWriterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests that the factory does not need any arguments.
     *
     * @covers MemoryWriter::factory
     */
    public function testConstructor()
    {
        MemoryWriter::factory();
    }

    /**
     * Tests that with each written event, the size of the messages array is incremented.
     *
     * @covers MemoryWriter::write
     * @covers MemoryWriter::getMessages
     */
    public function testMessagesAreRetrievable()
    {
        $writer = MemoryWriter::factory();

        $this->assertCount(0, $writer->getMessages());
        $writer->write(['message' => '1']);
        $this->assertCount(1, $writer->getMessages());
        $writer->write(['message' => '2']);
        $this->assertCount(2, $writer->getMessages());
    }
}