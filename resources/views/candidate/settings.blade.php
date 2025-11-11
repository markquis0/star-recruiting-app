@extends('layouts.app')

@section('title', 'Settings - Candidate')

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
                        <label for="role_title" class="form-label">Role Title</label>
                        <input type="text" class="form-control" id="role_title" name="role_title" placeholder="e.g., Software Engineer">
                    </div>
                    
                    <div class="mb-3">
                        <label for="years_exp" class="form-label">Years of Experience</label>
                        <input type="number" class="form-control" id="years_exp" name="years_exp" min="0" placeholder="e.g., 5">
                    </div>
                    
                    <div id="settings-error" class="alert alert-danger" style="display: none;"></div>
                    <div id="settings-success" class="alert alert-success" style="display: none;"></div>
                    
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary btn-lg">Save Changes</button>
                        <a href="/candidate/home" class="btn btn-secondary btn-lg ms-2">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    const token = localStorage.getItem('api_token');
    
    if (!token) {
        window.location.href = '/login';
    }
    
    // Load current profile data
    async function loadProfile() {
        try {
            const response = await fetch('/api/candidate/profile', {
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
            
            if (result.candidate) {
                document.getElementById('first_name').value = result.candidate.first_name || '';
                document.getElementById('last_name').value = result.candidate.last_name || '';
                document.getElementById('role_title').value = result.candidate.role_title || '';
                document.getElementById('years_exp').value = result.candidate.years_exp || '';
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
            role_title: document.getElementById('role_title').value || null,
            years_exp: document.getElementById('years_exp').value || null,
        };
        
        // Only include password if it's not empty
        const password = document.getElementById('password').value;
        if (password) {
            formData.password = password;
        }
        
        // Remove null values
        Object.keys(formData).forEach(key => {
            if (formData[key] === null || formData[key] === '') {
                delete formData[key];
            }
        });
        
        try {
            const response = await fetch('/api/candidate/profile', {
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

