<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - SecureShare</title>
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
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--bg-dark);
            min-height: 100vh;
            overflow: hidden;
        }
        
        .auth-container {
            display: flex;
            height: 100vh;
        }
        
        /* Left Side - Carousel */
        .auth-carousel {
            flex: 1;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            position: relative;
            overflow: hidden;
        }
        
        .carousel-slides {
            position: relative;
            width: 100%;
            height: 100%;
        }
        
        .carousel-slide {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            transition: opacity 1s ease-in-out;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            padding: 3rem;
            text-align: center;
            background-size: cover;
            background-position: center;
        }
        
        .carousel-slide.active {
            opacity: 1;
        }
        
        .carousel-slide::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.4);
        }
        
        .carousel-content {
            position: relative;
            z-index: 1;
            color: white;
        }
        
        .carousel-content i {
            font-size: 4rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }
        
        .carousel-content h2 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            font-weight: 700;
        }
        
        .carousel-content p {
            font-size: 1.2rem;
            opacity: 0.9;
            max-width: 500px;
        }
        
        .carousel-dots {
            position: absolute;
            bottom: 2rem;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 0.5rem;
            z-index: 2;
        }
        
        .carousel-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.5);
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .carousel-dot.active {
            background: white;
            width: 30px;
            border-radius: 5px;
        }
        
        /* Right Side - Auth Form */
        .auth-form-container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            background: var(--bg-dark);
        }
        
        .auth-card {
            background: var(--bg-card);
            border-radius: 20px;
            padding: 3rem;
            width: 100%;
            max-width: 450px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }
        
        .auth-tabs {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            background: rgba(255, 255, 255, 0.05);
            padding: 0.5rem;
            border-radius: 12px;
        }
        
        .auth-tab {
            flex: 1;
            padding: 0.8rem;
            border: none;
            background: transparent;
            color: var(--text-secondary);
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            border-radius: 8px;
            transition: all 0.3s;
        }
        
        .auth-tab.active {
            background: var(--primary-blue);
            color: white;
        }
        
        .auth-logo {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .auth-logo i {
            font-size: 3rem;
            color: var(--primary-blue);
            margin-bottom: 1rem;
        }
        
        .auth-logo h1 {
            color: var(--text-primary);
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
        }
        
        .auth-logo p {
            color: var(--text-secondary);
            font-size: 0.95rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            color: var(--text-secondary);
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
            font-weight: 500;
        }
        
        .form-input {
            width: 100%;
            padding: 0.9rem 1rem;
            background: rgba(255, 255, 255, 0.05);
            border: 2px solid var(--border);
            border-radius: 12px;
            color: var(--text-primary);
            font-size: 1rem;
            transition: all 0.3s;
        }
        
        .form-input:focus {
            outline: none;
            border-color: var(--primary-blue);
            background: rgba(255, 255, 255, 0.08);
            box-shadow: 0 0 0 4px rgba(79, 127, 255, 0.1);
        }
        
        .form-input::placeholder {
            color: var(--text-muted);
        }
        
        .btn-primary {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--primary-blue-hover) 100%);
            border: none;
            border-radius: 12px;
            color: white;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 0.5rem;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(79, 127, 255, 0.3);
        }
        
        .btn-primary:active {
            transform: translateY(0);
        }
        
        .form-footer {
            margin-top: 1.5rem;
            text-align: center;
        }
        
        .form-footer a {
            color: var(--primary-blue);
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.3s;
        }
        
        .form-footer a:hover {
            color: var(--primary-blue-hover);
            text-decoration: underline;
        }
        
        .divider {
            display: flex;
            align-items: center;
            margin: 1.5rem 0;
            color: var(--text-secondary);
            font-size: 0.85rem;
        }
        
        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border);
        }
        
        .divider span {
            padding: 0 1rem;
        }
        
        @media (max-width: 968px) {
            .auth-carousel {
                display: none;
            }
            
            .auth-form-container {
                flex: 1;
            }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <!-- Left Side - Image Carousel -->
        <div class="auth-carousel">
            <div class="carousel-slides">
                <div class="carousel-slide active" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <div class="carousel-content">
                        <i class="fas fa-shield-alt"></i>
                        <h2>Secure Collaboration</h2>
                        <p>End-to-end encrypted document sharing with military-grade AES-256 encryption</p>
                    </div>
                </div>
                <div class="carousel-slide" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                    <div class="carousel-content">
                        <i class="fas fa-users"></i>
                        <h2>Team Productivity</h2>
                        <p>Collaborate seamlessly with your team on projects and documents in real-time</p>
                    </div>
                </div>
                <div class="carousel-slide" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                    <div class="carousel-content">
                        <i class="fas fa-history"></i>
                        <h2>Version Control</h2>
                        <p>Track every change with complete version history and audit trails</p>
                    </div>
                </div>
            </div>
            <div class="carousel-dots">
                <div class="carousel-dot active" data-slide="0"></div>
                <div class="carousel-dot" data-slide="1"></div>
                <div class="carousel-dot" data-slide="2"></div>
            </div>
        </div>
        
        <!-- Right Side - Auth Form -->
        <div class="auth-form-container">
            <div class="auth-card">
                <div class="auth-logo">
                    <i class="fas fa-lock"></i>
                    <h1>SecureShare</h1>
                    <p>Secure Document Collaboration Platform</p>
                </div>
                
                <div class="auth-tabs">
                    <button class="auth-tab active" data-tab="login">Login</button>
                    <button class="auth-tab" data-tab="register">Sign Up</button>
                </div>
                
                <!-- Login Form -->
                <form id="loginForm" class="auth-form">
                    <div class="form-group">
                        <label for="login-email">Email Address</label>
                        <input type="email" id="login-email" class="form-input" placeholder="you@example.com" required>
                    </div>
                    <div class="form-group">
                        <label for="login-password">Password</label>
                        <input type="password" id="login-password" class="form-input" placeholder="••••••••" required>
                    </div>
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-sign-in-alt"></i> Sign In
                    </button>
                    <div class="form-footer">
                        <a href="{{ route('password.request') }}">Forgot your password?</a>
                    </div>
                </form>
                
                <!-- Register Form -->
                <form id="registerForm" class="auth-form" style="display: none;">
                    <div class="form-group">
                        <label for="register-name">Full Name</label>
                        <input type="text" id="register-name" class="form-input" placeholder="John Doe" required>
                    </div>
                    <div class="form-group">
                        <label for="register-email">Email Address</label>
                        <input type="email" id="register-email" class="form-input" placeholder="you@example.com" required>
                    </div>
                    <div class="form-group">
                        <label for="register-password">Password</label>
                        <input type="password" id="register-password" class="form-input" placeholder="••••••••" required>
                    </div>
                    <div class="form-group">
                        <label for="register-password-confirm">Confirm Password</label>
                        <input type="password" id="register-password-confirm" class="form-input" placeholder="••••••••" required>
                    </div>
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-user-plus"></i> Create Account
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Check if already logged in - redirect to dashboard
        const existingToken = localStorage.getItem('token');
        if (existingToken) {
            console.log('Already logged in, redirecting to dashboard...');
            window.location.href = '/dashboard';
        }
        
        // Carousel
        let currentSlide = 0;
        const slides = document.querySelectorAll('.carousel-slide');
        const dots = document.querySelectorAll('.carousel-dot');
        
        function showSlide(index) {
            slides.forEach(slide => slide.classList.remove('active'));
            dots.forEach(dot => dot.classList.remove('active'));
            slides[index].classList.add('active');
            dots[index].classList.add('active');
        }
        
        function nextSlide() {
            currentSlide = (currentSlide + 1) % slides.length;
            showSlide(currentSlide);
        }
        
        // Auto rotate every 5 seconds
        setInterval(nextSlide, 5000);
        
        // Dot click
        dots.forEach((dot, index) => {
            dot.addEventListener('click', () => {
                currentSlide = index;
                showSlide(currentSlide);
            });
        });
        
        // Tab switching
        $('.auth-tab').click(function() {
            const tab = $(this).data('tab');
            $('.auth-tab').removeClass('active');
            $(this).addClass('active');
            
            if (tab === 'login') {
                $('#loginForm').show();
                $('#registerForm').hide();
            } else {
                $('#loginForm').hide();
                $('#registerForm').show();
            }
        });
        
        // Toast notification
        function showToast(message, type = 'success') {
            // Simple toast implementation
            alert(message);
        }
        
        // Login form
        $('#loginForm').submit(function(e) {
            e.preventDefault();
            console.log('=== LOGIN FORM SUBMITTED ===');
            
            const email = $('#login-email').val();
            const password = $('#login-password').val();
            
            console.log('Email:', email);
            console.log('Password length:', password.length);
            
            $.ajax({
                url: '/api/v1/auth/login',
                method: 'POST',
                data: { email, password },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Accept': 'application/json'
                }
            })
            .done(function(response) {
                console.log('=== LOGIN SUCCESS ===', response);
                if (response.access_token) {
                    localStorage.setItem('token', response.access_token);
                    localStorage.setItem('user', JSON.stringify(response.user));
                    window.location.href = '/dashboard';
                }
            })
            .fail(function(xhr) {
                console.error('=== LOGIN FAILED ===', xhr);
                alert(xhr.responseJSON?.message || 'Login failed');
            });
        });
        
        // Register form
        $('#registerForm').submit(function(e) {
            e.preventDefault();
            
            const name = $('#register-name').val();
            const email = $('#register-email').val();
            const password = $('#register-password').val();
            const password_confirmation = $('#register-password-confirm').val();
            
            if (password !== password_confirmation) {
                alert('Passwords do not match!');
                return;
            }
            
            $.ajax({
                url: '/api/v1/auth/register',
                method: 'POST',
                data: { name, email, password, password_confirmation },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Accept': 'application/json'
                }
            })
            .done(function(response) {
                console.log('Registration success:', response);
                if (response.access_token) {
                    localStorage.setItem('token', response.access_token);
                    localStorage.setItem('user', JSON.stringify(response.user));
                    window.location.href = '/dashboard';
                }
            })
            .fail(function(xhr) {
                console.error('Registration failed:', xhr);
                const errors = xhr.responseJSON?.errors;
                if (errors) {
                    const errorMsg = Object.values(errors).flat().join('\n');
                    alert(errorMsg);
                } else {
                    alert(xhr.responseJSON?.message || 'Registration failed');
                }
            });
        });
    </script>
</body>
</html>
