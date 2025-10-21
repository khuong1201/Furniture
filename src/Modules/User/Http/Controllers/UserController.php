<?php

namespace Modules\User\Http\Controllers;

use Modules\User\Models\User;
use Modules\User\Http\Requests\StoreUserRequest;
use Modules\User\Http\Requests\UpdateUserRequest;

class UserController extends BaseController
{
    public function index()
    {
        $users = User::active()->get();
        return view('user::index', compact('users'));
    }

    public function create()
    {
        return view('user::create');
    }

    public function store(StoreUserRequest $request)
    {
        User::create([
            'uuid' => \Str::uuid(),
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'is_active' => true,
            'is_deleted' => false,
        ]);

        return redirect()->route('users.index')->with('success', 'Tạo người dùng thành công');
    }

    public function show($id)
    {
        $user = User::findOrFail($id);
        return view('user::show', compact('user'));
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        return view('user::edit', compact('user'));
    }

    public function update(UpdateUserRequest $request, $id)
    {
        $user = User::findOrFail($id);
        $user->update($request->validated());

        return redirect()->route('users.index')->with('success', 'Cập nhật thành công');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->update(['is_deleted' => true]);

        return redirect()->route('users.index')->with('success', 'Đã xóa người dùng');
    }
}