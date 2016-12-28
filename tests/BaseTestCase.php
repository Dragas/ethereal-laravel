<?php

use Ethereal\Bastion\Rucks;
use Ethereal\Bastion\Bastion;
use Ethereal\Bastion\Store\Store;
use Ethereal\Cache\GroupFileStore;
use Ethereal\Database\Ethereal;
use Illuminate\Container\Container;
use Orchestra\Testbench\TestCase;

class BaseTestCase extends TestCase
{
    public function setUp()
    {
        parent::setUp();

//        $this->artisan('migrate', [
//            '--database' => 'ethereal',
//            '--realpath' => __DIR__ . '/migrations',
//        ]);
    }

    /**
     * Setup testing environment.
     *
     * @param \Illuminate\Foundation\Application $app
     */
    protected function getEnvironmentSetUp($app)
    {
//        $app['config']->set('database.default', 'ethereal');
//        $app['config']->set('database.connections.ethereal', [
//            'driver' => 'sqlite',
//            'database' => ':memory:',
//            'prefix' => '',
//        ]);
//        $app['config']->set('bastion', [
//            'tables' => [
//                'abilities' => 'abilities',
//                'assigned_roles' => 'assigned_roles',
//                'permissions' => 'permissions',
//                'roles' => 'roles',
//            ],
//
//            'models' => [
//                'ability' => \Ethereal\Bastion\Database\Ability::class,
//                'assigned_role' => \Ethereal\Bastion\Database\AssignedRole::class,
//                'permission' => \Ethereal\Bastion\Database\Permission::class,
//                'role' => \Ethereal\Bastion\Database\Role::class,
//            ],
//        ]);
    }

    /**
     * Clean up the testing environment before the next test.
     *
     * @return void
     */
    public function tearDown()
    {
        $this->app['files']->deleteDirectory(__DIR__ . '/cache');

        parent::tearDown();
    }
}
