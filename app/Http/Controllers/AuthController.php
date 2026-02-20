<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Services\Auth\AuthService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(private AuthService $authService) {}

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(RegisterRequest $request)
    {
        $data = $request->validated();

        $user = $this->authService->register($data);
        $this->authService->loginUser($request, $user);

        return redirect('/')->with('status', 'Регистрация успешна.');
    }

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();

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

    public function resetPassword(ResetPasswordRequest $request)
    {
        $data = $request->validated();

        $this->authService->resetPassword($data);

        return redirect('/login')->with('status', 'Пароль обновлен. Войдите снова.');
    }
}
