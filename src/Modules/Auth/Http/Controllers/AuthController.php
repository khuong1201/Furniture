<?php

namespace Modules\Auth\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Modules\User\Models\User;

class AuthController extends Controller
{
    public function index()
    {
        $users = User::orderBy('created_at', 'desc')->paginate(20);
        return view('auths.index', compact('users'));
    }

    // Form tạo user
    public function create()
    {
        return view('auths.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::create([
            'uuid' => Str::uuid(),
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $roleId = DB::table('roles')->where('name', 'customer')->value('id');
        if (!$roleId) {
            $roleId = DB::table('roles')->insertGetId([
                'name' => 'customer',
                'label' => 'Khách hàng',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        if (!DB::table('role_user')->where(['user_id' => $user->id, 'role_id' => $roleId])->exists()) {
            DB::table('role_user')->insert([
                'user_id' => $user->id,
                'role_id' => $roleId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return redirect()->route('auth.index')->with('success', 'Tạo user thành công.');
    }

    // Hiển thị chi tiết user
    public function show(User $auth)
    {
        return view('auths.show', ['user' => $auth]);
    }

    // Form edit user
    public function edit(User $auth)
    {
        return view('auths.edit', ['user' => $auth]);
    }

    // Cập nhật user
    public function update(Request $request, User $auth)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $auth->id,
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        $auth->name = $validated['name'];
        $auth->email = $validated['email'];
        if (!empty($validated['password'])) {
            $auth->password = Hash::make($validated['password']);
        }
        $auth->save();

        return redirect()->route('auth.show', $auth->id)->with('success', 'Cập nhật thành công.');
    }

    // Xóa user
    public function destroy(User $auth)
    {
        $auth->delete();
        return redirect()->route('auth.index')->with('success', 'Xóa user thành công.');
    }
}
