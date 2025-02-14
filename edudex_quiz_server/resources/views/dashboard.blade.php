<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <h1>Dashboard</h1>
        <p>Xin chào, {{ auth()->user()->username }}</p>
        
        <div class="mb-3">
            @if(auth()->user()->role === 0)
                <a href="/test/admin" class="btn btn-primary">Test Admin View</a>
            @elseif(auth()->user()->role === 1)
                <a href="/test/teacher" class="btn btn-success">Test Teacher View</a>
            @else
                <a href="/test/staff" class="btn btn-warning">Test Staff View</a>
            @endif
        </div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-danger">Đăng xuất</button>
        </form>
    </div>
</body>
</html> 