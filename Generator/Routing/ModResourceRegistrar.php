<?php
namespace Generator\Routing;

use Illuminate\Routing\ResourceRegistrar;

class ModResourceRegistrar extends ResourceRegistrar
{
	/**
	 * The default actions for a resourceful controller.
	 *
	 * @var array
	 */
	protected $resourceDefaults = ['list', 'create', 'store', 'show', 'edit', 'update', 'destroy'];

	/**
	 * Add the list method for a resourceful route.
	 *
	 * @param string $name
	 * @param string $base
	 * @param string $controller
	 * @param array  $options
	 *
	 * @return \Illuminate\Routing\Route
	 */
	protected function addResourceList($name, $base, $controller, $options)
	{
		$uri = $this->getResourceUri($name);

		$action = $this->getResourceAction($name, $controller, 'list', $options);

		return $this->router->get($uri, $action);
	}
}
