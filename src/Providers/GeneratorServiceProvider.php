<?php
namespace Generator\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Filesystem\Filesystem;

class GeneratorServiceProvider extends ServiceProvider
{
	public function __construct()
	{
		$this->fs = app(Filesystem::class);
	}

	public function boot()
	{
		if ($this->fs->exist(config_path('generator.php'))) {
			$this->fs->delete(config_path('generator.php'));
		}
		$this->publishes([
			__DIR__ . '/../config/generator.php' => config_path('generator.php'),
		]);
	}
}
