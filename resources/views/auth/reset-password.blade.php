<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Reset Password - SecureShare</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        :root {
            --primary-blue: #4F7FFF;
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
            background: var(--bg-dark);
            color: var(--text-primary);
            min-height: 100vh;
            overflow-x: hidden;
        }
        
        /* Background Carousel */
        .bg-carousel {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
        }
        
        .carousel-slide {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-size: cover;
            background-position: center;
            opacity: 0;
            transition: opacity 1.5s ease-in-out;
        }
        
        .carousel-slide.active {
            opacity: 1;
        }
        
        .carousel-slide::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1;
        }
        
        /* Content Container */
        .content-wrapper {
            position: relative;
            z-index: 10;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        
        .card {
            background: rgba(22, 33, 62, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 3rem;
            width: 100%;
            max-width: 450px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .logo-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .logo-header i {
            font-size: 3rem;
            color: var(--primary-blue);
            margin-bottom: 1rem;
        }
        
        .logo-header h1 {
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
        }
        
        .logo-header p {
            color: var(--text-secondary);
        }
        
        h2 {
            text-align: center;
            color: var(--primary-blue);
            margin-bottom: 1rem;
            font-size: 1.5rem;
        }
        
        .subtitle {
            text-align: center;
            color: var(--text-secondary);
            margin-bottom: 2rem;
            font-size: 0.95rem;
        }
        
        input {
            width: 100%;
            padding: 0.9rem;
            background: rgba(255, 255, 255, 0.05);
            border: 2px solid var(--border);
            border-radius: 12px;
            color: var(--text-primary);
            font-size: 1rem;
            transition: all 0.3s;
        }
        
        input:focus {
            outline: none;
            border-color: var(--primary-blue);
            background: rgba(255, 255, 255, 0.08);
            box-shadow: 0 0 0 4px rgba(79, 127, 255, 0.1);
        }
        
        input::placeholder {
            color: rgba(184, 184, 184, 0.5);
        }
        
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--text-secondary);
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        small {
            color: var(--text-secondary);
            font-size: 0.85rem;
        }
        
        button {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, var(--primary-blue) 0%, #3D6FEF 100%);
            border: none;
            border-radius: 12px;
            color: white;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        button:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(79, 127, 255, 0.4);
        }
        
        button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        .back-link {
            display: block;
            text-align: center;
            margin-top: 1.5rem;
            color: var(--primary-blue);
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .back-link:hover {
            color: #3D6FEF;
        }
        
        /* Toast */
        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 1rem 1.5rem;
            border-radius: 12px;
            color: white;
            font-weight: 500;
            transform: translateX(400px);
            transition: transform 0.3s;
            z-index: 9999;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
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
        
        @media (max-width: 640px) {
            .content-wrapper {
                padding: 1rem;
            }
            
            .card {
                padding: 2rem 1.5rem;
            }
            
            .logo-header h1 {
                font-size: 1.5rem;
            }
            
            h2 {
                font-size: 1.25rem;
            }
        }
    </style>
</head>
<body>
    <!-- Background Carousel -->
    <div class="bg-carousel">
        <div class="carousel-slide active" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);"></div>
        <div class="carousel-slide" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);"></div>
        <div class="carousel-slide" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);"></div>
    </div>
    
    <!-- Content -->
    <div class="content-wrapper">
        <div class="card">
            <div class="logo-header">
                <i class="fas fa-shield-alt"></i>
                <h1>SecureShare</h1>
                <p>Secure Document Collaboration</p>
            </div>
            
            <h2>Set New Password</h2>
            <p class="subtitle">Enter your new password below</p>
            
            <form id="resetPasswordForm">
                <input type="hidden" id="token" value="{{ $token }}">
                <input type="hidden" id="email" value="{{ $email }}">
                
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" value="{{ $email }}" disabled>
                </div>
                
                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" id="password" required minlength="8" placeholder="Minimal 8 characters">
                    <small>Minimum 8 characters</small>
                </div>
                
                <div class="form-group">
                    <label>Confirm Password</label>
                    <input type="password" id="password_confirmation" required minlength="8" placeholder="Re-enter password">
                </div>
                
                <button type="submit" id="submitBtn">Reset Password</button>
            </form>
            
            <a href="{{ route('login') }}" class="back-link">
                <i class="fas fa-arrow-left"></i> Back to Login
            </a>
        </div>
    </div>
    
    <!-- Toast -->
    <div id="toast" class="toast"></div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    // Background carousel
    let currentSlide = 0;
    const slides = document.querySelectorAll('.carousel-slide');
    
    function nextSlide() {
        slides[currentSlide].classList.remove('active');
        currentSlide = (currentSlide + 1) % slides.length;
        slides[currentSlide].classList.add('active');
    }
    
    setInterval(nextSlide, 5000);
    
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
        toast.removeClass('success error').addClass(type);
        toast.addClass('show');
        
        setTimeout(() => {
            toast.removeClass('show');
        }, 3000);
    }
    
    // Form submission
    $('#resetPasswordForm').submit(function(e) {
        e.preventDefault();
        
        const password = $('#password').val();
        const passwordConfirmation = $('#password_confirmation').val();
        
        // Validate passwords match
        if (password !== passwordConfirmation) {
            showToast('Passwords do not match!', 'error');
            return;
        }
        
        const data = {
            token: $('#token').val(),
            email: $('#email').val(),
            password: password,
            password_confirmation: passwordConfirmation
        };
        
        const submitBtn = $('#submitBtn');
        submitBtn.prop('disabled', true).text('Resetting...');
        
        $.post('/api/v1/auth/reset-password', data)
            .done(response => {
                showToast('Password reset successful! Redirecting...', 'success');
                setTimeout(() => {
                    window.location.href = '{{ route("login") }}';
                }, 2000);
            })
            .fail(error => {
                submitBtn.prop('disabled', false).text('Reset Password');
                const message = error.responseJSON?.message || 'Failed to reset password. The link may have expired.';
                showToast(message, 'error');
            });
    });
    </script>
</body>
</html>
