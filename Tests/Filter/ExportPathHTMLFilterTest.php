<?php
namespace Terrific\ExporterBundle\Tests\Filter;
use Terrific\ExporterBundle\Filter\ExportPathHTMLFilter;


/**
 * Generated by PHPUnit_SkeletonGenerator on 2012-09-08 at 09:30:13.
 */
class ExportPathHTMLFilterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ExportPathHTMLFilter
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new ExportPathHTMLFilter(__DIR__ . "/../App");
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    /**
     * @covers Terrific\ExporterBundle\Filter\ExportPathHTMLFilter::filterJS
     * @todo   Implement testFilterJS().
     */
    public function testFilterJS()
    {
        $test = '<script type="text/javascript" src="/js/jquerytest.js"></script>';
        $ret = $this->object->filterJS($test);
        $this->assertSame('<script type="text/javascript" src="../js/jquerytest.js"></script>', $ret);

        $test = "<script type='text/javascript' src='/js/jquerytest.js'></script>";
        $ret = $this->object->filterJS($test);
        $this->assertSame("<script type='text/javascript' src='../js/jquerytest.js'></script>", $ret);
    }

    /**
     * @covers Terrific\ExporterBundle\Filter\ExportPathHTMLFilter::filterCSS
     * @todo   Implement testFilterCSS().
     */
    public function testFilterCSS()
    {
        $test = "<link rel='stylesheet' href='/css/compiled/base.css' />";
        $ret = $this->object->filterCSS($test);
        $this->assertSame("<link rel='stylesheet' href='../css/base.css' />", $ret);

        $test = '<link href="/css/compiled/base.css" />';
        $ret = $this->object->filterCSS($test);
        $this->assertSame('<link href="../css/base.css" />', $ret);
    }

    /**
     * @covers Terrific\ExporterBundle\Filter\ExportPathHTMLFilter::filterHTML
     * @todo   Implement testFilterHTML().
     */
    public function testFilterHTML()
    {
        $test = '<img src="/bundles/terrificmoduletest/img/blubb.jpg" />';
        $ret = $this->object->filterHTML($test);
        $this->assertSame('<img src="../img/Test/blubb.jpg" />', $ret);

        $test = '<img data-bigsrc="/bundles/terrificmoduletest/img/blubb.jpg" />';
        $ret = $this->object->filterHTML($test);
        $this->assertSame('<img data-bigsrc="../img/Test/blubb.jpg" />', $ret);

        $test = '<area shape="poly" coords="388,398,388,406,397,405,397,398"  alt="Country selection" href="es" data-img="/bundles/terrificmoduletest/img/blubb.jpg?1"   data-tip="es" />';
        $ret = $this->object->filterHTML($test);
        $this->assertSame('<area shape="poly" coords="388,398,388,406,397,405,397,398"  alt="Country selection" href="es" data-img="../img/Test/blubb.jpg"   data-tip="es" />', $ret);

        $test = '<map name="continent-north-america" data-img="/bundles/terrificmodulefooter/img/webasto_country_map_continent_north_amerika_zoom.png?1">';
        $ret = $this->object->filterHTML($test);
        $this->assertSame('<map name="continent-north-america" data-img="../img/Footer/webasto_country_map_continent_north_amerika_zoom.png">', $ret);

        $test = '<a href="#"></a>';
        $ret = $this->object->filterHTML($test);
        $this->assertSame('<a href="#"></a>', $ret);
    }

    /**
     *
     */
    public function testGetModuleFromPath()
    {
        $test = "/data/vhosts.d/webasto-internet.local/htdocs/src/Terrific/Module/MainNavigation/Resources/img/test.jpg";
        $ret = $this->object->getModuleFromPath($test);
        $this->assertSame("MainNavigation", $ret);
    }

    /**
     * @covers Terrific\ExporterBundle\Filter\ExportPathHTMLFilter::filter
     * @todo   Implement testFilter().
     */
    public function testFilter()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }
}
