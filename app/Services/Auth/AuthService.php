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
                'email' => 'РќРµРІРµСЂРЅС‹Рµ СѓС‡РµС‚РЅС‹Рµ РґР°РЅРЅС‹Рµ.',
            ]);
        }

        if ($user->locked_at || $user->must_reset_password) {
            return [
                'status' => 'reset',
                'message' => 'РђРєРєР°СѓРЅС‚ Р·Р°Р±Р»РѕРєРёСЂРѕРІР°РЅ. РўСЂРµР±СѓРµС‚СЃСЏ СЃРјРµРЅР° РїР°СЂРѕР»СЏ.',
            ];
        }

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            $this->users->clearSecurityFlags($user);

            return [
                'status' => 'ok',
                'message' => 'Р’С‹ РІРѕС€Р»Рё РІ СЃРёСЃС‚РµРјСѓ.',
            ];
        }

        $remaining = $this->users->registerFailedAttempt($user, self::MAX_FAILED_ATTEMPTS);

        if ($remaining === 0) {
            return [
                'status' => 'reset',
                'message' => 'РЎР»РёС€РєРѕРј РјРЅРѕРіРѕ РїРѕРїС‹С‚РѕРє. РђРєРєР°СѓРЅС‚ Р·Р°Р±Р»РѕРєРёСЂРѕРІР°РЅ, СЃРјРµРЅРёС‚Рµ РїР°СЂРѕР»СЊ.',
            ];
        }

        throw ValidationException::withMessages([
            'password' => 'РќРµРІРµСЂРЅС‹Р№ РїР°СЂРѕР»СЊ. РџРѕРїС‹С‚РѕРє РѕСЃС‚Р°Р»РѕСЃСЊ: '.$remaining.'.',
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
                'email' => 'РџРѕР»СЊР·РѕРІР°С‚РµР»СЊ РЅРµ РЅР°Р№РґРµРЅ.',
            ]);
        }

        $this->users->resetPassword($user, Hash::make($data['password']));
    }
}
