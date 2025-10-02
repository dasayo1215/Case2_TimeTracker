<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    public function index()
    {
        // DBの items テーブルから全件取得して返す
        $items = Item::all();

        return response()->json($items);
    }

    public function show($id)
    {
        $item = Item::findOrFail($id);
        return response()->json($item);
    }
}
