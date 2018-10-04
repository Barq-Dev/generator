<?php
namespace Generator\Traits;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Generator\Interfaces\CallbackAfterStoreOrUpdate;
use Generator\Interfaces\CallbackBeforeStoreOrUpdate;

trait CRUDTrait
{
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index()
	{
		$this->generateNameModule();
		if (request()->has('search')) {
			$items = $this->getModel()->filter(collect(request()->all()));
		} else {
			$items = $this->getModel()->getItems();
		}

		return $this->view($this->module . '.index', compact('items'));
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create()
	{
		$this->generateNameModule();

		$view = $this->view($this->module . '.create');

		if (method_exists($this, 'embedData')) {
			$view->with($this->embedData(null));
		}

		return $view;
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param \Illuminate\Http\Request $request
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function store()
	{
		$this->generateNameModule();
		$request = $this->getRequest();
		if ($this instanceof CallbackBeforeStoreOrUpdate) {
			$request = $this->callbackBeforeStoreOrUpdate($request);
		}
		DB::beginTransaction();
		try {
			$resul = $this->getModel()->insert($request->only($this->getRequestField()));
			if ($this instanceof CallbackAfterStoreOrUpdate) {
				$this->callbackAfterStoreOrUpdate($resul, $request);
			}
			DB::commit();

			return $this->redirectSuccess('create', $request, $resul);
		} catch (Exception $e) {
			DB::rollback();
			if (config('app.debug')) {
				throw $e;
			} else {
				return $this->redirectFail('create', $request, $e);
			}
		}
	}

	/**
	 * Display the specified resource.
	 *
	 * @param int $id
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function show($id)
	{
		$this->generateNameModule();
		$item = $this->getModel()->finditem($id);

		$view = $this->view($this->module . '.show', ['item' => $item]);
		if (method_exists($this, 'embedDataToShow')) {
			$view->with($this->embedDataToShow($id, collect(request()->all())));
		}

		return $view;
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param int $id
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function edit($id)
	{
		$this->generateNameModule();
		$item = $this->getModel()->findItem($id);
		$view = $this->view($this->module . '.edit', ['item' => $item]);
		//it will deprecated
		if (method_exists($this, 'embedDataToEdit')) {
			$view->with($this->embedDataToEdit($id));
		} else {
			if (method_exists($this, 'embedData')) {
				$view->with($this->embedData($id));
			}
		}

		return $view;
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param \Illuminate\Http\Request $request
	 * @param int                      $id
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function update($id)
	{
		$this->generateNameModule();
		$request = $this->getRequest();

		if ($this instanceof CallbackBeforeStoreOrUpdate) {
			$request = $this->callbackBeforeStoreOrUpdate($request);
		}

		//make model or update model

		DB::beginTransaction();
		try {
			$result = $this->getModel()->update($id, $request->only($this->getRequestField()));
			if ($this instanceof CallbackAfterStoreOrUpdate) {
				$this->callbackAfterStoreOrUpdate($result, $request);
			}
			DB::commit();

			return $this->redirectSuccess('update', $request, $result);
		} catch (Exception $e) {
			DB::rollback();
			if (config('app.debug')) {
				throw $e;
			} else {
				return $this->redirectFail('update', $request, $e->getMessage());
			}
		}
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param int $id
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function destroy($id)
	{
		$request = $this->getRequest();
		$this->generateNameModule();

		DB::beginTransaction();
		try {
			$this->getModel()->delete($id);
			DB::commit();

			return $this->redirectSuccess('delete', $request);
		} catch (Exception $e) {
			DB::rollback();
			if (config('app.debug')) {
				throw $e;
			} else {
				return $this->redirectFail('delete', $request);
			}
		}
	}
}
