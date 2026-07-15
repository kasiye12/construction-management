<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Construction Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #1a237e 0%, #283593 50%, #3949ab 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-container {
            width: 100%;
            max-width: 420px;
            padding: 20px;
        }
        .login-card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        .login-card .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-card .logo .icon { font-size: 3rem; display: block; margin-bottom: 10px; }
        .login-card .logo h3 { font-weight: 700; color: #1a237e; margin: 0; }
        .login-card .logo p { color: #666; font-size: 0.85rem; margin: 5px 0 0 0; }
        .login-card .form-control {
            border-radius: 10px; padding: 12px 15px;
            border: 1px solid #ddd; transition: all 0.3s ease;
        }
        .login-card .form-control:focus {
            border-color: #3949ab; box-shadow: 0 0 0 3px rgba(57, 73, 171, 0.1);
        }
        .login-card .btn-login {
            background: linear-gradient(135deg, #1a237e 0%, #3949ab 100%);
            border: none; border-radius: 10px; padding: 12px;
            font-weight: 600; font-size: 1rem; transition: all 0.3s ease;
        }
        .login-card .btn-login:hover {
            transform: translateY(-2px); box-shadow: 0 8px 25px rgba(26, 35, 126, 0.4);
        }
        .login-card .input-group-text {
            border-radius: 10px 0 0 10px; background: #f5f5f5; border: 1px solid #ddd;
        }
        .credentials-info {
            background: #f5f5f5; border-radius: 10px; padding: 15px;
            margin-top: 20px; font-size: 0.8rem;
        }
        .credentials-info strong { color: #1a237e; }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="logo">
                <span class="icon">🏗️</span>
                <h3>CMS Pro</h3>
                <p>Construction Management System</p>
            </div>
            
            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert" style="border-radius: 10px;">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    {{ $errors->first('email') ?: 'Invalid credentials. Please try again.' }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            
            <form action="{{ route('login.submit') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label fw-bold">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-envelope text-muted"></i></span>
                        <input type="email" name="email" class="form-control" 
                               placeholder="Enter your email" value="{{ old('email') }}" required autofocus>
                    </div>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-bold">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock text-muted"></i></span>
                        <input type="password" name="password" class="form-control" 
                               placeholder="Enter your password" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-login w-100 text-white">
                    <i class="fas fa-sign-in-alt me-2"></i>Sign In
                </button>
            </form>
            
            <div class="credentials-info">
                <strong>🔑 Demo Credentials:</strong><br>
                <table class="table table-sm table-borderless mb-0 mt-2" style="font-size: 0.75rem;">
                    <tr><td><strong>Admin</strong></td><td>admin@cms.com</td></tr>
                    <tr><td><strong>Manager</strong></td><td>manager@cms.com</td></tr>
                    <tr><td><strong>Engineer</strong></td><td>engineer@cms.com</td></tr>
                    <tr><td colspan="2" class="text-muted">Password: <strong>password</strong></td></tr>
                </table>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
