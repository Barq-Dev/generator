<?php
namespace Generator\Providers;

use Illuminate\Support\ServiceProvider;
use Closure;

abstract class InterfaceBinderServiceProvider extends ServiceProvider
{
	/**
	 * Bootstrap any application services.
	 */
	public function boot()
	{
		//
		// \Schema::defaultStringLength(191);
	}

	/**
	 * Register any application services.
	 */
	public function register()
	{
		foreach ($this->bindingItems() as $need => $whenAndGive) {
			if (!is_array($whenAndGive)) {
				throw new Exception('Must be array', 1);
			}
			foreach ($whenAndGive as $when => $give) {
				$app = $this->app->when($when)
								 ->needs($need);
				if (\is_array($give)) {
					foreach ($give as $g) {
						$this->give($app, $g);
					}
				} else {
					$this->give($app, $give);
				}
			}
		}
	}

	/**
	 * helper function for auto conditional binding.
	 *
	 * @param Application $app
	 * @param mix         $give
	 */
	private function give($app, $give)
	{
		if ($give instanceof Closure) {
			$app->give($give);
		} elseif (is_string($give)) {
			$app->give(function () use ($give) {
				return app($give);
			});
		}
	}

	/**
	 * list interface yang dibutuhkan untuk class
	 * yang akan menggunakan dan class yang akan
	 * diberikan sebgai pengganti interface tersebut.
	 */
	abstract public function bindingItems();

	// {
	//     return [
	//         //ex : UserRepositoryInterface::class
	//         'class interface' => [
	//             // class yang menggunakan interface
	//             'need' => [
	//                // Controller::class,
	//             ],
	//             // class yang akan diberikan ketika 'class need'
	//             // membutuhkan interface diatas
	//             // 'give' => Class::class
	//             // 'give' => [Class::class,function($app){}]
	//             // 'give' => function($app){}
	//             'give' => ''
	//         ]
	//     ];
	// }
}
