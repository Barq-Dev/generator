<?php
namespace Generator\Providers;

use Illuminate\Support\ServiceProvider;

class GeneratorServiceProvider extends ServiceProvider
{
	public function boot()
	{
		$this->publishes([
			__DIR__ . '../config/generator.php' => config_path('generator.php'),
		]);
	}
}
