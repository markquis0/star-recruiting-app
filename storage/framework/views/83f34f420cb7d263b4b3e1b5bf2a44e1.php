<?php $__env->startSection('title', 'View Assessment Results'); ?>

<?php $__env->startSection('content'); ?>
<div class="row">
    <div class="col-12">
        <h2 id="assessment-title">Assessment Results</h2>
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Assessment Details</h5>
                <a href="/candidate/home" class="btn btn-sm btn-secondary">Back to Dashboard</a>
            </div>
            <div class="card-body">
                <div id="assessment-details">
                    <p>Loading assessment data...</p>
                </div>
            </div>
        </div>
    </div>
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
                            <label class="form-label fw-bold">Status</label>
                            <div>
                                <span class="badge bg-${form.status === 'submitted' ? 'success' : (form.status === 'reviewed' ? 'info' : 'warning')}">
                                    ${form.status.charAt(0).toUpperCase() + form.status.slice(1)}
                                </span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Review Count</label>
                            <div>${form.review_count || 0}</div>
                        </div>
                    </div>
                    <hr>
                `;
                
                if (assessment) {
                    if (form.form_type === 'behavioral' && assessment.score_summary) {
                        html += `
                            <div class="mb-4">
                                <h5>Your Behavioral Profile</h5>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Primary Category</label>
                                        <div class="p-3 bg-primary text-white rounded">
                                            <h4 class="mb-0">${assessment.category || 'N/A'}</h4>
                                        </div>
                                    </div>
                                </div>
                                <h6 class="mt-4 mb-3">Trait Scores</h6>
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
                                    <label class="form-label fw-bold">${trait.charAt(0).toUpperCase() + trait.slice(1)}</label>
                                    <div class="progress" style="height: 30px;">
                                        <div class="progress-bar" role="progressbar" style="width: ${percentage}%" aria-valuenow="${percentage}" aria-valuemin="0" aria-valuemax="100">
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
                                <h5>Your Aptitude Results</h5>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Category</label>
                                        <div class="p-3 bg-info text-white rounded">
                                            <h4 class="mb-0">${assessment.category || 'N/A'}</h4>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Score</label>
                                        <div class="p-3 bg-secondary text-white rounded">
                                            <h4 class="mb-0">${assessment.total_score || 0}%</h4>
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
                                    <td>${question ? question.question_text : `Question ${index + 1}`}</td>
                                    <td>${answer.answer}</td>
                                    ${form.form_type === 'aptitude' ? `<td>${answer.score !== null ? (answer.score === 1 ? '✓ Correct' : '✗ Incorrect') : 'N/A'}</td>` : ''}
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
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/markquissimmons/star-recruiting-app/resources/views/candidate/assessment-view.blade.php ENDPATH**/ ?>