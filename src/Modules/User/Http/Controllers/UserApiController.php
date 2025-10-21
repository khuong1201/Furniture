<?php
namespace Modules\User\Http\Controllers;

use Modules\User\Models\User;
use Modules\User\Traits\ApiResponse;
use Modules\User\Http\Requests\StoreUserRequest;
use Modules\User\Http\Requests\UpdateUserRequest;

class UserApiController extends BaseController
{
    use ApiResponse;

    public function index()
    {
        $users = User::active()->get();
        return $this->success($users);
    }

    public function show($id)
    {
        $user = User::findOrFail($id);
        return $this->success($user);
    }

    public function store(StoreUserRequest $request)
    {
        $user = User::create([
            'uuid' => \Str::uuid(),
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        return $this->success($user, 'Tạo người dùng thành công', 201);
    }

    public function update(UpdateUserRequest $request, $id)
    {
        $user = User::findOrFail($id);
        $user->update($request->validated());

        return $this->success($user, 'Cập nhật thành công');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->update(['is_deleted' => true]);

        return $this->success([], 'Đã xóa người dùng');
    }
}
