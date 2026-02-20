<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    private const MAX_FAILED_ATTEMPTS = 3;

    public function __construct(private UserRepository $users) {}

    public function register(array $data): User
    {
        return $this->users->create($data);
    }

    public function login(Request $request, array $credentials): array
    {
        $user = $this->users->findByEmail($credentials['email']);

        if (! $user) {
            throw ValidationException::withMessages([
                'email' => 'Неверные учетные данные.',
            ]);
        }

        if ($user->locked_at || $user->must_reset_password) {
            return [
                'status' => 'reset',
                'message' => 'Аккаунт заблокирован. Требуется смена пароля.',
            ];
        }

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            $this->users->clearSecurityFlags($user);

            return [
                'status' => 'ok',
                'message' => 'Вы вошли в систему.',
            ];
        }

        $remaining = $this->users->registerFailedAttempt($user, self::MAX_FAILED_ATTEMPTS);

        if ($remaining === 0) {
            return [
                'status' => 'reset',
                'message' => 'Слишком много попыток. Аккаунт заблокирован, смените пароль.',
            ];
        }

        throw ValidationException::withMessages([
            'password' => 'Неверный пароль. Попыток осталось: '.$remaining.'.',
        ]);
    }

    public function loginUser(Request $request, User $user): void
    {
        Auth::login($user);
        $request->session()->regenerate();
    }

    public function logout(Request $request): void
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    public function resetPassword(array $data): void
    {
        $user = $this->users->findByEmail($data['email']);

        if (! $user) {
            throw ValidationException::withMessages([
                'email' => 'Пользователь не найден.',
            ]);
        }

        $this->users->resetPassword($user, Hash::make($data['password']));
    }
}
