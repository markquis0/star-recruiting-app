@extends('layouts.app')

@section('title', 'Fill Out Form')

@section('content')
<div class="row px-3 px-lg-5">
    <div class="col-12">
        <h2>Project Form</h2>
        <div class="card">
            <div class="card-body">
                <form id="project-form">
                    <div class="mb-3">
                        <label for="project_name" class="form-label">Project Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="project_name" name="project_name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="description" name="description" rows="5" required></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="technologies" class="form-label">Technologies Used</label>
                        <input type="text" class="form-control" id="technologies" name="technologies" placeholder="e.g., PHP, Laravel, MySQL, JavaScript">
                    </div>
                    
                    <div class="mb-3">
                        <label for="duration" class="form-label">Project Duration</label>
                        <input type="text" class="form-control" id="duration" name="duration" placeholder="e.g., 3 months">
                    </div>
                    
                    <div class="mb-3">
                        <label for="role" class="form-label">Your Role</label>
                        <input type="text" class="form-control" id="role" name="role" placeholder="e.g., Lead Developer, Full-stack Developer">
                    </div>
                    
                    <div id="form-error" class="alert alert-danger" style="display: none;"></div>
                    <div id="form-success" class="alert alert-success" style="display: none;"></div>
                    
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary btn-lg">Submit Form</button>
                        <a href="/candidate/home" class="btn btn-secondary btn-lg ms-2">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    const token = localStorage.getItem('api_token');
    const formId = window.location.pathname.split('/').pop();
    
    if (!token) {
        window.location.href = '/login';
    }
    
    // Load existing form data if available
    async function loadFormData() {
        try {
            const response = await fetch(`/api/candidate/form/${formId}`, {
                headers: {
                    'Authorization': 'Bearer ' + token,
                    'Accept': 'application/json'
                }
            });
            
            if (response.ok) {
                const result = await response.json();
                if (result.form && result.form.data) {
                    const data = result.form.data;
                    if (data.project_name) document.getElementById('project_name').value = data.project_name;
                    if (data.description) document.getElementById('description').value = data.description;
                    if (data.technologies) document.getElementById('technologies').value = data.technologies;
                    if (data.duration) document.getElementById('duration').value = data.duration;
                    if (data.role) document.getElementById('role').value = data.role;
                }
            }
        } catch (error) {
            console.error('Error loading form:', error);
        }
    }
    
    document.getElementById('project-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = {
            data: {
                project_name: document.getElementById('project_name').value,
                description: document.getElementById('description').value,
                technologies: document.getElementById('technologies').value || null,
                duration: document.getElementById('duration').value || null,
                role: document.getElementById('role').value || null
            },
            status: 'submitted'
        };
        
        // Remove null/empty values
        Object.keys(formData.data).forEach(key => {
            if (!formData.data[key]) {
                delete formData.data[key];
            }
        });
        
        try {
            const response = await fetch(`/api/candidate/form/${formId}`, {
                method: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + token,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(formData)
            });
            
            const result = await response.json();
            
            if (response.ok) {
                document.getElementById('form-error').style.display = 'none';
                document.getElementById('form-success').innerHTML = 'Form submitted successfully! Redirecting...';
                document.getElementById('form-success').style.display = 'block';
                
                setTimeout(() => {
                    window.location.href = '/candidate/home';
                }, 1500);
            } else {
                document.getElementById('form-success').style.display = 'none';
                let errorMessage = result.message || '';
                if (result.errors) {
                    const errorList = Object.entries(result.errors).map(([field, messages]) => {
                        return `<strong>${field}:</strong> ${Array.isArray(messages) ? messages.join(', ') : messages}`;
                    }).join('<br>');
                    errorMessage = errorList || errorMessage;
                }
                document.getElementById('form-error').innerHTML = errorMessage;
                document.getElementById('form-error').style.display = 'block';
            }
        } catch (error) {
            document.getElementById('form-success').style.display = 'none';
            document.getElementById('form-error').innerHTML = 'An error occurred: ' + error.message;
            document.getElementById('form-error').style.display = 'block';
        }
    });
    
    // Load form data on page load
    loadFormData();
</script>
@endsection

