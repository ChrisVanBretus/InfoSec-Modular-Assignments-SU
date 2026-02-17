<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
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

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
        ]);

        Auth::login($user);
        $request->session()->regenerate();

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

        $user = User::where('email', $credentials['email'])->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => 'Неверные учетные данные.',
            ]);
        }

        if ($user->locked_at || $user->must_reset_password) {
            return redirect('/password/reset')
                ->withErrors(['email' => 'Аккаунт заблокирован. Требуется смена пароля.']);
        }

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            $user->forceFill([
                'failed_attempts' => 0,
                'locked_at' => null,
                'must_reset_password' => false,
            ])->save();

            return redirect('/')->with('status', 'Вы вошли в систему.');
        }

        $user->failed_attempts = (int) $user->failed_attempts + 1;

        if ($user->failed_attempts >= 3) {
            $user->forceFill([
                'failed_attempts' => $user->failed_attempts,
                'locked_at' => now(),
                'must_reset_password' => true,
            ])->save();

            return redirect('/password/reset')
                ->withErrors(['email' => 'Слишком много попыток. Аккаунт заблокирован, смените пароль.']);
        }

        $user->save();

        throw ValidationException::withMessages([
            'password' => 'Неверный пароль. Попыток осталось: ' . (3 - $user->failed_attempts) . '.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

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

        $user = User::where('email', $data['email'])->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => 'Пользователь не найден.',
            ]);
        }

        $user->forceFill([
            'password' => Hash::make($data['password']),
            'failed_attempts' => 0,
            'locked_at' => null,
            'must_reset_password' => false,
        ])->save();

        return redirect('/login')->with('status', 'Пароль обновлен. Войдите снова.');
    }
}
