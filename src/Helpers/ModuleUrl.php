<?php
namespace Generator\Helpers;

class ModuleUrl
{
	private $routeName;
	private $routeAction;
	private $defaultUse;

	public function __construct($routeName, $routeAction, $defaultUse = 'routeName')
	{
		$this->routeAction = $routeAction;
		$this->routeName   = $routeName;
		$this->defaultUse  = $defaultUse;
	}

	public function __toString()
	{
		return $this->{$this->defaultUse} ?? '';
	}
}
