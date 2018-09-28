<?php
namespace Generator\Providers\Traits;

use Illuminate\Http\Request;
use Generator\Interfaces\RepositoryInterface;
use Exception;

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
		$output = call_user_func_array('view', $params)->with('module_url', $this->module_url($this->module))
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

	public function module_url($module)
	{
		$module = str_replace(["{$this->viewNamespace}_", '_'], ['_', '-'], $module);
		if (property_exists($this, 'module_url')) {
			$module = $this->module_url;
		}
		if (!property_exists($this, 'role')) {
			throw new \Exception('Attribute [role] in ' . get_class($this) . ' Must be exsist', 1);
		}

		$list_url = ['index', 'create', 'store', 'show', 'edit', 'update', 'destroy'];

		foreach ($list_url as $url) {
			$module_url[$url] = null === $this->role ? "$module.$url" : "{$this->role}.$module.$url";
		}
		$module_url['back'] = url()->previous() == url()->current() ? route($module_url['index']) : url()->previous();

		return json_decode(json_encode($module_url), false);
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
				'message' => 'Berhasil Menambah/Memperbarui data',
				'url'     => route($this->module_url($this->module)->index),
				'type'    => 'success',
			];
		}

		return redirect()->route($this->module_url($this->module)->index)
						 ->withMessage('Berhasil Menambah/Memperbarui data');
	}

	/**
	 * meng-redirect user kembali ke halaman sebelumnya beserta
	 * input dan error.
	 *
	 * @return redirect
	 */
	public function redirectBackWithError(Request $request, $message = 'Aksi gagal')
	{
		if ($request->ajax()) {
			return [
				'message' => $message,
				'type'    => 'error',
			];
		}

		return redirect()->back()->withInput()->withErrors($message);
	}

	/**
	 * meng-redirect user kembali ke halaman sebelumnya tanpa
	 * input dan error.
	 *
	 * @return redirect
	 */
	public function redirectBackWithoutErrorMessage(Request $request)
	{
		if ($request->ajax()) {
			return [
				'message' => 'Aksi Gagal',
				'type'    => 'error',
			];
		}

		return redirect()->route($this->module_url($this->module)->index)
			->withMessage(session()->get('message'));
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
