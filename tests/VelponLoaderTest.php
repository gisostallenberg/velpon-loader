<?php

namespace GisoStallenberg\VelponLoader\Tests;

use GisoStallenberg\VelponLoader\VelponLoader;
use PHPUnit_Framework_TestCase;

class VelponLoaderTest extends PHPUnit_Framework_TestCase
{
    /**
     * The list of a class stack when PluginOne and PluginTwo are loaded for the class RealClassFullTest
     *
     * @var array
     */
    private $fullClassList = [
        0 => 'RealClassFullTest',
        1 => 'RealClassFullTestPluginOne',
        2 => 'RealClassFullTestPluginOneVelpon',
        3 => 'RealClassFullTestPluginTwo',
        4 => 'RealClassFullTestPluginTwoVelpon',
        5 => 'RealClassFullTestPluggable',
    ];

    /**
     * The list of a class stack when PluginOne provides RealClassHalfTest and plugin two is not added
     *
     * @var array
     */
    private $halfClassList = [
        0 => 'RealClassHalfTest',
        1 => 'RealClassHalfTestPluginOne',
        2 => 'RealClassHalfTestPluginOneVelpon',
        5 => 'RealClassHalfTestPluggable',
    ];

    /**
     * Test to see if the full loader works
     */
    public function testFullLoaderWorks()
    {
        $registered = VelponLoader::register([
            'plugin_two' => [
                'RealClassFullTest' => 'RealClassFullTestPluginTwo'
            ],
            'plugin_one' => [
                'RealClassFullTest' => 'RealClassFullTestPluginOne'
            ],
        ]);
        $this->assertTrue($registered);

        $class = new \RealClassFullTest();
        $this->assertEquals('RealClassFullTestPluginOne', get_class($class));
        foreach ($this->fullClassList as $className) {
            $this->assertInstanceOf($className, $class);
        }

        $this->assertTrue(VelponLoader::unregister());
    }

    /**
     * Test to see if half loading the plugins works
     */
    public function testHalfLoaderWorks()
    {
        $registered = VelponLoader::register([
            'plugin_one' => [
                'RealClassHalfTest' => 'RealClassHalfTestPluginOne'
            ],
        ]);
        $this->assertTrue($registered);

        $class = new \RealClassHalfTest();
        $this->assertEquals('RealClassHalfTestPluginOne', get_class($class));
        $this->assertFalse(array_key_exists('RealClassHalfTestPluginTwo', class_parents($class)));

        foreach ($this->halfClassList as $className) {
            $this->assertInstanceOf($className, $class);
        }


        $this->assertTrue(VelponLoader::unregister());
    }

    /**
     * Test to see if not adding any plugins works
     */
    public function testNoPluginsWork()
    {
        $registered = VelponLoader::register([]);
        $this->assertTrue($registered);

        $class = new \RealClassNoPluginTest();
        $this->assertEquals('RealClassNoPluginTestPluggable', get_class($class));
        $this->assertInstanceOf('RealClassNoPluginTest', $class);

        $this->assertTrue(VelponLoader::unregister());
    }
}
