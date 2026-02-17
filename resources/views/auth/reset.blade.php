<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Смена пароля</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 24px; }
        .container { max-width: 420px; margin: 0 auto; }
        label { display: block; margin-top: 12px; }
        input { width: 100%; padding: 8px; margin-top: 4px; }
        button { margin-top: 16px; padding: 10px 16px; }
        .error { color: #b91c1c; margin-top: 8px; }
        .status { color: #065f46; margin-top: 8px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Смена пароля</h1>

        @if (session('status'))
            <div class="status">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="error">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="post" action="/password/reset">
            @csrf
            <label>
                Email
                <input type="email" name="email" value="{{ old('email') }}" required>
            </label>
            <label>
                Новый пароль
                <input type="password" name="password" required>
            </label>
            <label>
                Повтор пароля
                <input type="password" name="password_confirmation" required>
            </label>
            <button type="submit">Сменить пароль</button>
        </form>

        <p><a href="/login">Вернуться к входу</a></p>
    </div>
</body>
</html>
