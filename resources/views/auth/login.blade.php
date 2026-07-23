<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CMS Pro | Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family: 'Inter', -apple-system, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: url('https://images.unsplash.com/photo-1541888946425-d81bb19240f5?w=1920&q=80') center/cover no-repeat fixed;
            position: relative;
        }
        body::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(15,23,42,0.90) 0%, rgba(30,41,59,0.85) 50%, rgba(51,65,85,0.80) 100%);
            z-index: 0;
        }
        .login-wrapper {
            display: flex;
            background: #fff;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 25px 60px rgba(0,0,0,0.4);
            max-width: 960px;
            width: 100%;
            min-height: 550px;
            position: relative;
            z-index: 1;
        }
        .login-left {
            background: linear-gradient(135deg, rgba(26,35,126,0.92), rgba(13,21,66,0.95)), url('https://images.unsplash.com/photo-1504917595217-d4dc5ebe6122?w=800&q=80') center/cover no-repeat;
            color: #fff;
            padding: 50px 40px;
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        .login-left::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle, rgba(255,255,255,0.08) 0%, transparent 70%);
        }
        .login-left .brand { position: relative; z-index: 1; }
        .login-left .brand-icon {
            width: 60px; height: 60px;
            background: rgba(255,255,255,0.15);
            border-radius: 16px;
            display: flex; align-items: center; justify-content: center;
            font-size: 28px; margin-bottom: 20px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
        }
        .login-left h2 { font-size: 26px; font-weight: 700; margin-bottom: 6px; text-shadow: 0 2px 10px rgba(0,0,0,0.3); }
        .login-left p { font-size: 14px; opacity: 0.9; line-height: 1.6; text-shadow: 0 1px 5px rgba(0,0,0,0.3); }
        .login-left .features { margin-top: 30px; position: relative; z-index: 1; }
        .login-left .features .feat {
            display: flex; align-items: center; gap: 10px;
            padding: 6px 0; font-size: 13px; opacity: 0.9;
            text-shadow: 0 1px 4px rgba(0,0,0,0.3);
        }
        .login-left .features .feat i { width: 20px; color: #4ade80; font-size: 12px; }
        
        .login-right {
            padding: 50px 45px; flex: 1;
            display: flex; flex-direction: column; justify-content: center;
        }
        .login-right .welcome { margin-bottom: 30px; }
        .login-right .welcome h3 { font-size: 24px; font-weight: 700; color: #0f172a; margin-bottom: 4px; }
        .login-right .welcome p { font-size: 14px; color: #64748b; }
        
        .form-group { margin-bottom: 20px; }
        .form-group label {
            display: block; font-size: 12px; font-weight: 600;
            color: #475569; margin-bottom: 6px;
            text-transform: uppercase; letter-spacing: 0.5px;
        }
        .form-group .input-wrap { position: relative; }
        .form-group .input-wrap i {
            position: absolute; left: 16px; top: 50%;
            transform: translateY(-50%); color: #94a3b8; font-size: 15px;
        }
        .form-group input {
            width: 100%; padding: 13px 16px 13px 45px;
            border: 2px solid #e2e8f0; border-radius: 10px;
            font-size: 14px; font-family: 'Inter', sans-serif;
            transition: all 0.2s; background: #f8fafc;
        }
        .form-group input:focus {
            outline: none; border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79,70,229,0.1); background: #fff;
        }
        .form-group input::placeholder { color: #94a3b8; }
        
        .btn-login {
            width: 100%; padding: 14px; background: #4f46e5; color: #fff;
            border: none; border-radius: 10px; font-size: 15px;
            font-weight: 600; cursor: pointer; font-family: 'Inter', sans-serif;
            transition: all 0.2s; margin-top: 8px;
            display: flex; align-items: center; justify-content: center; gap: 8px;
        }
        .btn-login:hover {
            background: #4338ca; transform: translateY(-1px);
            box-shadow: 0 8px 20px rgba(79,70,229,0.3);
        }
        
        .error-box {
            background: #fef2f2; color: #dc2626;
            padding: 12px 16px; border-radius: 10px;
            border: 1px solid #fecaca; margin-bottom: 20px;
            font-size: 13px; font-weight: 500;
            display: flex; align-items: center; gap: 8px;
        }
        
        .login-footer {
            text-align: center; margin-top: 24px;
            font-size: 11px; color: #94a3b8;
        }
        
        @media (max-width: 768px) {
            .login-wrapper { flex-direction: column; max-width: 420px; }
            .login-left { padding: 30px 24px; }
            .login-left .features { display: none; }
            .login-right { padding: 30px 24px; }
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <!-- Left Side - Brand with Construction Photo -->
        <div class="login-left">
            <div class="brand">
                <div class="brand-icon">🏗️</div>
                <h2>TNT Construction And Trading</h2>
                <p>Construction Management System</p>
            </div>
            <div class="features">
                <div class="feat"><i class="fas fa-check-circle"></i> Project & BOQ Management</div>
                <div class="feat"><i class="fas fa-check-circle"></i> Quantity Takeoff Sheets</div>
                <div class="feat"><i class="fas fa-check-circle"></i> IPC Payment Certificates</div>
                <div class="feat"><i class="fas fa-check-circle"></i> Gantt Chart & Timelines</div>
                <div class="feat"><i class="fas fa-check-circle"></i> Workflow Approvals</div>
            </div>
        </div>
        
        <!-- Right Side - Login Form -->
        <div class="login-right">
            <div class="welcome">
                <h3>Welcome Back</h3>
                <p>Sign in to access your dashboard</p>
            </div>
            
            @if($errors->any())
                <div class="error-box">
                    <i class="fas fa-exclamation-circle"></i>
                    {{ $errors->first() }}
                </div>
            @endif
            
            <form method="POST" action="{{ route('login.submit') }}">
                @csrf
                <div class="form-group">
                    <label>Email Address</label>
                    <div class="input-wrap">
                        <i class="fas fa-envelope"></i>
                        <input type="email" name="email" value="{{ old('email') }}" placeholder="Enter your email" required autofocus>
                    </div>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <div class="input-wrap">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="password" placeholder="Enter your password" required>
                    </div>
                </div>
                <button type="submit" class="btn-login">
                    <i class="fas fa-sign-in-alt"></i> Sign In
                </button>
            </form>
            
            <div class="login-footer">
                CMS Pro &copy; {{ date('Y') }} | TNT Construction & Trading
            </div>
        </div>
    </div>
</body>
</html>
