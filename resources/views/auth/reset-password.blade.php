@extends('layouts.app')

@section('title', 'Reset Password')

@section('content')
<div style="max-width: 400px; margin: 4rem auto;">
    <div class="card">
        <h2 style="margin-bottom: 1rem; text-align: center; color: var(--primary);">Set New Password</h2>
        <p style="text-align: center; color: #6B7280; margin-bottom: 1.5rem;">
            Masukkan password baru Anda.
        </p>
        
        <form id="resetPasswordForm">
            <input type="hidden" id="token" value="{{ $token }}">
            <input type="hidden" id="email" value="{{ $email }}">
            
            <div style="margin-bottom: 1rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Email</label>
                <input type="email" value="{{ $email }}" disabled style="width: 100%; padding: 0.7rem; border: 1px solid var(--border); border-radius: 6px; background: var(--light);">
            </div>
            
            <div style="margin-bottom: 1rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">New Password</label>
                <input type="password" id="password" required minlength="8" placeholder="Minimal 8 karakter" style="width: 100%; padding: 0.7rem; border: 1px solid var(--border); border-radius: 6px;">
                <small style="color: #6B7280;">Minimal 8 karakter</small>
            </div>
            
            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Confirm Password</label>
                <input type="password" id="password_confirmation" required minlength="8" placeholder="Ulangi password" style="width: 100%; padding: 0.7rem; border: 1px solid var(--border); border-radius: 6px;">
            </div>
            
            <button type="submit" class="btn btn-primary" id="submitBtn" style="width: 100%;">Reset Password</button>
        </form>
        
        <div style="margin-top: 1.5rem; text-align: center;">
            <a href="{{ route('login') }}" style="color: var(--primary); text-decoration: none;">← Back to Login</a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
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
            showToast('Password reset successful! Redirecting to login...', 'success');
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
@endpush
