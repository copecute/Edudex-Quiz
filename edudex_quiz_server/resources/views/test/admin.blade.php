<!DOCTYPE html>
<html>
<head>
    <title>Test Admin View</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="alert alert-primary">
            <h1>Test Admin View</h1>
            <p>Đây là trang test dành cho Admin (role = 0)</p>
            <p>User hiện tại: {{ auth()->user()->username }}</p>
            <p>Role: {{ auth()->user()->role }}</p>
        </div>
        <a href="{{ route('dashboard') }}" class="btn btn-primary">Quay về Dashboard</a>
    </div>
</body>
</html> 