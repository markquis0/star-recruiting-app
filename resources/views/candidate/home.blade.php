@extends('layouts.app')

@section('title', 'Candidate Dashboard')

@section('content')
<div class="row px-3 px-lg-5 mt-4 mt-lg-5">
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
    const token = localStorage.getItem('api_token');
    const userRole = localStorage.getItem('user_role');

    if (!token) {
        window.location.href = '/login';
    }

    if (userRole !== 'candidate') {
        window.location.href = '/recruiter/home';
    }

    function escapeHtml(value) {
        return value
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
        } catch (error) {
            console.error('Error:', error);
            document.getElementById('forms-container').innerHTML = '<p class="text-danger">Error loading forms. Please login again.</p>';
        }
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
                if (formType === 'project') {
                    window.location.href = `/candidate/form/${data.form.id}`;
                } else {
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
    
    // Load forms on page load
    loadForms();
</script>
@endsection

