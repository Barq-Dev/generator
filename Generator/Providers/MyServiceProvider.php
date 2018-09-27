<?php
namespace Generator\Providers;

use Illuminate\Support\ServiceProvider;
use Generator\Routing\ModResourceRegistrar;
use Illuminate\Routing\ResourceRegistrar;

class MyServiceProvider extends ServiceProvider
{
	public function register()
	{
		//change default get ResourceRegistar class to modded ResourceRegistar  class
		// $this->app->bind(ResourceRegistrar::class, ModResourceRegistrar::class);
	}
}
