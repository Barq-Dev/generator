<?php
namespace Generator\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Filesystem\Filesystem;

class GeneratorServiceProvider extends ServiceProvider
{
	public function boot()
	{
		/*
	 * @var \Illuminate\Filesystem\Filesystem
	 */
		$this->fs = $this->app->make(Filesystem::class);

		if ($this->fs->exists(config_path('generator.php'))) {
			$this->fs->delete(config_path('generator.php'));
		}
		$this->publishes([
			__DIR__ . '/../config/generator.php' => config_path('generator.php'),
		]);
	}
}
