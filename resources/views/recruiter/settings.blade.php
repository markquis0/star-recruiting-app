@extends('layouts.app')

@section('title', 'Settings - Recruiter')

@section('content')
<div class="row px-3 px-lg-5 mt-4 mt-lg-5">
    <div class="col-12">
        <h2>Settings</h2>
        <div class="card">
            <div class="card-header">
                <h5>Update Your Profile</h5>
            </div>
            <div class="card-body">
                <form id="settings-form">
                    <h6 class="mb-3">Account Information</h6>
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">New Password</label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Leave blank to keep current password" minlength="6">
                        <small class="form-text text-muted">Leave blank if you don't want to change your password</small>
                    </div>
                    
                    <hr class="my-4">
                    
                    <h6 class="mb-3">Profile Information</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="first_name" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="first_name" name="first_name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="last_name" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="last_name" name="last_name" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="company_name" class="form-label">Company Name</label>
                        <input type="text" class="form-control" id="company_name" name="company_name" placeholder="e.g., Tech Recruiting Inc.">
                    </div>
                    
                    <div id="settings-error" class="alert alert-danger" style="display: none;"></div>
                    <div id="settings-success" class="alert alert-success" style="display: none;"></div>
                    
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary btn-lg">Save Changes</button>
                        <a href="/recruiter/home" class="btn btn-secondary btn-lg ms-2">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    const token = localStorage.getItem('api_token');
    const userRole = localStorage.getItem('user_role');
    
    if (!token) {
        window.location.href = '/login';
    }
    
    if (userRole !== 'recruiter') {
        window.location.href = '/candidate/home';
    }
    
    // Load current profile data
    async function loadProfile() {
        try {
            const response = await fetch('/api/recruiter/profile', {
                headers: {
                    'Authorization': 'Bearer ' + token,
                    'Accept': 'application/json'
                }
            });
            
            if (response.status === 401) {
                window.location.href = '/login';
                return;
            }
            
            const result = await response.json();
            
            if (result.user) {
                document.getElementById('username').value = result.user.username || '';
            }
            
            if (result.recruiter) {
                document.getElementById('first_name').value = result.recruiter.first_name || '';
                document.getElementById('last_name').value = result.recruiter.last_name || '';
                document.getElementById('email').value = result.recruiter.email || '';
                document.getElementById('company_name').value = result.recruiter.company_name || '';
            }
        } catch (error) {
            console.error('Error loading profile:', error);
            document.getElementById('settings-error').innerHTML = 'Error loading profile data.';
            document.getElementById('settings-error').style.display = 'block';
        }
    }
    
    document.getElementById('settings-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = {
            username: document.getElementById('username').value,
            first_name: document.getElementById('first_name').value,
            last_name: document.getElementById('last_name').value,
            email: document.getElementById('email').value,
        };
        
        // Add company_name if provided
        const companyName = document.getElementById('company_name').value.trim();
        if (companyName) {
            formData.company_name = companyName;
        }
        
        // Only include password if it's not empty
        const password = document.getElementById('password').value;
        if (password) {
            formData.password = password;
        }
        
        try {
            const response = await fetch('/api/recruiter/profile', {
                method: 'PUT',
                headers: {
                    'Authorization': 'Bearer ' + token,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(formData)
            });
            
            const result = await response.json();
            
            if (response.ok) {
                document.getElementById('settings-error').style.display = 'none';
                document.getElementById('settings-success').innerHTML = 'Profile updated successfully!';
                document.getElementById('settings-success').style.display = 'block';
                
                // Scroll to top to show success message
                window.scrollTo({ top: 0, behavior: 'smooth' });
            } else {
                document.getElementById('settings-success').style.display = 'none';
                let errorMessage = result.message || '';
                if (result.errors) {
                    const errorList = Object.entries(result.errors).map(([field, messages]) => {
                        return `<strong>${field}:</strong> ${Array.isArray(messages) ? messages.join(', ') : messages}`;
                    }).join('<br>');
                    errorMessage = errorList || errorMessage;
                }
                document.getElementById('settings-error').innerHTML = errorMessage;
                document.getElementById('settings-error').style.display = 'block';
            }
        } catch (error) {
            document.getElementById('settings-success').style.display = 'none';
            document.getElementById('settings-error').innerHTML = 'An error occurred: ' + error.message;
            document.getElementById('settings-error').style.display = 'block';
        }
    });
    
    // Load profile on page load
    loadProfile();
</script>
@endsection

