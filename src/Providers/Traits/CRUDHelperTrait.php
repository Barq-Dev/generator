<?php
namespace Generator\Providers\Traits;

use stdClass;
use Exception;
use Illuminate\Http\Request;
use Generator\Interfaces\RepositoryInterface;

trait CRUDHelperTrait
{
	/**
	 * untuk menampilkan view.
	 *
	 * @return view
	 */
	public function view()
	{
		$this->checkProperty(['title']);
		$params = func_get_args();
		if (property_exists($this, 'viewNamespace') && null !== $this->viewNamespace) {
			$view_url  = str_start(str_replace(str_finish($this->viewNamespace, '_'), str_finish($this->viewNamespace, '::'), $params[0]), str_finish($this->viewNamespace, '::'));
			$params[0] = $view_url;
		} else {
			$view_url = $params[0];
		}
		$view_folder = collect(\explode('.', $view_url));
		$view_name   = $view_folder->pop();

		$title_document = isset($params[2]['title_document']) ? $ $params[2]['title_document'] : $this->makeTitleDocument($view_url);
		// $title = \str_replace(['-', 'controller'], ' ', ucfirst(kebab_case(class_basename(get_class($this)))));
		$output = call_user_func_array('view', $params)->with('module_url', $this->moduleURL())
													->with('title', $this->title)
													->with('title_document', $title_document);
		if (in_array($view_name, ['edit', 'create'])) {
			$form = \implode('.', $view_folder->toArray()) . '.form';
			$output->with('form', $form);
			if (method_exists($this, 'formData')) {
				$output->with($this->formData());
			}
		}

		return $output;
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

	public function moduleURL($module = null)
	{
		$module = str_replace(["{$this->viewNamespace}_", '_'], ['', '-'], $module ?? $this->module);
		if (property_exists($this, 'module_url')) {
			$module = $this->module_url;
		}
		if (!property_exists($this, 'role')) {
			throw new \Exception('Attribute [role] in ' . get_class($this) . ' Must be exsist', 1);
		}

		$list_url   = ['index', 'create', 'store', 'show', 'edit', 'update', 'destroy'];
		$module_url = new stdClass();
		foreach ($list_url as $url) {
			$module_url->{$url} = null === $this->role ? "$module.$url" : "{$this->role}.$module.$url";
		}
		$module_url->url = url()->previous() == url()->current() ? route($module_url['index']) : url()->previous();

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
	public function redirectToIndex(Request $request)
	{
		if ($request->ajax()) {
			return [
				'message' => $this->messageSucces($request),
				'url'     => route($this->moduleURL()->index),
				'type'    => 'success',
			];
		}

		return redirect()->route($this->moduleURL()->index)
						 ->withMessage($this->messageSucces($request));
	}

	public function messageSuccessOrFail($methodCUD, $isSuccessOrFail)
	{
		$entitas          = $this->title ?? 'Entitas';
		$translateMethod  = $this->translatedActionMethod();
		$translatedMethod = $translatedMethod[$methodCUD] ?? 'dilakukan';

		return sprintf($this->messageFormat(), $entitas, $translatedMethod);
	}

	public function translatedActionMethod()
	{
		return [
			'POST'   => 'ditambah',
			'PUT'    => 'diubah',
			'DELETE' => 'dihapus',
		];
	}

	public function messageFormat($isSuccess = true)
	{
		return $isSuccess ? '%s berhasil %s' : '%s gagal %s';
	}

	public function messageSucces(Request $request)
	{
		return $this->messageSuccessOrFail($request->getMethod(), true);
	}

	public function messageFail(Request $request)
	{
		return $this->messageSuccessOrFail($request->getMethod(), false);
	}

	/**
	 * meng-redirect user kembali ke halaman sebelumnya beserta
	 * input dan error.
	 *
	 * @return redirect
	 */
	public function redirectBackWithError(Request $request, $message = null)
	{
		$message = $message ?? $this->messageFail($request);
		if ($request->ajax()) {
			return [
				'message' => $message,
				'type'    => 'error',
			];
		}

		return redirect()->back()->withInput()->withMessageError($message);
	}

	/**
	 * meng-redirect user kembali ke halaman sebelumnya tanpa
	 * input dan error.
	 *
	 * @return redirect
	 */
	public function redirectBackWithoutErrorMessage(Request $request)
	{
		$message = $this->messageFail($request);
		if ($request->ajax()) {
			return [
				'message' => $message,
				'type'    => 'error',
			];
		}

		return redirect()->route($this->moduleURL()->index)
			->withMessage($message);
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
	public function redirectSuccess(Request $request, $result = null)
	{
		return $this->redirectToIndex($request);
	}

	/**
	 * make redirect if store or update is fail.
	 *
	 * @param Illuminate\Http\Request $request
	 * @param string                  $message
	 *
	 * @return Illuminate\Http\Response
	 */
	public function redirectFail(Request $request, $message = 'Gagal')
	{
		if ($message = 'Gagal') {
			// Antisipasi jika $message tidak terisi
			return $this->redirectBackWithoutErrorMessage($request);
		}

		return $this->redirectBackWithError($request, $message);
	}
}
