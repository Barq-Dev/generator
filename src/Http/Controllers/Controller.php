<?php
namespace Generator\Http\Controllers;

use Illuminate\Support\Str;
use Generator\Providers\Traits\CRUDHelperTrait;
use Generator\Providers\Traits\CRUDTrait;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesResources;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
	protected $requestName;
	protected $model;
	protected $requestField;
	protected $module;
	protected $role;
	protected $request;
	protected $viewNamespace;
	protected $baseView;
	protected $locationView;

	use AuthorizesRequests,
		// AuthorizesResources,
		DispatchesJobs,
		ValidatesRequests,
		CRUDHelperTrait,
		CRUDTrait;

	/**
	 * fungsi untuk mendapatkan
	 * namespace dari class itu sendiri.
	 *
	 * @return [type] [description]
	 */
	final protected function getNamespace()
	{
		$reflection = new \ReflectionClass($this);

		return $reflection->getNamespaceName();
	}

	/**
	 * ini adalah fungsi untuk menggenerate nama
	 * view folder dan routenya berdasarkan nama
	 * dari class controllernya.
	 */
	final protected function generateNameModule()
	{
		if (!property_exists($this, 'module') || null == $this->module) {
			$namespace    = $this->getNamespace();
			$this->module = Str::snake(str_replace([$namespace, 'Controller', '\\'], '', get_class($this)));
		} else {
			$this->module = str_replace('-', '_', $this->module);
		}
		$this->registerLocationView();
	}

	/**
	 * untuk register view location default dari controller.
	 */
	final protected function registerLocationView()
	{
		$this->locationView = '';

		if (null !== $this->viewNamespace) {
			$this->locationView .= $this->viewNamespace;
		}
		if (null !== $this->baseView) {
			$this->locationView .= $this->baseView;
		}
		if (null !== $this->module) {
			$this->locationView .= ".$this->module";
		}
	}

	/**
	 * menggabungkan base view dengan view yang akan di akses.
	 *
	 * @param string $viewName
	 */
	protected function loadViewName($viewName)
	{
		return "{$this->locationView}.$viewName";
	}
}
