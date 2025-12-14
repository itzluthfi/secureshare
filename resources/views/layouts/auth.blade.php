<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Auth') - SecureShare</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        :root {
            --primary-blue: #4F7FFF;
            --primary-blue-hover: #3D6FEF;
            --bg-dark: #1a1a2e;
            --bg-card: #16213e;
            --text-primary: #FFFFFF;
            --text-secondary: #B8B8B8;
            --border: #2d3748;
            --success: #10B981;
            --danger: #EF4444;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .auth-container {
            width: 100%;
            max-width: 450px;
            padding: 2rem;
        }
        
        .card {
            background: var(--bg-card);
            border-radius: 16px;
            padding: 2.5rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
            border: 1px solid var(--border);
        }
        
        .logo {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .logo i {
            font-size: 3rem;
            color: var(--primary-blue);
            margin-bottom: 0.5rem;
        }
        
        .logo h1 {
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
        }
        
        .logo p {
            color: var(--text-secondary);
            font-size: 0.9rem;
        }
        
        h2 {
            margin-bottom: 1.5rem;
            text-align: center;
            color: var(--primary-blue);
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--text-primary);
        }
        
        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 0.875rem;
            border: 1px solid var(--border);
            border-radius: 8px;
            background: var(--bg-dark);
            color: var(--text-primary);
            font-size: 0.95rem;
            transition: all 0.3s;
        }
        
        input:focus {
            outline: none;
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 3px rgba(79, 127, 255, 0.1);
        }
        
        input::placeholder {
            color: var(--text-secondary);
        }
        
        .btn {
            width: 100%;
            padding: 0.875rem;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: var(--primary-blue);
            color: white;
        }
        
        .btn-primary:hover:not(:disabled) {
            background: var(--primary-blue-hover);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(79, 127, 255, 0.4);
        }
        
        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        .text-center {
            text-align: center;
        }
        
        .mt-2 {
            margin-top: 1.5rem;
        }
        
        a {
            color: var(--primary-blue);
            text-decoration: none;
            transition: color 0.3s;
        }
        
        a:hover {
            color: var(--primary-blue-hover);
        }
        
        small {
            color: var(--text-secondary);
            font-size: 0.875rem;
        }
        
        /* Toast */
        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 1rem 1.5rem;
            border-radius: 8px;
            color: white;
            font-weight: 500;
            transform: translateX(400px);
            transition: transform 0.3s;
            z-index: 9999;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        }
        
        .toast.show {
            transform: translateX(0);
        }
        
        .toast.success {
            background: var(--success);
        }
        
        .toast.error {
            background: var(--danger);
        }
        
        .toast.info {
            background: var(--primary-blue);
        }
        
        @media (max-width: 480px) {
            .auth-container {
                padding: 1rem;
            }
            
            .card {
                padding: 1.5rem;
            }
        }
        
        @yield('styles')
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="card">
            <div class="logo">
                <i class="fas fa-shield-alt"></i>
                <h1>SecureShare</h1>
                <p>Secure Document Collaboration</p>
            </div>
            
            @yield('content')
        </div>
    </div>
    
    <!-- Toast -->
    <div id="toast" class="toast"></div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Setup AJAX
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json'
            }
        });
        
        // Toast notification
        function showToast(message, type = 'success') {
            const toast = $('#toast');
            toast.text(message);
            toast.removeClass('success error info').addClass(type);
            toast.addClass('show');
            
            setTimeout(() => {
                toast.removeClass('show');
            }, 3000);
        }
    </script>
    
    @stack('scripts')
</body>
</html>
