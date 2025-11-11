@extends('layouts.app')

@section('title', 'Candidate Dashboard')

@section('content')
<div class="row px-3 px-lg-5">
    <div class="col-12">
        <h2>Candidate Dashboard</h2>
        
        <!-- Create New Forms Section -->
        <div class="card mb-4">
            <div class="card-header">
                <h5>Create New Form</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <button class="btn btn-primary w-100" onclick="createForm('project')">
                            <strong>Project Form</strong><br>
                            <small>Share your project experience</small>
                        </button>
                    </div>
                    <div class="col-md-4 mb-3">
                        <button id="behavioral-btn" class="btn btn-success w-100" onclick="createForm('behavioral')">
                            <strong>Behavioral Assessment</strong><br>
                            <small>Rate yourself on key traits</small>
                        </button>
                    </div>
                    <div class="col-md-4 mb-3">
                        <button id="aptitude-btn" class="btn btn-info w-100" onclick="createForm('aptitude')">
                            <strong>Aptitude Test</strong><br>
                            <small>Test your skills</small>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- My Forms Section -->
        <div class="card">
            <div class="card-header">
                <h5>My Forms</h5>
            </div>
            <div class="card-body">
                <div id="forms-container">
                    <p>Loading forms...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // API token should be stored from login
    const token = localStorage.getItem('api_token');
    const userRole = localStorage.getItem('user_role');
    
    if (!token) {
        window.location.href = '/login';
    }
    
    if (userRole !== 'candidate') {
        window.location.href = '/recruiter/home';
    }
    
    function loadForms() {
        fetch('/api/candidate/home', {
            headers: {
                'Authorization': 'Bearer ' + token,
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (response.status === 401) {
                window.location.href = '/login';
                return;
            }
            return response.json();
        })
        .then(data => {
            if (!data) return;
            const container = document.getElementById('forms-container');
            
            // Check which assessment types already exist and disable buttons
            const hasBehavioral = data.forms && data.forms.some(f => f.form_type === 'behavioral');
            const hasAptitude = data.forms && data.forms.some(f => f.form_type === 'aptitude');
            
            updateCreateButtons(hasBehavioral, hasAptitude);
            
            if (data.forms && data.forms.length > 0) {
                container.innerHTML = data.forms.map(form => {
                    const formType = form.form_type.charAt(0).toUpperCase() + form.form_type.slice(1);
                    const statusBadge = form.status === 'submitted' ? 'success' : (form.status === 'reviewed' ? 'info' : 'warning');
                    
                    let actionButtons = '';
                    
                    if (form.status === 'incomplete') {
                        if (form.form_type === 'project') {
                            actionButtons = `<a href="/candidate/form/${form.id}" class="btn btn-sm btn-primary me-2">Fill Out</a>`;
                        } else if (form.form_type === 'behavioral' || form.form_type === 'aptitude') {
                            actionButtons = `<a href="/candidate/assessment/${form.id}?type=${form.form_type}" class="btn btn-sm btn-primary me-2">Take Assessment</a>`;
                        }
                    } else {
                        if (form.form_type === 'project') {
                            actionButtons = `<a href="/candidate/form/${form.id}/view" class="btn btn-sm btn-secondary me-2">View</a>`;
                        } else if (form.form_type === 'behavioral' || form.form_type === 'aptitude') {
                            actionButtons = `<a href="/candidate/assessment/${form.id}/view" class="btn btn-sm btn-secondary me-2">View</a>`;
                        } else {
                            actionButtons = `<button class="btn btn-sm btn-secondary me-2" onclick="viewForm(${form.id})">View</button>`;
                        }
                    }
                    
                    // Add delete button for all forms
                    actionButtons += `<button class="btn btn-sm btn-danger" onclick="deleteForm(${form.id}, '${form.form_type}')">Delete</button>`;
                    
                    return `
                        <div class="card mb-3">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h5>${formType} Form</h5>
                                        <p class="mb-1">Status: <span class="badge bg-${statusBadge}">${form.status}</span></p>
                                        <p class="mb-1">Review Count: ${form.review_count}</p>
                                        ${form.assessment ? `<p class="mb-1">Category: <strong>${form.assessment.category}</strong></p>` : ''}
                                        ${form.assessment && form.assessment.total_score !== null ? `<p class="mb-1">Score: ${form.assessment.total_score}%</p>` : ''}
                                        ${form.assessment && form.assessment.score_summary ? formatScoreSummary(form.assessment.score_summary) : ''}
                                    </div>
                                    <div>
                                        ${actionButtons}
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                }).join('');
            } else {
                container.innerHTML = '<p>No forms yet. Create your first form using the buttons above!</p>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('forms-container').innerHTML = '<p class="text-danger">Error loading forms. Please login again.</p>';
        });
    }
    
    function updateCreateButtons(hasBehavioral, hasAptitude) {
        const behavioralBtn = document.getElementById('behavioral-btn');
        const aptitudeBtn = document.getElementById('aptitude-btn');
        
        if (behavioralBtn) {
            if (hasBehavioral) {
                behavioralBtn.disabled = true;
                behavioralBtn.classList.remove('btn-success');
                behavioralBtn.classList.add('btn-secondary');
                behavioralBtn.innerHTML = '<strong>Behavioral Assessment</strong><br><small>Already created</small>';
            } else {
                behavioralBtn.disabled = false;
                behavioralBtn.classList.remove('btn-secondary');
                behavioralBtn.classList.add('btn-success');
                behavioralBtn.innerHTML = '<strong>Behavioral Assessment</strong><br><small>Rate yourself on key traits</small>';
            }
        }
        
        if (aptitudeBtn) {
            if (hasAptitude) {
                aptitudeBtn.disabled = true;
                aptitudeBtn.classList.remove('btn-info');
                aptitudeBtn.classList.add('btn-secondary');
                aptitudeBtn.innerHTML = '<strong>Aptitude Test</strong><br><small>Already created</small>';
            } else {
                aptitudeBtn.disabled = false;
                aptitudeBtn.classList.remove('btn-secondary');
                aptitudeBtn.classList.add('btn-info');
                aptitudeBtn.innerHTML = '<strong>Aptitude Test</strong><br><small>Test your skills</small>';
            }
        }
    }
    
    function createForm(formType) {
        // Check if button is disabled
        const btn = formType === 'behavioral' ? document.getElementById('behavioral-btn') : 
                   formType === 'aptitude' ? document.getElementById('aptitude-btn') : null;
        
        if (btn && btn.disabled) {
            const formTypeName = formType.charAt(0).toUpperCase() + formType.slice(1);
            alert(`You already have a ${formTypeName} assessment. You can only create one.`);
            return;
        }
        
        fetch('/api/candidate/form', {
            method: 'POST',
            headers: {
                'Authorization': 'Bearer ' + token,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ form_type: formType })
        })
        .then(response => {
            if (response.status === 409) {
                return response.json().then(data => {
                    alert(data.message || 'You already have this type of assessment.');
                    loadForms(); // Refresh to update button states
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.form) {
                loadForms();
                // Auto-redirect to the form page
                if (formType === 'project') {
                    window.location.href = `/candidate/form/${data.form.id}`;
                } else {
                    window.location.href = `/candidate/assessment/${data.form.id}?type=${formType}`;
                }
            } else if (!data.message) {
                alert('Error: ' + JSON.stringify(data));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error creating form: ' + error.message);
        });
    }
    
    
    function viewForm(formId) {
        // For non-project forms, show in popup for now
        fetch(`/api/candidate/form/${formId}`, {
            headers: {
                'Authorization': 'Bearer ' + token,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.form) {
                alert('Form Data:\n' + JSON.stringify(data.form.data || {}, null, 2));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading form');
        });
    }
    
    function deleteForm(formId, formType) {
        const formTypeName = formType.charAt(0).toUpperCase() + formType.slice(1);
        
        if (!confirm(`Are you sure you want to delete this ${formTypeName} form? This action cannot be undone.`)) {
            return;
        }
        
        fetch(`/api/candidate/form/${formId}`, {
            method: 'DELETE',
            headers: {
                'Authorization': 'Bearer ' + token,
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (response.ok) {
                return response.json().then(data => {
                    alert('Form deleted successfully!');
                    loadForms(); // Reload the forms list (this will also update button states)
                });
            } else {
                return response.json().then(data => {
                    alert('Error: ' + (data.message || 'Failed to delete form'));
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error deleting form: ' + error.message);
        });
    }
    
    function formatScoreSummary(scoreSummary) {
        if (!scoreSummary || typeof scoreSummary !== 'object') {
            return '';
        }
        
        const traits = {
            'analytical': 'Analytical',
            'collaborative': 'Collaborative',
            'persistent': 'Persistent',
            'social': 'Social'
        };
        
        let html = '<div class="mt-2"><small class="text-muted">Trait Scores:</small><div class="mt-1">';
        
        Object.entries(scoreSummary).forEach(([trait, score]) => {
            const traitName = traits[trait] || trait;
            html += `<span class="badge bg-secondary me-1">${traitName}: ${score}%</span>`;
        });
        
        html += '</div></div>';
        return html;
    }
    
    // Load forms on page load
    loadForms();
</script>
@endsection

