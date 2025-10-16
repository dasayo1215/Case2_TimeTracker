<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;

class StaffController extends Controller
{
    public function getList()
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
