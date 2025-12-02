@extends('layouts.app')

@section('title', 'View Assessment Results')

@section('content')
<div class="container" style="max-width: 1200px; margin: 2rem auto; padding: 0 1rem;">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 id="assessment-title">Assessment Results</h2>
                <a href="/candidate/home" class="btn btn-sm btn-secondary">Back to Dashboard</a>
            </div>
            <div class="section-wrapper">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 section-title">Assessment Details</h5>
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
                    if (form.form_type === 'behavioral' && assessment.score_summary && !assessment.score_summary.categories) {
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
                        const summary = assessment.score_summary || {};
                        const categories = summary.categories || {};
                        const overallAccuracy = summary.overall_accuracy ?? assessment.total_score;
                        const profileSummary = summary.profile_summary || '';

                        html += `
                            <div class="mb-4">
                                <h5 style="font-size: 1rem; margin-bottom: 1rem;">Your Aptitude Results</h5>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold" style="font-size: 0.875rem;">Primary Strength</label>
                                        <div class="p-3 rounded" style="background: #2A2A5A; color: #FF3B6B;">
                                            <h4 class="mb-0" style="font-size: 1.25rem;">${assessment.category || 'Pending Review'}</h4>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold" style="font-size: 0.875rem;">Overall Accuracy</label>
                                        <div class="p-3 rounded" style="background: #2A2A5A; color: #FF3B6B;">
                                            <h4 class="mb-0" style="font-size: 1.25rem;">${overallAccuracy !== null && overallAccuracy !== undefined ? `${overallAccuracy}%` : 'Pending Review'}</h4>
                                        </div>
                                    </div>
                                </div>
                                ${profileSummary ? `<p class="text-muted mb-0">${profileSummary}</p>` : ''}
                            </div>
                        `;

                        if (Object.keys(categories).length > 0) {
                            html += `
                                <div class="mb-4">
                                    <h6 style="font-size: 0.9rem;" class="mb-3">Category Breakdown</h6>
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Category</th>
                                                    <th>Accuracy</th>
                                                    <th>Evaluated Questions</th>
                                                    <th>Open Responses</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                ${Object.values(categories).map(category => {
                                                    const accuracyText = category.accuracy !== null && category.accuracy !== undefined ? `${category.accuracy}%` : 'Pending review';
                                                    return `
                                                        <tr>
                                                            <td>${category.label || 'Category'}</td>
                                                            <td>${accuracyText}</td>
                                                            <td>${category.evaluated_questions}</td>
                                                            <td>${category.open_responses}</td>
                                                        </tr>
                                                    `;
                                                }).join('')}
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            `;
                        }
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
        </div>
    </div>
</div>
@endsection

