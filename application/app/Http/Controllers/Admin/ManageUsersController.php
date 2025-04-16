<?php

namespace App\Http\Controllers\Admin;

use Throwable;
use Illuminate\Http\Request;
use App\Services\UserService;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
// User model was missing in ManageUsersController w/ Added support/facades/hash
use App\Models\User;
use Illuminate\Support\Facades\Hash;

use Illuminate\Contracts\Support\Renderable;

class ManageUsersController extends Controller
{
    private UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function index(Request $request): Renderable
    {
        $allUsers = $this->userService->allUsers($request->search ?? null);
        return view('admin.manage-users.index', compact('allUsers'));
    }

    public function addUser(): Renderable
    {
        $user = null;
        return view('admin.manage-users.form', compact('user'));
    }

    public function edit(int $userId): Renderable|RedirectResponse
    {
        $user = $this->userService->findById($userId);
        if (!$user) {
            return redirect()
                ->route('admin.manage-users.index')
                ->with('error', trans('user does not exist'));
        }
        return view('admin.manage-users.form', compact('user'));
    }

    public function save(Request $request)
    {
        $data = $request->only(['name', 'email', 'password', 'roles']);

        try {
            if ($request->has('user_id')) {
                $user = User::findOrFail($request->input('user_id'));
                $user->name = $data['name'];
                $user->email = $data['email'];
                if (!empty($data['password'])) {
                    $user->password = Hash::make($data['password']);
                }
                $user->roles = $data['roles'];
                $user->save();

                session()->flash('status', __('account updated'));
            } else {
                $user = new User([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'password' => Hash::make($data['password']),
                    'roles' => $data['roles'],
                ]);
                $user->save();

                session()->flash('status', __('account created'));
            }

            return redirect()->route('admin.manage-users.index');

        } catch (\Exception $e) {
            session()->flash('error', 'failed to save user');
            return redirect()->back()->withInput();
        }
    }
}
