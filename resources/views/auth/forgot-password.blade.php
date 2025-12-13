@extends('layouts.app')

@section('title', 'Forgot Password')

@section('content')
<div style="max-width: 400px; margin: 4rem auto;">
    <div class="card">
        <h2 style="margin-bottom: 1rem; text-align: center; color: var(--primary);">Reset Password</h2>
        <p style="text-align: center; color: #6B7280; margin-bottom: 1.5rem;">
            Masukkan email Anda dan kami akan mengirimkan link untuk reset password.
        </p>
        
        <form id="forgotPasswordForm">
            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Email Address</label>
                <input type="email" id="email" required placeholder="email@example.com" style="width: 100%; padding: 0.7rem; border: 1px solid var(--border); border-radius: 6px;">
            </div>
            
            <button type="submit" class="btn btn-primary" id="submitBtn" style="width: 100%;">Send Reset Link</button>
        </form>
        
        <div id="successMessage" style="display: none; margin-top: 1rem; padding: 1rem; background: #D1FAE5; border-left: 4px solid var(--secondary); border-radius: 6px;">
            <strong style="color: var(--secondary);">✓ Email Sent!</strong>
            <p style="color: #065F46; margin: 0.5rem 0 0 0; font-size: 0.9rem;">
                Silakan cek email Anda untuk link reset password. Jika tidak ada di inbox, periksa folder spam.
            </p>
        </div>
        
        <div style="margin-top: 1.5rem; text-align: center;">
            <a href="{{ route('login') }}" style="color: var(--primary); text-decoration: none;">← Back to Login</a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$('#forgotPasswordForm').submit(function(e) {
    e.preventDefault();
    
    const email = $('#email').val();
    const submitBtn = $('#submitBtn');
    
    // Disable button
    submitBtn.prop('disabled', true).text('Sending...');
    
    $.post('/api/v1/auth/forgot-password', { email })
        .done(response => {
            // Show success message
            $('#successMessage').slideDown();
            $('#forgotPasswordForm').slideUp();
            showToast('Reset link sent to your email!', 'success');
        })
        .fail(error => {
            submitBtn.prop('disabled', false).text('Send Reset Link');
            const message = error.responseJSON?.message || 'Failed to send reset link. Please try again.';
            showToast(message, 'error');
        });
});
</script>
@endpush
