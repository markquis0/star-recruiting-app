@extends('layouts.app')

@section('title', 'View Project Form')

@section('content')
<div class="row px-3 px-lg-5">
    <div class="col-12">
        <h2>Project Form Details</h2>
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Project Information</h5>
                <a href="/candidate/home" class="btn btn-sm btn-secondary">Back to Dashboard</a>
            </div>
            <div class="card-body">
                <div id="form-details">
                    <p>Loading form data...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const token = localStorage.getItem('api_token');
    const formId = window.location.pathname.split('/')[3]; // /candidate/form/{id}/view
    
    if (!token) {
        window.location.href = '/login';
    }
    
    async function loadFormData() {
        try {
            const response = await fetch(`/api/candidate/form/${formId}`, {
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
            const container = document.getElementById('form-details');
            
            if (result.form) {
                const form = result.form;
                const data = form.data || {};
                
                let html = `
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Status</label>
                            <div>
                                <span class="badge bg-${form.status === 'submitted' ? 'success' : (form.status === 'reviewed' ? 'info' : 'warning')}">
                                    ${form.status.charAt(0).toUpperCase() + form.status.slice(1)}
                                </span>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Review Count</label>
                            <div>${form.review_count || 0}</div>
                        </div>
                    </div>
                    <hr>
                `;
                
                if (data.project_name) {
                    html += `
                        <div class="mb-3">
                            <label class="form-label fw-bold">Project Name</label>
                            <div class="p-2 bg-light rounded">${data.project_name}</div>
                        </div>
                    `;
                }
                
                if (data.description) {
                    html += `
                        <div class="mb-3">
                            <label class="form-label fw-bold">Description</label>
                            <div class="p-2 bg-light rounded" style="white-space: pre-wrap;">${data.description}</div>
                        </div>
                    `;
                }
                
                if (data.technologies) {
                    html += `
                        <div class="mb-3">
                            <label class="form-label fw-bold">Technologies Used</label>
                            <div class="p-2 bg-light rounded">${data.technologies}</div>
                        </div>
                    `;
                }
                
                if (data.duration) {
                    html += `
                        <div class="mb-3">
                            <label class="form-label fw-bold">Project Duration</label>
                            <div class="p-2 bg-light rounded">${data.duration}</div>
                        </div>
                    `;
                }
                
                if (data.role) {
                    html += `
                        <div class="mb-3">
                            <label class="form-label fw-bold">Your Role</label>
                            <div class="p-2 bg-light rounded">${data.role}</div>
                        </div>
                    `;
                }
                
                if (Object.keys(data).length === 0) {
                    html += '<p class="text-muted">No form data available.</p>';
                }
                
                container.innerHTML = html;
            } else {
                container.innerHTML = '<p class="text-danger">Form not found.</p>';
            }
        } catch (error) {
            console.error('Error:', error);
            document.getElementById('form-details').innerHTML = '<p class="text-danger">Error loading form data.</p>';
        }
    }
    
    loadFormData();
</script>
@endsection

