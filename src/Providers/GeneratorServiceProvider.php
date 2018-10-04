<?php
namespace Generator\Providers;

use Illuminate\Support\ServiceProvider;
use Collective\Html\HtmlServiceProvider;
use Caffeinated\Bonsai\BonsaiServiceProvider;
use Caffeinated\Bonsai\Facades\Bonsai;
use Collective\Html\FormFacade;
use Collective\Html\HtmlFacade;

class GeneratorServiceProvider extends ServiceProvider
{
	public function boot()
	{
		$this->setUpPublish();
		$this->loadView();
	}

	protected function setUpPublish()
	{
		$this->publishes([
			__DIR__ . '/../config/generator.php' => config_path('generator.php'),
			__DIR__ . '/../Resources/views'      => resource_path('views'),
		]);
	}

	public function loadView()
	{
		$this->loadViewsFrom(__DIR__ . '/../Resources/views/', 't-component');
	}

	public function register()
	{
		$this->registerBonsai();
		$this->register();
		$this->registerViewComponent();
	}

	public function registerBonsai()
	{
		if (!$this->app->bound(BonsaiServiceProvider::class)) {
			$this->app->register(BonsaiServiceProvider::class);
			$this->app->alias(Bonsai::class, 'Bonsai');
		}
	}

	public function registerLaravelCollective()
	{
		if (!$this->app->bound(HtmlServiceProvider::class)) {
			$this->app->register(HtmlServiceProvider::class);
			$this->app->alias(FormFacade::class, 'Form');
			$this->app->alias(HtmlFacade::class, 'Html');
		}
	}
}
