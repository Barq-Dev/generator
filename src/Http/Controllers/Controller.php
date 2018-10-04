<?php
namespace Generator\Http\Controllers;

use Generator\Traits\CRUDTrait;
use Generator\Traits\CRUDHelperTrait;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesResources;

class Controller extends BaseController
{
	protected $requestName;
	protected $model;
	protected $requestField;
	protected $module;
	protected $role;
	protected $request;
	protected $moduleViewNamespace;

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
	private function getNamespace()
	{
		$reflection = new \ReflectionClass($this);

		return $reflection->getNamespaceName();
	}

	/**
	 * ini adalah fungsi untuk menggenerate nama
	 * view folder dan routenya berdasarkan nama
	 * dari class controllernya.
	 */
	private function generateNameModule()
	{
		if (!property_exists($this, 'module') || null == $this->module) {
			$namespace    = $this->getNamespace();
			$this->module = snake_case(str_replace([$namespace, 'Controller', '\\'], '', get_class($this)));
		} else {
			$this->module = str_replace('-', '_', $this->module);
		}
	}
}
