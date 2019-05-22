<?php
namespace Scoutapm\Tests;

use \Scoutapm\Agent;
use \PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

/**
 * Test Case for @see \Scoutapm\Agent
 */
final class AgentTest extends TestCase {
  public function testCanBeInitialized() {
      $agent = new Agent();
      $this->assertNotNull($agent);
  }

  public function testCanSetLogger() {
      $agent = new Agent();
      $logger = new NullLogger();

      $agent->setLogger($logger);

      $this->assertEquals($agent->getLogger(), $logger);
  }
}
