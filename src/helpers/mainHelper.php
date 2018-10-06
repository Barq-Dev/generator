<?php


if (!function_exists('generateViewWithNamespace')) {
	/**
	 * untuk menggenerate namespace
	 * yang digunakan pada generator.
	 *
	 * @param string $viewPath
	 */
	function generateViewWithNamespace($viewPath)
	{
		$namespace = config('view.namespace');
		if (!empty($namespace)) {
			$namespace .= '::';
		}

		return $namespace . $viewPath;
	}
}
