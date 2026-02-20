<?php

use App\Services\Security\PasswordGeneratorService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use InvalidArgumentException;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Artisan::command('password:generate {length=12}', function () {
    $length = (int) $this->argument('length');
    $generator = app(PasswordGeneratorService::class);

    try {
        $password = $generator->generate($length);
    } catch (InvalidArgumentException $exception) {
        $this->error($exception->getMessage());

        return 1;
    }

    $this->info($password);
    $this->comment('Правило: минимум 8 символов, хотя бы одна заглавная, одна строчная, одна цифра и один спецсимвол.');
})->purpose('Generate a password that matches the validation rule');
