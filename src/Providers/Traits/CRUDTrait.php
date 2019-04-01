<?php
namespace Generator\Providers\Traits;

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

		return $this->view('index', compact('items'));
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create()
	{
		$this->generateNameModule();

		$view = $this->view('create');

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
			$result = $this->getModel()->insert($request->only($this->getRequestField()));
			if ($this instanceof CallbackAfterStoreOrUpdate) {
				$result = $this->callbackAfterStoreOrUpdate($result, $request, null);
			}
			DB::commit();

			return $this->redirectSuccess('create', $request, $result);
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

		$view = $this->view('show', ['item' => $item]);
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
		$view = $this->view('edit', ['item' => $item]);
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
			if ($this instanceof CallbackBeforeUpdate) {
				$this->callbackBeforeUpdate($this->getModel()->findItem($id));
			}
			if ($this instanceof CallbackAfterStoreOrUpdate) {
				$modeBefore = $this->getModel()->findItem($id);
			}
			$result = $this->getModel()->update($id, $request->only($this->getRequestField()));
			if ($this instanceof CallbackAfterStoreOrUpdate) {
				$result = $this->callbackAfterStoreOrUpdate($result, $request, $modeBefore);
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
		$req = request();
		$this->generateNameModule();

		DB::beginTransaction();
		try {
			$this->getModel()->delete($id);
			DB::commit();

			return $this->redirectSuccess('delete', $req);
		} catch (Exception $e) {
			DB::rollback();
			if (config('app.debug')) {
				throw $e;
			} else {
				return $this->redirectFail('delete', $req);
			}
		}
	}
}
