<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Forgot Password - SecureShare</title>
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
            overflow: hidden;
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
            font-size: 0.9rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .btn {
            padding: 1rem;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 1rem;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-blue), #3D6FEF);
            color: white;
            width: 100%;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(79, 127, 255, 0.3);
        }
        
        .btn-primary:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        a {
            color: var(--primary-blue);
            text-decoration: none;
            transition: color 0.3s;
        }
        
        a:hover {
            color: #3D6FEF;
            text-decoration: underline;
        }
        
        .back-link {
            margin-top: 1.5rem;
            text-align: center;
        }
        
        .success-message {
            display: none;
            margin-top: 1rem;
            padding: 1rem;
            background: rgba(16, 185, 129, 0.15);
            border-left: 4px solid var(--success);
            border-radius: 8px;
        }
        
        .success-message strong {
            color: var(--success);
            display: block;
            margin-bottom: 0.5rem;
        }
        
        .success-message p {
            color: var(--text-secondary);
            font-size: 0.9rem;
            margin: 0;
        }
        
        /* Carousel dots */
        .carousel-dots {
            position: fixed;
            bottom: 2rem;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 0.5rem;
            z-index: 5;
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
    </style>
</head>
<body>
    <!-- Background Carousel -->
    <div class="bg-carousel">
        <div class="carousel-slide active" 
             style="background: linear-gradient(135deg, rgba(102,126,234,0.7), rgba(118,75,162,0.7)), 
                    url('{{ asset('images/auth/bg-login1.jpg') }}');">
        </div>
        <div class="carousel-slide" 
             style="background: linear-gradient(135deg, rgba(240,147,251,0.7), rgba(245,87,108,0.7)), 
                    url('{{ asset('images/auth/bg-login2.webp') }}');">
        </div>
        <div class="carousel-slide" 
             style="background: linear-gradient(135deg, rgba(79,172,254,0.7), rgba(0,242,254,0.7)), 
                    url('{{ asset('images/auth/bg-login3.jpg') }}');">
        </div>
    </div>
    
    <!-- Carousel Dots -->
    <div class="carousel-dots">
        <div class="carousel-dot active" data-slide="0"></div>
        <div class="carousel-dot" data-slide="1"></div>
        <div class="carousel-dot" data-slide="2"></div>
    </div>
    
    <!-- Content -->
    <div class="content-wrapper">
        <div class="card">
            <div class="logo-header">
                <i class="fas fa-lock"></i>
                <h1>SecureShare</h1>
                <p>Secure Document Collaboration</p>
            </div>
            
            <h2><i class="fas fa-key"></i> Reset Password</h2>
            <p class="subtitle">
                Masukkan email Anda dan kami akan mengirimkan link untuk reset password.
            </p>
            
            <form id="forgotPasswordForm">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" required placeholder="Masukkan email (e.g. user@example.com)">
                </div>
                
                <button type="submit" class="btn btn-primary" id="submitBtn">
                    <i class="fas fa-paper-plane"></i> Send Reset Link
                </button>
            </form>
            
            <div id="successMessage" class="success-message">
                <strong><i class="fas fa-check-circle"></i> Email Sent!</strong>
                <p>
                    Silakan cek email Anda untuk link reset password. Jika tidak ada di inbox, periksa folder spam.
                </p>
            </div>
            
            <div class="back-link">
                <a href="{{ route('login') }}">
                    <i class="fas fa-arrow-left"></i> Back to Login
                </a>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
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
    
    // Form submission
    $('#forgotPasswordForm').submit(function(e) {
        e.preventDefault();
        
        const email = $('#email').val();
        const submitBtn = $('#submitBtn');
        
        // Disable button
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Sending...');
        
        $.ajax({
            url: '/api/v1/auth/forgot-password',
            method: 'POST',
            data: { email },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json'
            }
        })
        .done(response => {
            // Show success message
            $('#successMessage').slideDown();
            $('#forgotPasswordForm').slideUp();
        })
        .fail(error => {
            submitBtn.prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Send Reset Link');
            const message = error.responseJSON?.message || 'Failed to send reset link. Please try again.';
            alert(message);
        });
    });
    </script>
</body>
</html>
