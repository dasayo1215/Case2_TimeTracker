<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class StaffController extends Controller
{
    public function getList(Request $request)
    {
        $users = User::where('role', 'user')
            ->orderBy('id')
            ->get();

        $records = $users->map(function ($u) {
            return [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
            ];
        });

        return response()->json($records);
    }
}
