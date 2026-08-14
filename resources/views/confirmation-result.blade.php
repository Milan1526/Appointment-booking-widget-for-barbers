<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Potvrda termina - Barber Boki</title>
    @vite('resources/css/app.css')
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="max-w-md mx-4 bg-white rounded-xl shadow-md p-8 text-center">
        <div class="text-5xl mb-4">{{ $success ? '✅' : '❌' }}</div>
        <h1 class="text-xl font-semibold mb-2">{{ $success ? 'Uspešno!' : 'Greška' }}</h1>
        <p class="text-gray-600">{{ $message }}</p>
    </div>
</body>
</html>