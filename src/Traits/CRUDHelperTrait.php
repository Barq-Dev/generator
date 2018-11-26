<?php
namespace Generator\Traits;

use stdClass;
use Exception;
use Illuminate\Http\Request;
use Generator\Interfaces\RepositoryInterface;

trait CRUDHelperTrait
{
	protected $route_names_need_id = ['show', 'edit', 'update', 'destroy'];
	protected $route_names         = ['index', 'create', 'store'];

	/**
	 * untuk menampilkan view.
	 *
	 * @return view
	 */
	public function view()
	{
		$generateViewWithNamespace = function ($viewPath) {
			$__generatorNameSpace = config('view.namespace');
			if (!empty($__generatorNameSpace)) {
				$__generatorNameSpace .= '::';
			}

			return $__generatorNameSpace . $viewPath;
		};
		$this->checkProperty(['title']);
		$params = func_get_args();

		if (property_exists($this, 'moduleViewNamespace') && null !== $this->moduleViewNamespace) {
			$view_url  = str_start(str_replace(str_finish($this->moduleViewNamespace, '_'), str_finish($this->moduleViewNamespace, '::'), $params[0]), str_finish($this->moduleViewNamespace, '::'));
			$params[0] = $view_url;
		}
		/*
		 * soon will deprecated and use moduleViewNamespace
		 */
		elseif (property_exists($this, 'viewNamespace') && null !== $this->viewNamespace) {
			$view_url  = str_start(str_replace(str_finish($this->viewNamespace, '_'), str_finish($this->viewNamespace, '::'), $params[0]), str_finish($this->viewNamespace, '::'));
			$params[0] = $view_url;
		} else {
			$view_url = $params[0];
		}
		$view_folder = collect(\explode('.', $view_url));
		$view_name   = $view_folder->pop();

		$title_document = isset($params[1]['title_document']) ? $ $params[1]['title_document'] : $this->makeTitleDocument($view_url);
		// $title = \str_replace(['-', 'controller'], ' ', ucfirst(kebab_case(class_basename(get_class($this)))));

		$view = call_user_func('view', $params)->with([
			'module_url'     => $this->moduleURL(),
			'title'          => $this->title,
			'title_document' => $title_document,
			'role'           => $this->role,
		]);

		if (in_array($view_name, ['edit', 'create'])) {
			$form = \implode('.', $view_folder->toArray()) . '.form';
			$view->with('form', $form);
			if (method_exists($this, 'formData')) {
				$view->with($this->formData());
			}
		}

		if ('index' === $view_name) {
			if (property_exists($this, 'search_view') or method_exists($this, 'searchData')) {
				$search_view_path = generateViewWithNamespace('search');
				if (null !== $this->search_view) {
					if (array_key_exists($this->search_view, config('generator.view.search.views_path')) && 'native' !== $this->search_view) {
						throw new Exception('Search view method not supported yet');
					} else {
						$search_view_path = config('generator.view.search_method')[$this->search_view];
					}
				}

				$input       = method_exists($this, 'searchData') ? $this->searchData() : [];
				$search_form = view($search_view_path, [
					'title_search' => sprintf(config('generator.view.search.title_format'), $this->title),
					'inputs'       => $this->renderSearchFormInputs($input),
					'col'          => config('generator.view.search.slip-input', 3) / 12,
				])->render();
				$view->with('search_view', $search_form);
			}
		}

		return $view;
	}

	public function renderSearchFormInput($inputs)
	{
		return $inputs->orderBy('place')->map(function ($item) {
			if ('select' === $item['type']) {
				$items['attributes'] = array_merge($items['attributes'], [
					'data-placeholder' => $item['placeholder'],
					'id'               => $item['name'],
					'class'            => empty($item['class']) ? config('generator.view.search.select2-class')
																											: sprintf('%s %s', $item['class'], config('generator.view.search.select2-class')),
				]);
			}
			$input = 'select' === $item['type'] ? Form::select($item['name'], $item['options'] ?? [], $item['value'] ?? null, $items['attributes'])
																					: Form::text($item['name'], $item['value'] ?? null, $item['attribute']);

			return $input;
		});
	}

	/**
	 * method ini adalah metho untuk
	 * mengecek apakah property ada di dalam class.
	 *
	 * @param string|array $property
	 */
	private function checkProperty($property)
	{
		if (!is_array($property)) {
			$property = (array) $property;
		}
		foreach ($property as $prop) {
			if (!property_exists($this, $prop)) {
				throw new \Exception("Attribute [$prop] in " . get_class($this) . ' Must be exsist', 1);
			}
		}
	}

	private function makeTitleDocument($view_url)
	{
		$view_name = collect(explode('.', $view_url))->last();
		if (method_exists($this, 'titleDocuments')) {
			if (isset($this->titleDocuments()[$view_name])) {
				return  $this->titleDocuments()[$view_name];
			}
		}
		$alias = [
			'index'  => 'Daftar',
			'create' => 'Tambah',
			'edit'   => 'Edit',
			'show'   => 'Detail',
		];
		if (isset($alias[$view_name])) {
			return "{$alias[$view_name]} $this->title";
		}

		return $this->title;
	}

	/**
	 * menggabungkan antara route yang tanpa parameter dengan route yang membutuhkan parameter.
	 */
	public function getFullRoute()
	{
		return array_merge($this->route_names, $this->route_names_need_id);
	}

	/**
	 * untuk membuat url permodule.
	 *
	 * @param string $module
	 * @param array  $slug_data
	 */
	public function moduleURL($module = null, $slug_data = [])
	{
		$module_url = $this->generateModuleRouteName($module ?? $this->module);
		// $route_names = $this->route_names;
		// if (count($slug_data) > 1) {
		// 	$route_names = $this->getFullRoute();
		// }
		// foreach ($route_names as $route_name) {
		// 	$module_url->{$route_name} = route($route_name, $slug_data);
		// }
		$module_url->back = url()->previous() == url()->current() ? $module_url->index : url()->previous();

		return $module_url;
	}

	/**
	 * generate route name for resources module.
	 *
	 * @param string $module
	 */
	public function generateModuleRouteName($module)
	{
		$module = str_replace(["{$this->moduleViewNamespace}_", '_'], ['', '-'], $module);
		if (property_exists($this, 'module_url')) {
			$module = $this->module_url;
		}
		if (!property_exists($this, 'role')) {
			throw new \Exception('Attribute [role] in ' . get_class($this) . ' Must be exsist', 1);
		}

		$list_url   = $this->getFullRoute();
		$module_url = new stdClass();
		foreach ($list_url as $url) {
			$module_url->{$url} = null === $this->role ? "$module.$url" : "{$this->role}.$module.$url";
		}

		return $module_url;
	}

	/**
	 * mengambil nama request.
	 *
	 * @return string
	 */
	public function getRequest()
	{
		$this->checkProperty('request');
		if (\is_object($this->request)) {
			return $this->request;
		}

		return app($this->request);
	}

	/**
	 * mengambil model.
	 *
	 * @return model
	 */
	public function getModel()
	{
		$this->checkProperty('model');
		if (null == $this->model) {
			throw new Exception('Repository must be defined and store in [model] attribute', 1);
		}
		if (!($this->model instanceof RepositoryInterface)) {
			$message = 'Repository in [model] attribute must be instance of ' . RepositoryInterface::class;
			if (is_object($this->model)) {
				$message .= ', but ' . \get_class($this->model) . ' given';
			}
			throw new Exception($message, 1);
		}

		return $this->model;
	}

	/**
	 * mengambil field request yang dibutuhkan.
	 *
	 * @return array
	 */
	public function getRequestField()
	{
		$this->checkProperty('requestField');
		// if(!property_exists($this,'requestField') && $this->requestField == null)
		// 	throw new Exception("request field is needed", 1);
		return $this->requestField;
	}

	/**
	 * meng-redirect pengguna kembali ke tampilan awal.
	 *
	 * @return redirect
	 */
	public function redirectToIndex()
	{
		return redirect()->route($this->moduleURL()->index);
	}

	/**
	 * wrapper message success or fail.
	 *
	 * @param [type] $actionFrom
	 * @param [type] $isSuccessOrFail
	 */
	public function messageSuccessOrFail($actionFrom, $isSuccessOrFail)
	{
		$entitas = $this->title ?? 'Entitas';
		$action  = $this->translatedActionMethod($actionFrom);

		return trans($this->messageFormat(), ['title' => $entitas, 'action' => $action]);
	}

	/**
	 * translate method used in crud progress to understand message response.
	 */
	public function translatedActionMethod($actionFrom = 'default')
	{
		$actionTranslated = trans("generator.translate_action_method.{$actionFrom}");
		if (str_contains('generator.translate_action_method', $actionTranslated)) {
			return trans('generator.translate_action_method.default');
		}

		return $actionTranslated;
	}

	public function messageFormat($isSuccess = true)
	{
		return $isSuccess ? config('generator.message_format.true') : config('generator.message_format.false');
	}

	/**
	 * success message wrapper.
	 *
	 * @param string $actionFrom
	 */
	public function messageSucces($actionFrom)
	{
		return $this->messageSuccessOrFail($actionFrom, true);
	}

	/**
	 * fail message wrapper.
	 *
	 * @param string $actionFrom
	 */
	public function messageFail($actionFrom)
	{
		return $this->messageSuccessOrFail($actionFrom, false);
	}

	/**
	 * fungsi untuk mengganti request yang sudah ada.
	 *
	 * @param request $request
	 * @param array   $newAttribute
	 *
	 * @return request
	 */
	public function replaceRequest($request, $newAttribute)
	{
		return $request->merge($newAttribute);
	}

	/**
	 * make redirect if store or update is success.
	 *
	 * @param ReIlluminate\Http\Request          $request
	 * @param Illuminate\Database\Eloquent\Model $result
	 *
	 * @return Illuminate\Http\Response
	 */
	public function redirectSuccess($actionFrom, Request $request, $result = null)
	{
		$formatResponse = $this->formatResponse($this->messageSucces($actionFrom));
		if ($request->ajax()) {
			return array_merge($data, [
				'url' => route($this->moduleURL()->index),
			]);
		}

		return $this->redirectToIndex()->with($formatResponse);
	}

	/**
	 * make redirect if store or update is fail.
	 *
	 * @param Illuminate\Http\Request $request
	 * @param string                  $message
	 *
	 * @return Illuminate\Http\Response
	 */
	public function redirectFail($actionFrom, Request $request, $exception = null)
	{
		$message          = $exception                                                    ?? $this->messageFail($actionFrom);
		$message          = in_array(get_class($exception), $this->dontReportException()) ?? $exception->getMessage();
		$formatedResponse = $this->formatResponse($message, false);

		return !$request->ajax() ? redirect()->back()->withInput()->with($formatedResponse)
														 : array_merge($formatedResponse, ['url' => $this->moduleURL()->back]);
	}

	/**
	 * format response from Crud progtress.
	 *
	 * @param string $message
	 * @param bool   $isSuccessOrFailLevel
	 */
	public function formatResponse($message, $isSuccessOrFailLevel = true)
	{
		return [
			'message' => $message,
			'type'    => $isSuccessOrFailLevel ? 'success' : 'error',
		];
	}

	public function dontReportException()
	{
		return config('generator.dont_report_exception');
	}
}
