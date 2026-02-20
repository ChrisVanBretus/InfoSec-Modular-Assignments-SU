<?php

namespace App\Http\Controllers;

use App\Services\Auth\AuthService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(private AuthService $authService) {}

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = $this->authService->register($data);
        $this->authService->loginUser($request, $user);

        return redirect('/')->with('status', 'Регистрация успешна.');
    }

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $result = $this->authService->login($request, $credentials);

        if ($result['status'] === 'reset') {
            return redirect('/password/reset')
                ->withErrors(['email' => $result['message']]);
        }

        return redirect('/')->with('status', $result['message']);
    }

    public function logout(Request $request)
    {
        $this->authService->logout($request);

        return redirect('/login')->with('status', 'Вы вышли из системы.');
    }

    public function showReset()
    {
        return view('auth.reset');
    }

    public function resetPassword(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $this->authService->resetPassword($data);

        return redirect('/login')->with('status', 'Пароль обновлен. Войдите снова.');
    }
}
