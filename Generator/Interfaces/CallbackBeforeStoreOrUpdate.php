<?php
namespace Generator\Interfaces;

/**
 * interface ini berfungsi untuk memberitahu controller
 * bahwa terdapat callback sebelum terjadinya
 * penyimpanan atau pengupdatetan data.
 */
interface CallbackBeforeStoreOrUpdate
{
	/**
	 * fungsi untuk melakukan callback sebelum
	 * terjadinya penyimpanan dan pengupdatetan data.
	 *
	 * @param Illuminate\Http\Request $request
	 *
	 * @return Illuminate\Http\Request
	 */
	public function callbackBeforeStoreOrUpdate($request);
}
