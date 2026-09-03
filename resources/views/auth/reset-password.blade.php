<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Agenda Kelas Digital</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-linear-to-br from-blue-50 via-white to-indigo-50 font-['Inter'] min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full space-y-8 p-8 bg-white rounded-2xl shadow-xl">
        <h2 class="text-2xl font-bold text-center text-gray-900">Reset Password</h2>
        
        @if ($errors->any())
            <div class="bg-red-100 text-red-700 p-4 rounded-lg text-sm">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('password.update') }}" class="space-y-6">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input id="email" name="email" type="email" required class="block w-full px-3 py-2 border rounded-xl" value="{{ old('email', $email) }}">
            </div>
            
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Password Baru</label>
                <input id="password" name="password" type="password" required class="block w-full px-3 py-2 border rounded-xl">
            </div>
            
            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Konfirmasi Password</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required class="block w-full px-3 py-2 border rounded-xl">
            </div>
            
            <button type="submit" class="w-full py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700">Reset Password</button>
        </form>
    </div>
</body>
</html>
