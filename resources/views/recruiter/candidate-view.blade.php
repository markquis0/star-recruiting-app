@extends('layouts.app')

@section('title', 'Candidate Profile')

@section('content')
<div class="dashboard-container">
    <!-- Sidebar -->
    <aside class="dashboard-sidebar">
        <div class="dashboard-sidebar-brand">
            <img src="{{ secure_asset('images/star-recruiting-logo.png') }}" alt="Star Recruiting Logo" onerror="this.style.display='none';">
            <h1>star recruiting</h1>
        </div>
        <nav class="dashboard-sidebar-nav">
            <a href="/recruiter/home">
                <svg class="dashboard-sidebar-nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
                Dashboard
            </a>
            <a href="/recruiter/settings">
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
            <h2>Candidate Profile</h2>
        </header>
        <div class="section-wrapper">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 section-title">Candidate Information</h5>
                    <a href="/recruiter/home" class="btn btn-sm btn-secondary">Back to Dashboard</a>
                </div>
            <div class="card-body">
                <div id="candidate-details">
                    <p>Loading candidate data...</p>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
    const token = localStorage.getItem('api_token');
    const userRole = localStorage.getItem('user_role');
    const candidateId = window.location.pathname.split('/')[3]; // /recruiter/candidate/{id}
    
    if (!token) {
        window.location.href = '/login';
    }
    
    if (userRole !== 'recruiter') {
        window.location.href = '/candidate/home';
    }
    
    async function loadCandidateData() {
        try {
            const response = await fetch(`/api/recruiter/candidate/${candidateId}`, {
                headers: {
                    'Authorization': 'Bearer ' + token,
                    'Accept': 'application/json'
                }
            });
            
            if (response.status === 401) {
                window.location.href = '/login';
                return;
            }
            
            if (!response.ok) {
                const error = await response.json();
                throw new Error(error.message || 'Failed to load candidate data');
            }
            
            const result = await response.json();
            const container = document.getElementById('candidate-details');
            
            if (result.candidate) {
                const candidate = result.candidate;
                currentCandidateId = candidate.id;
                const user = candidate.user || {};
                const forms = candidate.forms || [];
                
                let html = `
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h4 style="color: #FF3B6B; font-size: 1.25rem; margin-bottom: 0.75rem;">${candidate.first_name} ${candidate.last_name}</h4>
                            <p class="text-muted" style="font-size: 0.875rem; line-height: 1.6; margin-bottom: 0.5rem;"><strong>Username:</strong> ${user.username || 'N/A'}</p>
                            <p class="text-muted" style="font-size: 0.875rem; line-height: 1.6; margin-bottom: 0.5rem;"><strong>Role Title:</strong> ${candidate.role_title || 'N/A'}</p>
                            <p class="text-muted" style="font-size: 0.875rem; line-height: 1.6; margin-bottom: 0;"><strong>Years of Experience:</strong> ${candidate.years_exp || 0}</p>
                        </div>
                    </div>
                    <hr style="border-color: rgba(255, 255, 255, 0.1); margin: 1rem 0;">
                `;
                
                // Project Experience
                const projects = result.projects || [];
                if (projects.length > 0) {
                    html += `<h5 class="mt-4 mb-3" style="color: #FF3B6B; font-size: 1rem;">Project Experience (${projects.length})</h5>`;
                    projects.forEach((form, index) => {
                        const data = form.data || {};
                        const projectTitle = data.project_title || 'Untitled Project';
                        const roleTitle = data.role_title || '';
                        const company = data.company || '';
                        const timeframe = data.timeframe || '';
                        const summary = data.summary_one_liner || '';
                        const metrics = data.metrics || [];
                        
                        let roleCompanyText = '';
                        if (roleTitle && company) {
                            roleCompanyText = `${roleTitle} @ ${company}`;
                        } else if (roleTitle) {
                            roleCompanyText = roleTitle;
                        } else if (company) {
                            roleCompanyText = company;
                        }

                        let metricsHtml = '';
                        if (metrics && metrics.length > 0) {
                            metricsHtml = `
                                <div class="mt-2 mb-2">
                                    <strong class="text-sm" style="color: #FF3B6B;">Key Metrics:</strong>
                                    <ul class="mb-0 mt-1" style="font-size: 0.875rem;">
                                        ${metrics.map(m => {
                                            const name = m.metric_name || 'Metric';
                                            const baseline = m.baseline_value || null;
                                            const final = m.final_value || null;
                                            const tf = m.timeframe || null;
                                            
                                            let metricText = name;
                                            if (baseline || final) {
                                                metricText += `: ${baseline || '?'} → ${final || '?'}`;
                                            }
                                            if (tf) {
                                                metricText += ` (${tf})`;
                                            }
                                            
                                            return `<li style="color: #ccc;">${metricText}</li>`;
                                        }).join('')}
                                    </ul>
                                </div>
                            `;
                        }

                        html += `
                            <div class="card mb-3">
                                <div class="card-body">
                                    <h6 style="color: #FF3B6B; margin-bottom: 0.5rem; font-size: 0.875rem;">${projectTitle}</h6>
                                    ${roleCompanyText ? `<p class="text-muted mb-1" style="font-size: 0.875rem; line-height: 1.6;"><strong>Role:</strong> ${roleCompanyText}</p>` : ''}
                                    ${timeframe ? `<p class="text-muted mb-1" style="font-size: 0.875rem; line-height: 1.6;"><strong>Timeframe:</strong> ${timeframe}</p>` : ''}
                                    ${summary ? `<p class="text-muted mb-2" style="font-size: 0.875rem; line-height: 1.6;">${summary}</p>` : ''}
                                    ${metricsHtml}
                                    <p class="text-muted mb-1" style="font-size: 0.875rem; line-height: 1.6;"><strong>Status:</strong> <span class="badge" style="background: ${form.status === 'submitted' ? '#10b981' : '#f59e0b'}; color: white; padding: 0.25rem 0.5rem; border-radius: 0.5rem; font-size: 0.75rem;">${form.status}</span></p>
                                    <p class="text-muted mb-2" style="font-size: 0.875rem; line-height: 1.6;"><strong>Review Count:</strong> ${form.review_count || 0}</p>
                                    
                                    <button class="btn btn-sm btn-secondary" onclick="toggleProjectDetails(${form.id})" id="project-toggle-${form.id}">
                                        View Details
                                    </button>
                                    
                                    <div id="project-details-${form.id}" style="display:none;" class="mt-3 pt-3 border-top">
                                        ${data.context ? `<p class="text-muted mb-2" style="font-size: 0.875rem; line-height: 1.6;"><strong>Context:</strong> ${data.context}</p>` : ''}
                                        ${data.problem ? `<p class="text-muted mb-2" style="font-size: 0.875rem; line-height: 1.6;"><strong>Problem:</strong> ${data.problem}</p>` : ''}
                                        ${data.affected_audience ? `<p class="text-muted mb-2" style="font-size: 0.875rem; line-height: 1.6;"><strong>Affected audience:</strong> ${data.affected_audience}</p>` : ''}
                                        ${data.primary_goal ? `<p class="text-muted mb-2" style="font-size: 0.875rem; line-height: 1.6;"><strong>Primary goal:</strong> ${data.primary_goal}</p>` : ''}
                                        ${data.responsibilities ? `<p class="text-muted mb-2" style="font-size: 0.875rem; line-height: 1.6;"><strong>Responsibilities:</strong> ${data.responsibilities}</p>` : ''}
                                        ${data.project_type ? `<p class="text-muted mb-2" style="font-size: 0.875rem; line-height: 1.6;"><strong>Project type:</strong> ${data.project_type.replace('_', ' ')}</p>` : ''}
                                        ${data.teams_involved && data.teams_involved.length > 0 ? `<p class="text-muted mb-2" style="font-size: 0.875rem; line-height: 1.6;"><strong>Teams involved:</strong> ${Array.isArray(data.teams_involved) ? data.teams_involved.join(', ') : data.teams_involved}</p>` : ''}
                                        ${data.collaboration_example ? `<p class="text-muted mb-2" style="font-size: 0.875rem; line-height: 1.6;"><strong>Collaboration example:</strong> ${data.collaboration_example}</p>` : ''}
                                        ${data.challenges ? `<p class="text-muted mb-2" style="font-size: 0.875rem; line-height: 1.6;"><strong>Challenges:</strong> ${data.challenges}</p>` : ''}
                                        ${data.challenge_response ? `<p class="text-muted mb-2" style="font-size: 0.875rem; line-height: 1.6;"><strong>How handled:</strong> ${data.challenge_response}</p>` : ''}
                                        ${data.tradeoffs ? `<p class="text-muted mb-2" style="font-size: 0.875rem; line-height: 1.6;"><strong>Tradeoffs:</strong> ${data.tradeoffs}</p>` : ''}
                                        ${data.outcome_summary ? `<p class="text-muted mb-2" style="font-size: 0.875rem; line-height: 1.6;"><strong>Outcome:</strong> ${data.outcome_summary}</p>` : ''}
                                        ${data.impact ? `<p class="text-muted mb-2" style="font-size: 0.875rem; line-height: 1.6;"><strong>Impact:</strong> ${data.impact}</p>` : ''}
                                        ${data.recognition ? `<p class="text-muted mb-2" style="font-size: 0.875rem; line-height: 1.6;"><strong>Recognition:</strong> ${data.recognition}</p>` : ''}
                                        ${data.learning ? `<p class="text-muted mb-2" style="font-size: 0.875rem; line-height: 1.6;"><strong>Learning:</strong> ${data.learning}</p>` : ''}
                                        ${data.retro ? `<p class="text-muted mb-0" style="font-size: 0.875rem; line-height: 1.6;"><strong>What would be done differently:</strong> ${data.retro}</p>` : ''}
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                }
                
                // Behavioral Assessment
                const behavioralForm = forms.find(f => f.form_type === 'behavioral' && f.assessment);
                if (behavioralForm && behavioralForm.assessment) {
                    const assessment = behavioralForm.assessment;
                    html += `
                        <hr style="border-color: rgba(255, 255, 255, 0.1); margin: 1rem 0;">
                        <h5 class="mt-4 mb-3" style="color: #FF3B6B; font-size: 1rem;">Behavioral Assessment</h5>
                        <div class="card mb-3">
                            <div class="card-body">
                                <p class="text-muted" style="font-size: 0.875rem; line-height: 1.6; margin-bottom: 0.5rem;"><strong>Category:</strong> <span style="color: #FF3B6B;">${assessment.category || 'N/A'}</span></p>
                                <p class="text-muted" style="font-size: 0.875rem; line-height: 1.6; margin-bottom: 0.5rem;"><strong>Status:</strong> <span class="badge" style="background: ${behavioralForm.status === 'submitted' ? '#10b981' : '#f59e0b'}; color: white; padding: 0.25rem 0.5rem; border-radius: 0.5rem; font-size: 0.75rem;">${behavioralForm.status}</span></p>
                                <p class="text-muted" style="font-size: 0.875rem; line-height: 1.6; margin-bottom: 0;"><strong>Review Count:</strong> ${behavioralForm.review_count || 0}</p>
                    `;
                    
                    if (assessment.score_summary && !assessment.score_summary.categories) {
                        html += '<p class="mb-2 text-muted" style="font-size: 0.875rem;"><strong>Trait Scores:</strong></p><div class="row" style="gap: 0.5rem 0;">';
                        Object.entries(assessment.score_summary).forEach(([trait, score]) => {
                            html += `
                                <div class="col-md-3 mb-2">
                                    <strong style="color: #FF3B6B; font-size: 0.875rem;">${trait.charAt(0).toUpperCase() + trait.slice(1)}:</strong> <span style="color: #FF3B6B; font-size: 0.875rem;">${score}%</span>
                                    <div class="progress" style="height: 16px; background: #2A2A5A; margin-top: 0.25rem;">
                                        <div class="progress-bar" role="progressbar" style="width: ${score}%; background: linear-gradient(135deg, #FF3B6B 0%, #3F1E6F 100%);" aria-valuenow="${score}" aria-valuemin="0" aria-valuemax="100">
                                        </div>
                                    </div>
                                </div>
                            `;
                        });
                        html += '</div>';
                    }
                    
                    html += '</div></div>';
                }
                
                // Aptitude Assessment
                const aptitudeForm = forms.find(f => f.form_type === 'aptitude' && f.assessment);
                if (aptitudeForm && aptitudeForm.assessment) {
                    const assessment = aptitudeForm.assessment;
                    const summary = assessment.score_summary || {};
                    const categories = summary.categories || {};
                    const profileSummary = summary.profile_summary || '';
                    const overallAccuracy = summary.overall_accuracy ?? assessment.total_score;

                    html += `
                        <hr style="border-color: rgba(255, 255, 255, 0.1); margin: 1rem 0;">
                        <h5 class="mt-4 mb-3" style="color: #FF3B6B; font-size: 1rem;">Aptitude Assessment</h5>
                        <div class="card mb-3">
                            <div class="card-body">
                                <p class="text-muted" style="font-size: 0.875rem; line-height: 1.6; margin-bottom: 0.5rem;"><strong>Primary Strength:</strong> <span style="color: #FF3B6B;">${assessment.category || 'Pending Review'}</span></p>
                                <p class="text-muted" style="font-size: 0.875rem; line-height: 1.6; margin-bottom: 0.5rem;"><strong>Overall Accuracy:</strong> <span style="color: #FF3B6B;">${overallAccuracy !== null && overallAccuracy !== undefined ? `${overallAccuracy}%` : 'Pending review'}</span></p>
                                <p class="text-muted" style="font-size: 0.875rem; line-height: 1.6; margin-bottom: 0.5rem;"><strong>Status:</strong> <span class="badge" style="background: ${aptitudeForm.status === 'submitted' ? '#10b981' : '#f59e0b'}; color: white; padding: 0.25rem 0.5rem; border-radius: 0.5rem; font-size: 0.75rem;">${aptitudeForm.status}</span></p>
                                <p class="text-muted" style="font-size: 0.875rem; line-height: 1.6; margin-bottom: 0.5rem;"><strong>Review Count:</strong> ${aptitudeForm.review_count || 0}</p>
                                ${profileSummary ? `<p class="text-muted" style="font-size: 0.875rem; line-height: 1.6; margin-bottom: 0;">${profileSummary}</p>` : ''}
                            </div>
                        </div>
                    `;

                    if (Object.keys(categories).length > 0) {
                        html += `
                            <div class="card mb-3">
                                <div class="card-body">
                                    <h6 style="font-size: 0.85rem;" class="mb-3">Category Breakdown</h6>
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Category</th>
                                                    <th>Accuracy</th>
                                                    <th>Evaluated</th>
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
                            </div>
                        `;
                    }
                }
                
                // Show answers for Aptitude Assessment (if available)
                if (aptitudeForm && aptitudeForm.assessment && aptitudeForm.assessment.answers && aptitudeForm.assessment.answers.length > 0) {
                    const aptitudeAssessment = aptitudeForm.assessment;
                    html += `
                        <div class="card mb-3">
                            <div class="card-header">
                                <h6>Aptitude Assessment Answers</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Question</th>
                                                <th>Answer</th>
                                                <th>Result</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                        `;
                        
                        aptitudeAssessment.answers.forEach((answer, index) => {
                            const question = answer.question;
                            html += `
                                <tr>
                                    <td style="font-size: 0.875rem; line-height: 1.6;">${question ? question.question_text : `Question ${index + 1}`}</td>
                                    <td style="font-size: 0.875rem; line-height: 1.6;">${answer.answer}</td>
                                    <td style="font-size: 0.875rem; line-height: 1.6;">${answer.score !== null ? (answer.score === 1 ? '<span class="badge" style="background: #10b981; color: white; padding: 0.25rem 0.5rem; border-radius: 0.5rem; font-size: 0.75rem;">Correct</span>' : '<span class="badge" style="background: #ef4444; color: white; padding: 0.25rem 0.5rem; border-radius: 0.5rem; font-size: 0.75rem;">Incorrect</span>') : 'N/A'}</td>
                                </tr>
                            `;
                        });
                        
                        html += `
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    `;
                }
                
                // Save/Remove candidate button
                html += `
                    <hr style="border-color: rgba(255, 255, 255, 0.1); margin: 1rem 0;">
                    <div class="mt-4">
                        <button id="save-remove-btn" class="btn btn-success" onclick="toggleSaveCandidate(${candidate.id})">Save Candidate</button>
                    </div>
                `;
                
                container.innerHTML = html;
                
                // Check if candidate is already saved
                checkIfSaved(candidate.id);
            } else {
                container.innerHTML = '<p class="text-danger">Candidate not found.</p>';
            }
        } catch (error) {
            console.error('Error:', error);
            document.getElementById('candidate-details').innerHTML = '<p class="text-danger">Error loading candidate data: ' + error.message + '</p>';
        }
    }
    
    let savedCandidateId = null;
    let currentCandidateId = null;
    
    function checkIfSaved(candidateId) {
        currentCandidateId = candidateId;
        // Check if candidate is in saved list
        fetch('/api/recruiter/home', {
            headers: {
                'Authorization': 'Bearer ' + token,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.saved_candidates) {
                const saved = data.saved_candidates.find(sc => sc.candidate.id === candidateId);
                if (saved) {
                    savedCandidateId = saved.id;
                    updateSaveButton(false);
                } else {
                    updateSaveButton(true);
                }
            }
        })
        .catch(error => {
            console.error('Error checking saved status:', error);
        });
    }
    
    function updateSaveButton(isNotSaved) {
        const btn = document.getElementById('save-remove-btn');
        if (btn) {
            if (isNotSaved) {
                btn.className = 'btn btn-success';
                btn.textContent = 'Save Candidate';
                btn.onclick = () => saveCandidate(currentCandidateId);
            } else {
                btn.className = 'btn btn-danger';
                btn.textContent = 'Remove from Saved';
                btn.onclick = () => removeSavedCandidate(savedCandidateId);
            }
        }
    }
    
    async function saveCandidate(candidateId) {
        try {
            const response = await fetch('/api/recruiter/save', {
                method: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + token,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ candidate_id: candidateId })
            });

            if (response.ok) {
                await response.json();
                await showAppModal({
                    title: 'Candidate Saved',
                    message: 'Candidate saved successfully.',
                    variant: 'success'
                });
                loadCandidateData();
            } else {
                const data = await response.json();
                await showAppModal({
                    title: 'Save Candidate Failed',
                    message: data.message || 'Unable to save candidate.',
                    variant: 'danger'
                });
            }
        } catch (error) {
            console.error('Error:', error);
            await showAppModal({
                title: 'Save Candidate Failed',
                message: error.message,
                variant: 'danger'
            });
        }
    }
    
    async function removeSavedCandidate(savedCandidateIdToRemove) {
        const confirmed = await showAppConfirm({
            title: 'Remove Candidate',
            message: 'Are you sure you want to remove this candidate from your saved list?',
            confirmLabel: 'Remove',
            confirmVariant: 'danger',
            variant: 'warning'
        });

        if (!confirmed) {
            return;
        }
        
        try {
            const response = await fetch(`/api/recruiter/save/${savedCandidateIdToRemove}`, {
                method: 'DELETE',
                headers: {
                    'Authorization': 'Bearer ' + token,
                    'Accept': 'application/json'
                }
            });

            if (response.ok) {
                await response.json();
                savedCandidateId = null;
                await showAppModal({
                    title: 'Candidate Removed',
                    message: 'Candidate removed from your saved list.',
                    variant: 'success'
                });
                checkIfSaved(currentCandidateId);
            } else {
                const data = await response.json();
                await showAppModal({
                    title: 'Remove Candidate Failed',
                    message: data.message || 'Unable to remove candidate.',
                    variant: 'danger'
                });
            }
        } catch (error) {
            console.error('Error:', error);
            await showAppModal({
                title: 'Remove Candidate Failed',
                message: error.message,
                variant: 'danger'
            });
        }
    }
    
    function toggleSaveCandidate(candidateId) {
        // This function is set dynamically by updateSaveButton
        // It will call either saveCandidate or removeSavedCandidate
    }
    
    function toggleProjectDetails(projectId) {
        const detailsDiv = document.getElementById('project-details-' + projectId);
        const toggleBtn = document.getElementById('project-toggle-' + projectId);
        
        if (detailsDiv.style.display === 'none') {
            detailsDiv.style.display = 'block';
            toggleBtn.textContent = 'Hide Details';
        } else {
            detailsDiv.style.display = 'none';
            toggleBtn.textContent = 'View Details';
        }
    }
    
    loadCandidateData();
</script>
@endsection

