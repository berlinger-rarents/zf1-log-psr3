<?php

namespace Boerl\Zf1LogPsr3;

use Psr\Log\LogLevel;
use Psr\Log\Test\LoggerInterfaceTest;

/**
 * @coversDefaultClass Wrapper
 */
class WrapperTest extends LoggerInterfaceTest
{
    /** @var MemoryWriter The Writer used by the Logger Under Test. */
    protected $testWriter;

    /* @var Wrapper The Logger Under Test. Created by {@see getLogger()} */
    protected $loggerUnderTest;

    /**
     * @inheritdoc
     *
     * @param string $format Message format for the {@see Zend_Log_Formatter_Simple}
     * @param array<string,mixed> $config Config for the {@see Wrapper}
     */
    public function getLogger($format = '%logLevel% %message%', array $config = [])
    {
        // Create formatter for the format that {@see LoggerInterfaceTest::getLogs()} requires.
        $formatter = new \Zend_Log_Formatter_Simple($format);

        // Create and keep a TestWriter that saves the messages in mem.
        $this->testWriter = MemoryWriter::factory();
        $this->testWriter->setFormatter($formatter);

        // Create the classic ZF1 logger.
        $zf1Logger = new \Zend_Log($this->testWriter);

        // Wrap the classic ZF1 logger with a PSR3 logger, the subject of our tests.
        $this->loggerUnderTest = new Wrapper($zf1Logger, $config);

        return $this->loggerUnderTest;
    }

    /**
     * @inheritdoc
     */
    public function getLogs()
    {
        return $this->testWriter->getMessages();
    }

    /**
     * Tests that an {@see \Exception} in the context does not throw an exception.
     */
    public function testExceptionInContext()
    {
        $logger = $this->getLogger();

        $exceptionMsg = 'exceptional!';
        $exception = new \Exception($exceptionMsg);
        $input = 'foo %exception% foo';
        $context = ['exception' => $exception];
        $expected = 'foo ' . $exceptionMsg . ' foo';

        $logger->emergency($input, $context);
        $this->assertNotEmpty($expected, $this->getOnlyLoggedMessage());
    }

    /**
     * Test expected behaviour when `rewrite_placeholders` is enabled.
     *
     * @covers ::__construct
     * @covers ::setConfig
     */
    public function testConfigRewritePlaceholdersOn()
    {
        $logger = $this->getLogger('%message%', ['rewrite_placeholders' => true]);

        $input = 'foo %replaceme% %replacemenot% {replaceme} {replacemenot} {priority} foo';
        $context = ['replaceme' => 'replaced'];
        $expected = 'foo replaced %replacemenot% replaced {replacemenot} 0 foo';

        $logger->emergency($input, $context);
        $this->assertSame($expected, $this->getOnlyLoggedMessage());
    }

    /**
     * Test expected behaviour when `rewrite_placeholders` is disabled.
     *
     * @covers ::__construct
     * @covers ::setConfig
     */
    public function testConfigRewritePlaceholdersOff()
    {
        $logger = $this->getLogger('%message%', ['rewrite_placeholders' => false]);

        $input = 'foo %replaceme% %replacemenot% {replaceme} {replacemenot} {priority} foo';
        $context = ['replaceme' => 'replaced'];
        $expected = 'foo replaced %replacemenot% {replaceme} {replacemenot} {priority} foo';
        $logger->emergency($input, $context);

        $this->assertSame($expected, $this->getOnlyLoggedMessage());
    }

    /**
     * Test expected behaviour when `add_log_level` is enabled.
     *
     * @covers ::__construct
     * @covers ::setConfig
     */
    public function testConfigAddLogLevelOn()
    {
        $logger = $this->getLogger('%message%', ['add_log_level' => true]);

        $input = 'foo %logLevel% foo';
        $expected = 'foo ' . LogLevel::EMERGENCY . ' foo';
        $logger->emergency($input);

        $this->assertSame($expected, $this->getOnlyLoggedMessage());
    }

    /**
     * Test expected behaviour when `add_log_level` is disabled.
     *
     * @covers ::__construct
     * @covers ::setConfig
     */
    public function testConfigAddLogLevelOff()
    {
        $logger = $this->getLogger('%message%', ['add_log_level' => false]);

        $input = 'foo %logLevel% foo';
        $expected = $input;
        $logger->emergency($input);

        $this->assertSame($expected, $this->getOnlyLoggedMessage());
    }

    /**
     * @return string The one and only logged message.
     */
    protected function getOnlyLoggedMessage()
    {
        $loggedMessages = $this->getLogs();
        $this->assertCount(1, $loggedMessages);
        $loggedMessage = reset($loggedMessages);

        return $loggedMessage;
    }
}