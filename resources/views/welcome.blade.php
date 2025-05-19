<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    {{-- <title>{{ config('app.name', 'Nicolites School Management System') }}</title> --}}
    <title>Landing Page</title>
    @vite(['resources/sass/app.scss','resources/js/app.js'])
    
    <!-- Bootstrap CSS -->
    {{-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"> --}}
    
    <!-- Fonts -->
    {{-- <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet"> --}}
    
    <style>
        body {
            font-family: 'Nunito', sans-serif;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container">
        <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm rounded mt-4">
            <div class="container-fluid">
                <a class="navbar-brand" href="/">Header Main Navbar</a>
            </div>
             <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link active" aria-current="page" href="/">Home</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="/login">Login</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="/signup">Signup</a>
        </li>
      </ul>
        </nav>

        <div class="row justify-content-center mt-5">
            <div class="col-md-8">
                <div class="card shadow-lg">
                    <div class="card-body text-center py-5">
                        <h1 class="display-4 mb-4">Header 1</h1>
                        <h2 class="h4 text-muted mb-4">Header 2</h2> 
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mt-5 mb-4">
            <div class="col-12 text-center">
                <p class="text-muted">© {{ date('Y') }} Footer Test</p>
            </div>
        </div>
    </div>

</body>
</html>
