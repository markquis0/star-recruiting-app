@extends('layouts.app')

@section('title', 'Login - Star Recruiting')

@section('content')
<div class="row justify-content-center mt-5 px-3 px-lg-5">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h4>Login</h4>
                <small class="text-muted">Login as Candidate or Recruiter</small>
            </div>
            <div class="card-body">
                <form id="login-form">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    
                    <div id="login-error" class="alert alert-danger" style="display: none;"></div>
                    <div id="login-success" class="alert alert-success" style="display: none;"></div>
                    
                    <button type="submit" class="btn btn-primary w-100" id="login-submit-btn">Login</button>
                </form>
                
                <div class="mt-3 text-center">
                    <a href="/register">Don't have an account? Register</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('login-form');
    
    if (!loginForm) {
        console.error('Login form not found');
        return;
    }
    
    loginForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // Disable submit button to prevent double submission
        const submitButton = this.querySelector('button[type="submit"]');
        if (submitButton) {
            submitButton.disabled = true;
            submitButton.textContent = 'Logging in...';
        }
        
        const formData = new FormData(this);
        const data = Object.fromEntries(formData);
        
        try {
            const response = await fetch('/api/login', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(data)
            });
            
            const result = await response.json();
            
            if (response.ok) {
                // Store token
                localStorage.setItem('api_token', result.token);
                localStorage.setItem('user_role', result.user.role);
                
                document.getElementById('login-error').style.display = 'none';
                document.getElementById('login-success').innerHTML = 'Login successful! Redirecting...';
                document.getElementById('login-success').style.display = 'block';
                
                // Redirect based on role
                setTimeout(() => {
                    if (result.user.role === 'candidate') {
                        window.location.href = '/candidate/home';
                    } else if (result.user.role === 'recruiter') {
                        window.location.href = '/recruiter/home';
                    } else {
                        // Unknown role, redirect to home
                        window.location.href = '/';
                    }
                }, 1500);
            } else {
                // Re-enable submit button on error
                if (submitButton) {
                    submitButton.disabled = false;
                    submitButton.textContent = 'Login';
                }
                
                document.getElementById('login-success').style.display = 'none';
                document.getElementById('login-error').innerHTML = result.message || 'Invalid credentials';
                document.getElementById('login-error').style.display = 'block';
            }
        } catch (error) {
            // Re-enable submit button on error
            if (submitButton) {
                submitButton.disabled = false;
                submitButton.textContent = 'Login';
            }
            
            document.getElementById('login-success').style.display = 'none';
            document.getElementById('login-error').innerHTML = 'An error occurred: ' + error.message;
            document.getElementById('login-error').style.display = 'block';
            console.error('Login error:', error);
        }
    });
});
</script>
@endsection

