@extends('layouts.app')

@section('title', 'Candidate Dashboard')

@section('content')
<div class="row px-3 px-lg-5 mt-4 mt-lg-5">
    <div class="col-12">
        <h2>Candidate Dashboard</h2>
        
        <!-- Shareable Profile Link Section -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Shareable Profile Link</h5>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">
                    Generate a read-only link to share your Star Recruiting profile with recruiters.
                </p>

                <div id="public-profile-status" class="mb-3"></div>

                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-primary btn-sm" onclick="generatePublicProfile()">
                        Generate / Regenerate Link
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="disablePublicProfile()">
                        Disable Link
                    </button>
                </div>
            </div>
        </div>

        <!-- Create New Forms Section -->
        <div class="card mb-4">
            <div class="card-header">
                <h5>Create New</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <a href="/candidate/projects/new" class="btn btn-primary w-100">
                            <strong>Project</strong><br>
                            <small>Share your project experience</small>
                        </a>
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
                <h5 class="mb-0">My Forms</h5>
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
    const token = localStorage.getItem('api_token');
    const userRole = localStorage.getItem('user_role');

    if (!token) {
        window.location.href = '/login';
    }

    if (userRole !== 'candidate') {
        window.location.href = '/recruiter/home';
    }

    function escapeHtml(value) {
        if (!value) return '';
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    async function loadForms() {
        try {
            const response = await fetch('/api/candidate/home', {
                headers: {
                    'Authorization': 'Bearer ' + token,
                    'Accept': 'application/json'
                }
            });

            if (response.status === 401) {
                window.location.href = '/login';
                return;
            }

            const data = await response.json();
            const forms = data.forms || [];
            const container = document.getElementById('forms-container');

            const hasBehavioral = forms.some(f => f.form_type === 'behavioral');
            const hasAptitude = forms.some(f => f.form_type === 'aptitude');
            updateCreateButtons(hasBehavioral, hasAptitude);

            if (!forms.length) {
                container.innerHTML = '<p>No forms yet. Create your first form using the buttons above!</p>';
                return;
            }

            container.innerHTML = forms.map(form => {
                // Special rendering for projects
                if (form.form_type === 'project') {
                    return renderProjectForm(form);
                }
                
                // Standard rendering for assessments
                return renderAssessmentForm(form);
            }).join('');
        } catch (error) {
            console.error('Error:', error);
            document.getElementById('forms-container').innerHTML = '<p class="text-danger">Error loading forms. Please login again.</p>';
        }
    }

    function renderProjectForm(form) {
        const data = form.data || {};
        const projectTitle = data.project_title || 'Untitled Project';
        const roleTitle = data.role_title || '';
        const company = data.company || '';
        const timeframe = data.timeframe || '';
        const summary = data.summary_one_liner || '';
        const metrics = data.metrics || [];
        const statusBadge = form.status === 'submitted' ? 'success' : (form.status === 'reviewed' ? 'info' : 'warning');

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
                <div class="mt-2">
                    <small class="text-muted">Key Metrics:</small>
                    <ul class="list-unstyled mb-0 mt-1" style="font-size: 0.875rem;">
                        ${metrics.map(metric => {
                            const name = metric.metric_name || 'Metric';
                            const baseline = metric.baseline_value || null;
                            const final = metric.final_value || null;
                            const tf = metric.timeframe || null;
                            
                            let metricText = name;
                            if (baseline || final) {
                                metricText += `: ${baseline || '?'} → ${final || '?'}`;
                            }
                            if (tf) {
                                metricText += ` (${tf})`;
                            }
                            
                            return `<li class="text-muted mb-1">${escapeHtml(metricText)}</li>`;
                        }).join('')}
                    </ul>
                </div>
            `;
        }

        let actionButtons = '';
        if (form.status === 'incomplete') {
            actionButtons = `<a href="/candidate/projects/${form.id}/edit" class="btn btn-sm btn-primary me-2">Fill Out</a>`;
        } else {
            actionButtons = `<a href="/candidate/projects/${form.id}/edit" class="btn btn-sm btn-secondary me-2">View/Edit</a>`;
        }
        actionButtons += `<button class="btn btn-sm btn-danger" onclick="deleteForm(${form.id}, '${form.form_type}')">Delete</button>`;

        return `
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <h5 style="color: #FF3B6B; margin-bottom: 0.5rem;">${escapeHtml(projectTitle)}</h5>
                            ${roleCompanyText ? `<p class="text-muted mb-1 small">${escapeHtml(roleCompanyText)}</p>` : ''}
                            ${timeframe ? `<p class="text-muted mb-1 small"><small>${escapeHtml(timeframe)}</small></p>` : ''}
                            ${summary ? `<p class="mb-2">${escapeHtml(summary)}</p>` : ''}
                            ${metricsHtml}
                            <p class="mb-1 mt-2">Status: <span class="badge bg-${statusBadge}">${form.status}</span></p>
                            <p class="mb-0">Review Count: ${form.review_count || 0}</p>
                        </div>
                        <div class="text-end ms-3">
                            ${actionButtons}
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    function renderAssessmentForm(form) {
        const formType = form.form_type.charAt(0).toUpperCase() + form.form_type.slice(1);
        const statusBadge = form.status === 'submitted' ? 'success' : (form.status === 'reviewed' ? 'info' : 'warning');

        let actionButtons = '';

        if (form.status === 'incomplete') {
            if (form.form_type === 'behavioral' || form.form_type === 'aptitude') {
                actionButtons = `<a href="/candidate/assessment/${form.id}?type=${form.form_type}" class="btn btn-sm btn-primary me-2">Take Assessment</a>`;
            }
        } else {
            if (form.form_type === 'behavioral' || form.form_type === 'aptitude') {
                actionButtons = `<a href="/candidate/assessment/${form.id}/view" class="btn btn-sm btn-secondary me-2">View</a>`;
            } else {
                actionButtons = `<button class="btn btn-sm btn-secondary me-2" onclick="viewForm(${form.id})">View</button>`;
            }
        }

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

    async function createForm(formType) {
        const btn = formType === 'behavioral' ? document.getElementById('behavioral-btn') :
                    formType === 'aptitude' ? document.getElementById('aptitude-btn') : null;

        if (btn && btn.disabled) {
            const formTypeName = formType.charAt(0).toUpperCase() + formType.slice(1);
            await showAppModal({
                title: `${formTypeName} Assessment Already Exists`,
                message: `You already have a ${formTypeName.toLowerCase()} assessment. You can only create one.`,
                variant: 'warning'
            });
            return;
        }

        try {
            const response = await fetch('/api/candidate/form', {
                method: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + token,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ form_type: formType })
            });

            if (response.status === 409) {
                const data = await response.json();
                await showAppModal({
                    title: 'Assessment Already Exists',
                    message: data.message || 'You already have this type of assessment.',
                    variant: 'warning'
                });
                await loadForms();
                return;
            }

            const data = await response.json();
            if (data.form) {
                await loadForms();
                // Projects should be created via /candidate/projects/new, not here
                if (formType !== 'project') {
                    window.location.href = `/candidate/assessment/${data.form.id}?type=${formType}`;
                }
            } else if (!data.message) {
                await showAppModal({
                    title: 'Unexpected Response',
                    message: '<pre class="mb-0">' + escapeHtml(JSON.stringify(data, null, 2)) + '</pre>',
                    variant: 'warning',
                    renderHtml: true
                });
            }
        } catch (error) {
            console.error('Error:', error);
            await showAppModal({
                title: 'Error Creating Form',
                message: error.message,
                variant: 'danger'
            });
        }
    }

    async function viewForm(formId) {
        try {
            const response = await fetch(`/api/candidate/form/${formId}`, {
                headers: {
                    'Authorization': 'Bearer ' + token,
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();
            if (data.form) {
                const formatted = JSON.stringify(data.form.data || {}, null, 2);
                await showAppModal({
                    title: 'Form Data',
                    message: '<pre class="mb-0">' + escapeHtml(formatted) + '</pre>',
                    renderHtml: true
                });
            }
        } catch (error) {
            console.error('Error:', error);
            await showAppModal({
                title: 'Error Loading Form',
                message: error.message,
                variant: 'danger'
            });
        }
    }

    async function deleteForm(formId, formType) {
        const formTypeName = formType.charAt(0).toUpperCase() + formType.slice(1);
        const confirmed = await showAppConfirm({
            title: `Delete ${formTypeName} Form`,
            message: 'This action cannot be undone. Do you want to continue?',
            confirmLabel: 'Delete',
            confirmVariant: 'danger',
            variant: 'warning'
        });

        if (!confirmed) {
            return;
        }

        try {
            const response = await fetch(`/api/candidate/form/${formId}`, {
                method: 'DELETE',
                headers: {
                    'Authorization': 'Bearer ' + token,
                    'Accept': 'application/json'
                }
            });

            if (response.ok) {
                await loadForms();
                await showAppModal({
                    title: 'Form Deleted',
                    message: `${formTypeName} form deleted successfully.`,
                    variant: 'success'
                });
            } else {
                const data = await response.json();
                await showAppModal({
                    title: 'Error Deleting Form',
                    message: data.message || 'Failed to delete form.',
                    variant: 'danger'
                });
            }
        } catch (error) {
            console.error('Error:', error);
            await showAppModal({
                title: 'Error Deleting Form',
                message: error.message,
                variant: 'danger'
            });
        }
    }

    function formatScoreSummary(scoreSummary) {
        if (!scoreSummary || typeof scoreSummary !== 'object') {
            return '';
        }

        // Behavioral structure (flat map of traits)
        const isBehavioralSummary = Object.values(scoreSummary).every(value => typeof value === 'number');

        if (isBehavioralSummary) {
            const traits = {
                'analytical': 'Analytical',
                'collaborative': 'Collaborative',
                'persistent': 'Persistent',
                'social': 'Social'
            };
            let html = '<div class="mt-2"><small class="text-muted">Trait Scores:</small><div class="mt-1">';

            Object.entries(scoreSummary).forEach(([trait, score]) => {
                const traitName = traits[trait] || trait;
                html += `<span class="badge bg-secondary me-1">${escapeHtml(traitName)}: ${score}%</span>`;
            });

            html += '</div></div>';
            return html;
        }

        // Aptitude structure (categories + profile summary)
        const categories = scoreSummary.categories || {};
        const profileSummary = scoreSummary.profile_summary || '';
        const categoryLabels = {
            'analytical': 'Analytical',
            'creative': 'Creative',
            'pragmatic': 'Pragmatic',
            'relational': 'Relational',
            'general': 'General'
        };

        let html = '<div class="mt-2">';

        if (Object.keys(categories).length > 0) {
            html += '<small class="text-muted">Category Insights:</small><div class="mt-1">';
            Object.entries(categories).forEach(([key, data]) => {
                const label = data.label || categoryLabels[key] || key;
                const accuracy = data.accuracy !== null && data.accuracy !== undefined
                    ? `${data.accuracy}%`
                    : 'Pending review';
                const openNotes = data.open_responses && data.open_responses > 0
                    ? ` • ${data.open_responses} open response${data.open_responses > 1 ? 's' : ''}`
                    : '';
                const safeLabel = escapeHtml((label.split('/')[0] || label).trim());
                html += `<div class="badge bg-secondary text-wrap me-1 mb-1">${safeLabel}: ${accuracy}${openNotes}</div>`;
            });
            html += '</div>';
        }

        if (profileSummary) {
            html += `<div class="mt-2"><small class="text-muted">Profile:</small><div>${escapeHtml(profileSummary)}</div></div>`;
        }

        html += '</div>';
        return html;
    }
    
    // Public Profile Management
    async function loadPublicProfileStatus() {
        const token = localStorage.getItem('api_token');
        if (!token) return;

        try {
            const res = await fetch('/api/candidate/public-profile', {
                headers: {
                    'Accept': 'application/json',
                    'Authorization': 'Bearer ' + token,
                }
            });

            const el = document.getElementById('public-profile-status');
            if (!el) return;

            if (!res.ok) {
                el.innerHTML = '<p class="text-danger small">Unable to load shareable link status.</p>';
                return;
            }

            const data = await res.json();
            if (data.active && data.url) {
                el.innerHTML = `
                    <div class="mb-2">
                        <p class="small mb-1"><strong>Link active:</strong></p>
                        <div class="input-group input-group-sm">
                            <input type="text" class="form-control" value="${data.url}" readonly 
                                   id="profile-link-input" onclick="this.select();">
                            <button class="btn btn-outline-secondary" type="button" onclick="copyProfileLink()">
                                Copy
                            </button>
                        </div>
                        <p class="text-muted small mt-1 mb-0">
                            Click "Copy" or select the link above to share it with recruiters.
                        </p>
                    </div>
                `;
            } else {
                el.innerHTML = '<p class="text-muted small">No active link. Click "Generate / Regenerate Link" to create one.</p>';
            }
        } catch (error) {
            console.error('Error loading public profile status:', error);
        }
    }

    function copyProfileLink() {
        const input = document.getElementById('profile-link-input');
        if (input) {
            input.select();
            document.execCommand('copy');
            const btn = event.target;
            const originalText = btn.textContent;
            btn.textContent = 'Copied!';
            setTimeout(() => {
                btn.textContent = originalText;
            }, 2000);
        }
    }

    async function generatePublicProfile() {
        const token = localStorage.getItem('api_token');
        if (!token) {
            alert('You are not logged in.');
            return;
        }

        try {
            const res = await fetch('/api/candidate/public-profile/generate', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + token,
                },
            });

            if (!res.ok) {
                alert('Unable to generate link.');
                return;
            }

            await loadPublicProfileStatus();
        } catch (error) {
            console.error('Error generating public profile:', error);
            alert('An error occurred while generating the link.');
        }
    }

    async function disablePublicProfile() {
        const token = localStorage.getItem('api_token');
        if (!token) {
            alert('You are not logged in.');
            return;
        }

        if (!confirm('Are you sure you want to disable your public profile link? It will no longer be accessible.')) {
            return;
        }

        try {
            const res = await fetch('/api/candidate/public-profile/disable', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + token,
                },
            });

            if (!res.ok) {
                alert('Unable to disable link.');
                return;
            }

            await loadPublicProfileStatus();
        } catch (error) {
            console.error('Error disabling public profile:', error);
            alert('An error occurred while disabling the link.');
        }
    }
    
    // Load forms on page load
    loadForms();
    
    // Load public profile status on page load
    loadPublicProfileStatus();
</script>
@endsection

