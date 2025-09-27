<!DOCTYPE html>
<html>
<head>
    <title>Test Page</title>
    <style>
        body { background-color: #f0f0f0; padding: 20px; }
        .test-box { background: white; padding: 20px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
    <div class="test-box">
        <h1>Test Halaman</h1>
        <p>Jika Anda melihat halaman ini, maka Laravel berfungsi dengan baik.</p>
        <p>Waktu: {{ now() }}</p>
    </div>
</body>
</html>
