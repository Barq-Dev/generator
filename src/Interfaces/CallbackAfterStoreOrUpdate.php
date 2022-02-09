<?php
namespace Generator\Interfaces;

/**
 * interface ini berfungsi untuk memberitahu controller
 * bahwa terdapat callback setelah terjadinya
 * penyimpanan atau pengupdatetan data.
 */
interface CallbackAfterStoreOrUpdate
{
  /**
   * fungsi ini berguna untuk melakukah
   * hal lain setelah terjadinya penyimpanan
   * atau perubahan data.
   *
   * @param \Illuminate\Database\Eloquent\Model      $model
   * @param \Illuminate\Http\Request                 $request
   * @param \Illuminate\Database\Eloquent\Model|null $modelBefore
   */
  public function callbackAfterStoreOrUpdate($model, $request, $modelBefore = null);
}
