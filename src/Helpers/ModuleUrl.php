<?php
namespace Generator\Helpers;

class ModuleUrl
{
	private $routeName;
	private $routeAction;

	public function __construct($routeName, $routeAction)
	{
		$this->routeAction = $routeAction;
		$this->routeName   = $routeName;
	}

	public function __toString()
	{
		return $this->{config('generator.url')} ?? null;
	}
}
