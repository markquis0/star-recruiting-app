@extends('layouts.app')

@section('title', 'Register - Star Recruiting')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h4>Register</h4>
            </div>
            <div class="card-body">
                <form id="register-form">
                    <div class="mb-3">
                        <label for="role" class="form-label">I am a:</label>
                        <select class="form-select" id="role" name="role" required>
                            <option value="">Select...</option>
                            <option value="candidate">Candidate</option>
                            <option value="recruiter">Recruiter</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required minlength="6">
                    </div>
                    
                    <div class="mb-3">
                        <label for="first_name" class="form-label">First Name</label>
                        <input type="text" class="form-control" id="first_name" name="first_name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="last_name" class="form-label">Last Name</label>
                        <input type="text" class="form-control" id="last_name" name="last_name" required>
                    </div>
                    
                    <!-- Candidate fields -->
                    <div id="candidate-fields" style="display: none;">
                        <div class="mb-3">
                            <label for="role_title" class="form-label">Role Title</label>
                            <input type="text" class="form-control" id="role_title" name="role_title" placeholder="e.g., Software Engineer">
                        </div>
                        <div class="mb-3">
                            <label for="years_exp" class="form-label">Years of Experience</label>
                            <input type="number" class="form-control" id="years_exp" name="years_exp" min="0">
                        </div>
                    </div>
                    
                    <!-- Recruiter fields -->
                    <div id="recruiter-fields" style="display: none;">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="company_name" class="form-label">Company Name</label>
                            <input type="text" class="form-control" id="company_name" name="company_name">
                        </div>
                    </div>
                    
                    <div id="register-error" class="alert alert-danger" style="display: none;"></div>
                    <div id="register-success" class="alert alert-success" style="display: none;"></div>
                    
                    <button type="submit" class="btn btn-primary w-100">Register</button>
                </form>
                
                <div class="mt-3 text-center">
                    <a href="/login">Already have an account? Login</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('role').addEventListener('change', function() {
    const role = this.value;
    const candidateFields = document.getElementById('candidate-fields');
    const recruiterFields = document.getElementById('recruiter-fields');
    const emailField = document.getElementById('email');
    
    if (role === 'candidate') {
        candidateFields.style.display = 'block';
        recruiterFields.style.display = 'none';
        emailField.removeAttribute('required');
    } else if (role === 'recruiter') {
        candidateFields.style.display = 'none';
        recruiterFields.style.display = 'block';
        emailField.setAttribute('required', 'required');
    } else {
        candidateFields.style.display = 'none';
        recruiterFields.style.display = 'none';
        emailField.removeAttribute('required');
    }
});

document.getElementById('register-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    // Remove empty fields based on role
    const role = document.getElementById('role').value;
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    
    // Clean up data based on role - remove empty strings and irrelevant fields
    if (role === 'candidate') {
        delete data.email;
        delete data.company_name;
        // Remove empty strings
        if (data.role_title === '') delete data.role_title;
        if (data.years_exp === '') delete data.years_exp;
    } else if (role === 'recruiter') {
        delete data.role_title;
        delete data.years_exp;
        // Remove empty strings
        if (data.company_name === '') delete data.company_name;
    }
    
    // Remove any empty string values
    Object.keys(data).forEach(key => {
        if (data[key] === '') {
            delete data[key];
        }
    });
    
    try {
        const response = await fetch('/api/register', {
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
            
            document.getElementById('register-error').style.display = 'none';
            document.getElementById('register-success').innerHTML = 'Registration successful! Redirecting...';
            document.getElementById('register-success').style.display = 'block';
            
            // Redirect based on role
            setTimeout(() => {
                if (result.user.role === 'candidate') {
                    window.location.href = '/candidate/home';
                } else {
                    window.location.href = '/recruiter/home';
                }
            }, 1500);
        } else {
            document.getElementById('register-success').style.display = 'none';
            
            // Format validation errors nicely
            let errorMessage = result.message || '';
            if (result.errors) {
                const errorList = Object.entries(result.errors).map(([field, messages]) => {
                    return `<strong>${field}:</strong> ${Array.isArray(messages) ? messages.join(', ') : messages}`;
                }).join('<br>');
                errorMessage = errorList || errorMessage;
            } else if (typeof result === 'string') {
                errorMessage = result;
            } else if (typeof result === 'object') {
                errorMessage = JSON.stringify(result);
            }
            
            document.getElementById('register-error').innerHTML = errorMessage;
            document.getElementById('register-error').style.display = 'block';
        }
    } catch (error) {
        document.getElementById('register-success').style.display = 'none';
        document.getElementById('register-error').innerHTML = 'An error occurred: ' + error.message;
        document.getElementById('register-error').style.display = 'block';
    }
});
</script>
@endsection

