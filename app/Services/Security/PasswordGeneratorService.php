<?php

namespace App\Services\Security;

use InvalidArgumentException;

class PasswordGeneratorService
{
    public function generate(int $length): string
    {
        if ($length < 8) {
            throw new InvalidArgumentException('Минимальная длина пароля — 8 символов.');
        }

        $upper = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $lower = 'abcdefghijklmnopqrstuvwxyz';
        $digits = '0123456789';
        $special = '!@#$%^&*()-_=+[]{};:,.<>?';

        $all = $upper.$lower.$digits.$special;

        $passwordChars = [
            $upper[random_int(0, strlen($upper) - 1)],
            $lower[random_int(0, strlen($lower) - 1)],
            $digits[random_int(0, strlen($digits) - 1)],
            $special[random_int(0, strlen($special) - 1)],
        ];

        for ($i = 4; $i < $length; $i++) {
            $passwordChars[] = $all[random_int(0, strlen($all) - 1)];
        }

        for ($i = count($passwordChars) - 1; $i > 0; $i--) {
            $j = random_int(0, $i);
            $tmp = $passwordChars[$i];
            $passwordChars[$i] = $passwordChars[$j];
            $passwordChars[$j] = $tmp;
        }

        return implode('', $passwordChars);
    }
}
