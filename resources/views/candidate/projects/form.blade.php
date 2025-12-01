@extends('layouts.app')

@section('title', isset($projectId) ? 'Edit Project' : 'Add a Project')

@section('content')
<div class="row px-3 px-lg-5 mt-4 mt-lg-5">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h2>{{ isset($projectId) ? 'Edit Project' : 'Add a Project' }}</h2>
                <p class="text-muted mb-0">Share a project that demonstrates your experience and impact.</p>
            </div>
            <a href="/candidate/projects" class="btn btn-sm btn-secondary">Back to Projects</a>
        </div>

        <div class="mb-3">
            <small class="text-muted">Step <span id="step-number">1</span> of <span id="step-total">6</span></small>
        </div>

        <div class="card">
            <div class="card-body">
                <form id="project-form">
                    {{-- STEP 1: Overview --}}
                    <div class="step step-1">
                        <div class="mb-3">
                            <label for="project_title" class="form-label">Project Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="project_title" name="project_title" required>
                        </div>

                        <div class="mb-3">
                            <label for="company" class="form-label">Company / Organization</label>
                            <input type="text" class="form-control" id="company" name="company">
                        </div>

                        <div class="mb-3">
                            <label for="role_title" class="form-label">Your Role / Position</label>
                            <input type="text" class="form-control" id="role_title" name="role_title" placeholder="e.g., Product Manager, Lead Developer">
                        </div>

                        <div class="mb-3">
                            <label for="timeframe" class="form-label">Timeframe</label>
                            <input type="text" class="form-control" id="timeframe" name="timeframe" placeholder="e.g., Jan 2023 – Jun 2023">
                        </div>

                        <div class="mb-3">
                            <label for="summary_one_liner" class="form-label">One-sentence summary</label>
                            <textarea class="form-control" id="summary_one_liner" name="summary_one_liner" rows="2" maxlength="255"></textarea>
                            <small class="text-muted">Example: Reduced onboarding time by simplifying the signup flow.</small>
                        </div>
                    </div>

                    {{-- STEP 2: Context & Goals --}}
                    <div class="step step-2" style="display:none;">
                        <div class="mb-3">
                            <label for="context" class="form-label">Context / Background</label>
                            <textarea class="form-control" id="context" name="context" rows="4"></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="problem" class="form-label">Problem or Opportunity</label>
                            <textarea class="form-control" id="problem" name="problem" rows="4"></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="affected_audience" class="form-label">Affected Audience</label>
                            <textarea class="form-control" id="affected_audience" name="affected_audience" rows="3"></textarea>
                            <small class="text-muted">Example: New customers, support agents, warehouse staff, sales team, etc.</small>
                        </div>

                        <div class="mb-3">
                            <label for="primary_goal" class="form-label">Primary Goal</label>
                            <textarea class="form-control" id="primary_goal" name="primary_goal" rows="3"></textarea>
                        </div>
                    </div>

                    {{-- STEP 3: Metrics --}}
                    <div class="step step-3" style="display:none;">
                        <p class="text-muted mb-3">
                            Add up to 3 metrics that show the impact of this project. These can be about time, quality, revenue, satisfaction, efficiency, etc.
                        </p>

                        <div id="metrics-container" class="mb-3">
                            {{-- Metrics will be added here by JavaScript --}}
                        </div>

                        <button type="button" id="add-metric-btn" class="btn btn-secondary btn-sm">
                            + Add another metric
                        </button>
                    </div>

                    {{-- STEP 4: Responsibilities & Collaboration --}}
                    <div class="step step-4" style="display:none;">
                        <div class="mb-3">
                            <label for="responsibilities" class="form-label">Your responsibilities</label>
                            <textarea class="form-control" id="responsibilities" name="responsibilities" rows="4"></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="project_type" class="form-label">Project type</label>
                            <select class="form-select" id="project_type" name="project_type">
                                <option value="">Select one</option>
                                <option value="individual">Individual contributor project</option>
                                <option value="led_project">I led this project</option>
                                <option value="team_contributor">I contributed as part of a team</option>
                                <option value="other">Other</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="teams_involved_input" class="form-label">Teams / departments involved</label>
                            <input type="text" class="form-control" id="teams_involved_input" name="teams_involved_input" placeholder="e.g., Sales, Engineering, Support">
                            <small class="text-muted">Separate multiple teams with commas.</small>
                        </div>

                        <div class="mb-3">
                            <label for="collaboration_example" class="form-label">Collaboration example</label>
                            <textarea class="form-control" id="collaboration_example" name="collaboration_example" rows="4"></textarea>
                        </div>
                    </div>

                    {{-- STEP 5: Challenges & Outcome --}}
                    <div class="step step-5" style="display:none;">
                        <div class="mb-3">
                            <label for="challenges" class="form-label">Biggest challenges</label>
                            <textarea class="form-control" id="challenges" name="challenges" rows="4"></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="challenge_response" class="form-label">How you handled them</label>
                            <textarea class="form-control" id="challenge_response" name="challenge_response" rows="4"></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="tradeoffs" class="form-label">Tradeoffs / key decisions</label>
                            <textarea class="form-control" id="tradeoffs" name="tradeoffs" rows="4"></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="outcome_summary" class="form-label">Outcome summary</label>
                            <textarea class="form-control" id="outcome_summary" name="outcome_summary" rows="4"></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="impact" class="form-label">Impact on others</label>
                            <textarea class="form-control" id="impact" name="impact" rows="4"></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="recognition" class="form-label">Recognition or follow-up</label>
                            <textarea class="form-control" id="recognition" name="recognition" rows="3"></textarea>
                        </div>
                    </div>

                    {{-- STEP 6: Reflection & Review --}}
                    <div class="step step-6" style="display:none;">
                        <div class="mb-3">
                            <label for="learning" class="form-label">What you learned</label>
                            <textarea class="form-control" id="learning" name="learning" rows="4"></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="retro" class="form-label">What you'd do differently</label>
                            <textarea class="form-control" id="retro" name="retro" rows="4"></textarea>
                        </div>

                        <div class="alert alert-info">
                            <small>When you submit, this project will be visible to recruiters as part of your profile.</small>
                        </div>
                    </div>

                    <div id="form-error" class="alert alert-danger" style="display: none;"></div>
                    <div id="form-success" class="alert alert-success" style="display: none;"></div>
                </form>

                <div class="mt-4 d-flex justify-content-between">
                    <button type="button" id="prev-btn" class="btn btn-secondary" disabled>Back</button>
                    <div>
                        <button type="button" id="next-btn" class="btn btn-primary">Next</button>
                        <button type="button" id="submit-btn" class="btn btn-primary" style="display:none;">
                            {{ isset($projectId) ? 'Update Project' : 'Submit Project' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const token = localStorage.getItem('api_token');
    const userRole = localStorage.getItem('user_role');
    const projectId = @json($projectId ?? null);
    
    if (!token) {
        window.location.href = '/login';
    }
    
    if (userRole !== 'candidate') {
        window.location.href = '/recruiter/home';
    }

    const totalSteps = 6;
    let currentStep = 1;
    let existingProject = null;

    // Load existing project data if editing
    async function loadProjectData() {
        if (!projectId) return;

        try {
            const response = await fetch(`/api/candidate/projects/${projectId}`, {
                headers: {
                    'Authorization': 'Bearer ' + token,
                    'Accept': 'application/json'
                }
            });

            if (response.ok) {
                const result = await response.json();
                existingProject = result.project;
                populateForm(existingProject.data || {});
            } else if (response.status === 404) {
                // Project not found, redirect to projects list
                window.location.href = '/candidate/projects';
            }
        } catch (error) {
            console.error('Error loading project:', error);
        }
    }

    function populateForm(data) {
        if (data.project_title) document.getElementById('project_title').value = data.project_title;
        if (data.company) document.getElementById('company').value = data.company;
        if (data.role_title) document.getElementById('role_title').value = data.role_title;
        if (data.timeframe) document.getElementById('timeframe').value = data.timeframe;
        if (data.summary_one_liner) document.getElementById('summary_one_liner').value = data.summary_one_liner;
        if (data.context) document.getElementById('context').value = data.context;
        if (data.problem) document.getElementById('problem').value = data.problem;
        if (data.affected_audience) document.getElementById('affected_audience').value = data.affected_audience;
        if (data.primary_goal) document.getElementById('primary_goal').value = data.primary_goal;
        if (data.responsibilities) document.getElementById('responsibilities').value = data.responsibilities;
        if (data.project_type) document.getElementById('project_type').value = data.project_type;
        if (data.teams_involved) {
            document.getElementById('teams_involved_input').value = Array.isArray(data.teams_involved) 
                ? data.teams_involved.join(', ') 
                : data.teams_involved;
        }
        if (data.collaboration_example) document.getElementById('collaboration_example').value = data.collaboration_example;
        if (data.challenges) document.getElementById('challenges').value = data.challenges;
        if (data.challenge_response) document.getElementById('challenge_response').value = data.challenge_response;
        if (data.tradeoffs) document.getElementById('tradeoffs').value = data.tradeoffs;
        if (data.outcome_summary) document.getElementById('outcome_summary').value = data.outcome_summary;
        if (data.impact) document.getElementById('impact').value = data.impact;
        if (data.recognition) document.getElementById('recognition').value = data.recognition;
        if (data.learning) document.getElementById('learning').value = data.learning;
        if (data.retro) document.getElementById('retro').value = data.retro;

        // Populate metrics
        if (data.metrics && data.metrics.length > 0) {
            data.metrics.forEach(metric => createMetricRow(metric));
        } else {
            createMetricRow(); // Start with one empty metric
        }
    }

    function showStep(step) {
        document.querySelectorAll('.step').forEach(s => s.style.display = 'none');
        document.querySelector('.step-' + step).style.display = 'block';

        document.getElementById('step-number').textContent = step;
        document.getElementById('step-total').textContent = totalSteps;

        document.getElementById('prev-btn').disabled = (step === 1);
        document.getElementById('next-btn').style.display = (step === totalSteps) ? 'none' : 'inline-block';
        document.getElementById('submit-btn').style.display = (step === totalSteps) ? 'inline-block' : 'none';
    }

    document.getElementById('next-btn').addEventListener('click', function () {
        // Validate required fields on step 1
        if (currentStep === 1) {
            const projectTitle = document.getElementById('project_title').value.trim();
            if (!projectTitle) {
                document.getElementById('form-error').textContent = 'Project Title is required.';
                document.getElementById('form-error').style.display = 'block';
                return;
            }
        }

        if (currentStep < totalSteps) {
            currentStep++;
            showStep(currentStep);
            document.getElementById('form-error').style.display = 'none';
        }
    });

    document.getElementById('prev-btn').addEventListener('click', function () {
        if (currentStep > 1) {
            currentStep--;
            showStep(currentStep);
            document.getElementById('form-error').style.display = 'none';
        }
    });

    // Metrics UI
    const metricsContainer = document.getElementById('metrics-container');
    const addMetricBtn = document.getElementById('add-metric-btn');
    const maxMetrics = 3;

    function createMetricRow(metric = {}) {
        const idx = metricsContainer.children.length;
        if (idx >= maxMetrics) return;

        const wrapper = document.createElement('div');
        wrapper.className = 'border rounded p-3 mb-3 metric-row';

        wrapper.innerHTML = `
            <div class="mb-2">
                <label class="form-label small">Metric name</label>
                <input type="text" class="form-control form-control-sm metric-name" value="${metric.metric_name || ''}" placeholder="e.g., Support tickets per week">
            </div>
            <div class="row mb-2">
                <div class="col-md-6">
                    <label class="form-label small">Baseline</label>
                    <input type="text" class="form-control form-control-sm metric-baseline" value="${metric.baseline_value || ''}">
                </div>
                <div class="col-md-6">
                    <label class="form-label small">Target</label>
                    <input type="text" class="form-control form-control-sm metric-target" value="${metric.target_value || ''}">
                </div>
            </div>
            <div class="row mb-2">
                <div class="col-md-6">
                    <label class="form-label small">Final</label>
                    <input type="text" class="form-control form-control-sm metric-final" value="${metric.final_value || ''}">
                </div>
                <div class="col-md-6">
                    <label class="form-label small">Timeframe</label>
                    <input type="text" class="form-control form-control-sm metric-timeframe" value="${metric.timeframe || ''}" placeholder="e.g., 3 months after launch">
                </div>
            </div>
            <button type="button" class="btn btn-sm btn-link text-danger p-0 remove-metric-btn">Remove</button>
        `;

        wrapper.querySelector('.remove-metric-btn').addEventListener('click', function () {
            wrapper.remove();
            updateAddMetricButton();
        });

        metricsContainer.appendChild(wrapper);
        updateAddMetricButton();
    }

    function updateAddMetricButton() {
        const currentCount = metricsContainer.children.length;
        addMetricBtn.disabled = currentCount >= maxMetrics;
        if (currentCount >= maxMetrics) {
            addMetricBtn.textContent = 'Maximum 3 metrics';
        } else {
            addMetricBtn.textContent = '+ Add another metric';
        }
    }

    addMetricBtn.addEventListener('click', function () {
        createMetricRow();
    });

    // Initialize metrics if not editing
    if (!projectId) {
        createMetricRow();
    }

    // Submit handler
    document.getElementById('submit-btn').addEventListener('click', async function () {
        const payload = collectProjectFormData();

        const url = projectId 
            ? `/api/candidate/projects/${projectId}`
            : '/api/candidate/projects';
        const method = projectId ? 'PUT' : 'POST';

        try {
            const response = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'Authorization': 'Bearer ' + token
                },
                body: JSON.stringify(payload)
            });

            const result = await response.json();

            if (response.ok) {
                document.getElementById('form-error').style.display = 'none';
                document.getElementById('form-success').textContent = 'Project saved successfully! Redirecting...';
                document.getElementById('form-success').style.display = 'block';
                
                setTimeout(() => {
                    window.location.href = '/candidate/projects';
                }, 1500);
            } else {
                document.getElementById('form-success').style.display = 'none';
                let errorMessage = result.message || 'An error occurred';
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
            document.getElementById('form-error').textContent = 'An error occurred: ' + error.message;
            document.getElementById('form-error').style.display = 'block';
        }
    });

    function collectProjectFormData() {
        const metrics = [];
        metricsContainer.querySelectorAll('.metric-row').forEach(row => {
            const metricName = row.querySelector('.metric-name').value.trim();
            const baseline = row.querySelector('.metric-baseline').value.trim();
            const target = row.querySelector('.metric-target').value.trim();
            const finalVal = row.querySelector('.metric-final').value.trim();
            const timeframe = row.querySelector('.metric-timeframe').value.trim();

            if (metricName || baseline || target || finalVal || timeframe) {
                metrics.push({
                    metric_name: metricName || null,
                    baseline_value: baseline || null,
                    target_value: target || null,
                    final_value: finalVal || null,
                    timeframe: timeframe || null,
                });
            }
        });

        // Teams: split on commas
        const teamsRaw = document.getElementById('teams_involved_input').value;
        const teams = teamsRaw
            ? teamsRaw.split(',').map(t => t.trim()).filter(t => t.length > 0)
            : [];

        return {
            project_title: document.getElementById('project_title').value.trim(),
            company: document.getElementById('company').value.trim() || null,
            role_title: document.getElementById('role_title').value.trim() || null,
            timeframe: document.getElementById('timeframe').value.trim() || null,
            summary_one_liner: document.getElementById('summary_one_liner').value.trim() || null,
            context: document.getElementById('context').value.trim() || null,
            problem: document.getElementById('problem').value.trim() || null,
            affected_audience: document.getElementById('affected_audience').value.trim() || null,
            primary_goal: document.getElementById('primary_goal').value.trim() || null,
            metrics: metrics.length > 0 ? metrics : null,
            responsibilities: document.getElementById('responsibilities').value.trim() || null,
            project_type: document.getElementById('project_type').value || null,
            teams_involved: teams.length > 0 ? teams : null,
            collaboration_example: document.getElementById('collaboration_example').value.trim() || null,
            challenges: document.getElementById('challenges').value.trim() || null,
            challenge_response: document.getElementById('challenge_response').value.trim() || null,
            tradeoffs: document.getElementById('tradeoffs').value.trim() || null,
            outcome_summary: document.getElementById('outcome_summary').value.trim() || null,
            impact: document.getElementById('impact').value.trim() || null,
            recognition: document.getElementById('recognition').value.trim() || null,
            learning: document.getElementById('learning').value.trim() || null,
            retro: document.getElementById('retro').value.trim() || null,
        };
    }

    // Initialize
    loadProjectData().then(() => {
        showStep(currentStep);
    });
</script>
@endsection

