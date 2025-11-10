@extends('layouts.app')

@section('title', 'View Assessment Results')

@section('content')
<div class="dashboard-container">
    <!-- Sidebar -->
    <aside class="dashboard-sidebar">
        <div class="dashboard-sidebar-brand">
            <img src="{{ asset('images/star-recruiting-logo.png') }}" alt="Star Recruiting Logo" onerror="this.style.display='none';">
            <h1>star recruiting</h1>
        </div>
        <nav class="dashboard-sidebar-nav">
            <a href="/candidate/home">
                <svg class="dashboard-sidebar-nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
                Dashboard
            </a>
            <a href="/candidate/settings">
                <svg class="dashboard-sidebar-nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                Settings
            </a>
            <a href="#" onclick="handleLogout(); return false;">
                <svg class="dashboard-sidebar-nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                </svg>
                Logout
            </a>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="dashboard-main">
        <header class="dashboard-header">
            <h2 id="assessment-title">Assessment Results</h2>
        </header>
        <div class="section-wrapper">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 section-title">Assessment Details</h5>
                    <a href="/candidate/home" class="btn btn-sm btn-secondary">Back to Dashboard</a>
                </div>
            <div class="card-body">
                <div id="assessment-details">
                    <p>Loading assessment data...</p>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
    const token = localStorage.getItem('api_token');
    const formId = window.location.pathname.split('/')[3]; // /candidate/assessment/{id}/view
    
    if (!token) {
        window.location.href = '/login';
    }
    
    async function loadAssessmentData() {
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
            const container = document.getElementById('assessment-details');
            const title = document.getElementById('assessment-title');
            
            if (result.form) {
                const form = result.form;
                const assessment = form.assessment;
                
                // Set title based on form type
                if (form.form_type === 'behavioral') {
                    title.textContent = 'Behavioral Assessment Results';
                } else if (form.form_type === 'aptitude') {
                    title.textContent = 'Aptitude Test Results';
                }
                
                let html = `
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold" style="font-size: 0.875rem;">Status</label>
                            <div>
                                <span class="badge" style="background: ${form.status === 'submitted' ? '#10b981' : (form.status === 'reviewed' ? '#3b82f6' : '#f59e0b')}; color: white; padding: 0.25rem 0.5rem; border-radius: 0.5rem; font-size: 0.75rem;">
                                    ${form.status.charAt(0).toUpperCase() + form.status.slice(1)}
                                </span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold" style="font-size: 0.875rem;">Review Count</label>
                            <div style="font-size: 0.875rem; line-height: 1.6;">${form.review_count || 0}</div>
                        </div>
                    </div>
                    <hr style="border-color: rgba(255, 255, 255, 0.1); margin: 1rem 0;">
                `;
                
                if (assessment) {
                    if (form.form_type === 'behavioral' && assessment.score_summary) {
                        html += `
                            <div class="mb-4">
                                <h5 style="font-size: 1rem; margin-bottom: 1rem;">Your Behavioral Profile</h5>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold" style="font-size: 0.875rem;">Primary Category</label>
                                        <div class="p-3 rounded" style="background: #2A2A5A; color: #FF3B6B;">
                                            <h4 class="mb-0" style="font-size: 1.25rem;">${assessment.category || 'N/A'}</h4>
                                        </div>
                                    </div>
                                </div>
                                <h6 class="mt-4 mb-3" style="font-size: 0.875rem;">Trait Scores</h6>
                                <div class="row">
                        `;
                        
                        // Display score summary
                        const scores = assessment.score_summary;
                        const traits = ['analytical', 'collaborative', 'persistent', 'social'];
                        
                        traits.forEach(trait => {
                            const score = scores[trait] || 0;
                            const percentage = score;
                            html += `
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold" style="font-size: 0.875rem;">${trait.charAt(0).toUpperCase() + trait.slice(1)}</label>
                                    <div class="progress" style="height: 24px;">
                                        <div class="progress-bar" role="progressbar" style="width: ${percentage}%; font-size: 0.75rem; line-height: 24px;" aria-valuenow="${percentage}" aria-valuemin="0" aria-valuemax="100">
                                            ${percentage}%
                                        </div>
                                    </div>
                                </div>
                            `;
                        });
                        
                        html += `
                                </div>
                            </div>
                        `;
                    } else if (form.form_type === 'aptitude') {
                        html += `
                            <div class="mb-4">
                                <h5 style="font-size: 1rem; margin-bottom: 1rem;">Your Aptitude Results</h5>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold" style="font-size: 0.875rem;">Category</label>
                                        <div class="p-3 rounded" style="background: #2A2A5A; color: #FF3B6B;">
                                            <h4 class="mb-0" style="font-size: 1.25rem;">${assessment.category || 'N/A'}</h4>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold" style="font-size: 0.875rem;">Score</label>
                                        <div class="p-3 rounded" style="background: #2A2A5A; color: #FF3B6B;">
                                            <h4 class="mb-0" style="font-size: 1.25rem;">${assessment.total_score || 0}%</h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                    }
                    
                    // Show individual answers if available
                    if (assessment.answers && assessment.answers.length > 0) {
                        html += `
                            <hr>
                            <h5 class="mt-4">Your Answers</h5>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Question</th>
                                            <th>Your Answer</th>
                                            ${form.form_type === 'aptitude' ? '<th>Score</th>' : ''}
                                        </tr>
                                    </thead>
                                    <tbody>
                        `;
                        
                        assessment.answers.forEach((answer, index) => {
                            const question = answer.question;
                            html += `
                                <tr>
                                    <td style="font-size: 0.875rem; line-height: 1.6;">${question ? question.question_text : `Question ${index + 1}`}</td>
                                    <td style="font-size: 0.875rem; line-height: 1.6;">${answer.answer}</td>
                                    ${form.form_type === 'aptitude' ? `<td style="font-size: 0.875rem; line-height: 1.6;">${answer.score !== null ? (answer.score === 1 ? '<span style="color: #10b981;">✓ Correct</span>' : '<span style="color: #ef4444;">✗ Incorrect</span>') : 'N/A'}</td>` : ''}
                                </tr>
                            `;
                        });
                        
                        html += `
                                    </tbody>
                                </table>
                            </div>
                        `;
                    }
                } else {
                    html += '<p class="text-muted">No assessment results available yet.</p>';
                }
                
                container.innerHTML = html;
            } else {
                container.innerHTML = '<p class="text-danger">Assessment not found.</p>';
            }
        } catch (error) {
            console.error('Error:', error);
            document.getElementById('assessment-details').innerHTML = '<p class="text-danger">Error loading assessment data.</p>';
        }
    }
    
    loadAssessmentData();
</script>
@endsection

