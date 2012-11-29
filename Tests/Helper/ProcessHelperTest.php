<?php
namespace Terrific\ExporterBundle\Tests\Filter;
use Terrific\ExporterBundle\Filter\ExportPathHTMLFilter;
use Terrific\ExporterBundle\Helper\ProcessHelper;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2012-09-08 at 09:30:13.
 */
class ProcessHelperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }


    /**
     * @covers \Terrific\ExporterBundle\Helper\ProcessHelper::checkCommand
     */
    public function testCheckCommand()
    {
        // checkCommand

        $this->assertTrue(ProcessHelper::checkCommand('dir'));
        $this->assertFalse(ProcessHelper::checkCommand('dirASDF'));
    }

    /**
     * @covers \Terrific\ExporterBundle\Helper\ProcessHelper::startCommand
     */
    public function testStartCommand()
    {
        $process = ProcessHelper::startCommand('dir');

        $this->assertNotNull($process->getOutput());
    }
}
