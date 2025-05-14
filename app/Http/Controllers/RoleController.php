<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Role;

class RoleController extends Controller
{
  /**
   * Simpan role baru
   */
  public function save(Request $request): JsonResponse
  {
    $role = new Role();
    $role->name = $request->name;
    $status = $role->save();

    if (!$status) {
      return response()->json(
        ["message" => __("failed to save")]
      )->setStatusCode(400);
    }

    return response()->json([
      "message" => __("saved successfully")
    ])->setStatusCode(200);
  }

  /**
   * Tampilkan detail role
   */
  public function detail(Request $request): JsonResponse
  {
    $id = $request->id;
    $role = Role::find($id);

    return response()->json(
      ["data" => $role]
    )->setStatusCode(200);
  }

  /**
   * Update role
   */
  public function update(Request $request): JsonResponse
  {
    $id = $request->id;
    $role = Role::find($id);

    if ($request->has('name')) {
      $role->name = $request->name;
    }

    $status = $role->save();

    if (!$status) {
      return response()->json(
        ["message" => __("data failed to change")]
      )->setStatusCode(400);
    }

    return response()->json([
      "message" => __("data changed successfully")
    ])->setStatusCode(200);
  }

  /**
   * Hapus role
   */
  public function delete(Request $request): JsonResponse
  {
    $id = $request->id;
    $role = Role::find($id);

    // Cek apakah role tersebut digunakan oleh user
    if ($role->users()->count() > 0) {
      return response()->json(
        ["message" => __("This role is still in use by some users")]
      )->setStatusCode(400);
    }

    $status = $role->delete();

    if (!$status) {
      return response()->json(
        ["message" => __("data failed to delete")]
      )->setStatusCode(400);
    }

    return response()->json([
      "message" => __("data deleted successfully")
    ])->setStatusCode(200);
  }
}
