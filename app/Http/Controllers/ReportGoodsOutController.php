<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use App\Models\GoodsOut;
use App\Models\Item;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;

class ReportGoodsOutController extends Controller
{
  public function index(): View
  {
    return view('admin.master.laporan.keluar'); // pastikan path view kamu sesuai
  }

  public function list(Request $request): JsonResponse
  {
    if ($request->ajax()) {
      if (empty($request->start_date) && empty($request->end_date)) {
        $goodsouts = GoodsOut::with('item.unit', 'user', 'customer');
      } else {
        $goodsouts = GoodsOut::with('item.unit', 'user', 'customer')
          ->whereBetween('date_out', [$request->start_date, $request->end_date]);
      }
      $goodsouts = $goodsouts->latest()->get();

      return DataTables::of($goodsouts)
        ->addColumn('quantity', function ($data) {
          return $data->quantity . '/' . $data->item->unit->name;
        })
        ->addColumn('date_out', function ($data) {
          return Carbon::parse($data->date_out)->format('d F Y');
        })
        ->addColumn('kode_barang', function ($data) {
          return $data->item->code;
        })
        ->addColumn('customer_name', function ($data) {
          return $data->customer->name;
        })
        ->addColumn('item_name', function ($data) {
          return $data->item->name;
        })
        ->make(true);
    }

    return response()->json(['error' => 'Invalid request'], 400);
  }
}
