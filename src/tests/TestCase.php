<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
{
    parent::setUp();

    $this->app->register(\Modules\Auth\Providers\AuthServiceProvider::class);
    $this->app['router']->aliasMiddleware('jwt', \Modules\Auth\Http\Middleware\JwtAuthenticate::class);
}

}
