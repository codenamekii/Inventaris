<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use App\Models\GoodsOut;
use App\Models\GoodsIn;
use App\Models\Customer;
use App\Models\Item;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;

class TransactionOutController extends Controller
{
  public function index(): View
  {
    $in_status = Item::where('active', 'true')->count();
    $customers = Customer::all();
    return view('admin.master.transaksi.keluar', compact('customers', 'in_status'));
  }

  public function list(Request $request): JsonResponse
  {
    if (!$request->ajax()) {
      return response()->json([
        'message' => __('Invalid request')
      ], 400);
    }

    $goodsouts = GoodsOut::with('item.unit', 'user', 'customer')->latest();

    return DataTables::of($goodsouts)
      ->addColumn('quantity', function ($data) {
        return $data->quantity . "/" . $data->item->unit->name;
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
      ->addColumn('tindakan', function ($data) {
        $edit = "<button class='ubah btn btn-success m-1' id='{$data->id}'><i class='fas fa-pen m-1'></i>" . __("edit") . "</button>";
        $delete = "<button class='hapus btn btn-danger m-1' id='{$data->id}'><i class='fas fa-trash m-1'></i>" . __("delete") . "</button>";
        return $edit . $delete;
      })
      ->rawColumns(['tindakan'])
      ->make(true);
  }

  public function save(Request $request): JsonResponse
  {
    $request->validate([
      'item_id' => 'required|exists:items,id',
      'user_id' => 'required|exists:users,id',
      'quantity' => 'required|numeric|min:1',
      'invoice_number' => 'required|string',
      'date_out' => 'required|date',
      'customer_id' => 'required|exists:customers,id',
    ]);

    // cek stok total untuk item ini
    $goodsIn = GoodsIn::where('item_id', $request->item_id)->sum('quantity');
    $goodsOut = GoodsOut::where('item_id', $request->item_id)->sum('quantity');
    $currentStock = max(0, $goodsIn - $goodsOut);

    if ($request->quantity > $currentStock) {
      return response()->json([
        'message' => __('Stok tidak mencukupi')
      ], 400);
    }

    GoodsOut::create([
      'item_id' => $request->item_id,
      'user_id' => $request->user_id,
      'quantity' => $request->quantity,
      'invoice_number' => $request->invoice_number,
      'date_out' => $request->date_out,
      'customer_id' => $request->customer_id,
    ]);

    return response()->json([
      'message' => __('Berhasil disimpan')
    ], 200);
  }

  public function detail(Request $request): JsonResponse
  {
    $data = GoodsOut::with('customer')->findOrFail($request->id);
    $barang = Item::with('category', 'unit')->findOrFail($data->item_id);

    $detail = [
      'kode_barang' => $barang->code,
      'satuan_barang' => $barang->unit->name,
      'jenis_barang' => $barang->category->name,
      'nama_barang' => $barang->name,
      'customer_id' => $data->customer_id,
      'id_barang' => $barang->id,
      'quantity' => $data->quantity,
      'date_out' => $data->date_out,
    ];

    return response()->json(['data' => $detail], 200);
  }

  public function update(Request $request): JsonResponse
  {
    $request->validate([
      'id' => 'required|exists:goods_out,id',
      'item_id' => 'required|exists:items,id',
      'user_id' => 'required|exists:users,id',
      'customer_id' => 'required|exists:customers,id',
      'date_out' => 'required|date',
      'quantity' => 'required|numeric|min:1',
    ]);

    $data = GoodsOut::findOrFail($request->id);

    $data->update([
      'item_id' => $request->item_id,
      'user_id' => $request->user_id,
      'customer_id' => $request->customer_id,
      'date_out' => $request->date_out,
      'quantity' => $request->quantity,
    ]);

    return response()->json([
      'message' => __('Data berhasil diubah')
    ], 200);
  }

  public function delete(Request $request): JsonResponse
  {
    $request->validate([
      'id' => 'required|exists:goods_out,id',
    ]);

    $data = GoodsOut::findOrFail($request->id);
    $data->delete();

    return response()->json([
      'message' => __('Data berhasil dihapus')
    ], 200);
  }
}
