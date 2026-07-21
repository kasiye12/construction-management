<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - Server Error</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #1a237e 0%, #283593 50%, #3949ab 100%);
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .error-card {
            background: white; border-radius: 20px; padding: 50px 40px;
            text-align: center; max-width: 500px; box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        .error-code { font-size: 100px; font-weight: 800; color: #dc3545; line-height: 1; margin-bottom: 10px; }
        .error-icon { font-size: 60px; color: #f59e0b; margin-bottom: 15px; }
        .error-title { font-size: 24px; font-weight: 700; color: #1a237e; margin-bottom: 10px; }
        .error-message { font-size: 16px; color: #666; margin-bottom: 25px; }
        .btn-back {
            background: #4f46e5; color: white; border: none; padding: 12px 30px;
            border-radius: 10px; font-weight: 600; font-size: 14px; text-decoration: none;
            transition: all 0.3s; display: inline-block;
        }
        .btn-back:hover { background: #3730a3; color: white; transform: translateY(-2px); }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="error-icon"><i class="fas fa-exclamation-triangle"></i></div>
        <div class="error-code">500</div>
        <div class="error-title">Server Error</div>
        <div class="error-message">
            Something went wrong on our end.<br>
            Please try again later or contact support.
        </div>
        <a href="{{ url('/') }}" class="btn-back">
            <i class="fas fa-home me-2"></i> Back to Dashboard
        </a>
    </div>
</body>
</html>
