@extends('layouts.app')

@section('title', 'My Projects')

@section('content')
<div class="row px-3 px-lg-5 mt-4 mt-lg-5">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Your Projects</h2>
            <a href="/candidate/projects/new" class="btn btn-primary">
                + Add Project
            </a>
        </div>

        <div id="projects-container">
            <p>Loading projects...</p>
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

    async function loadProjects() {
        try {
            const response = await fetch('/api/candidate/projects', {
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
                throw new Error('Failed to load projects');
            }

            const data = await response.json();
            const projects = data.projects || [];
            const container = document.getElementById('projects-container');

            if (projects.length === 0) {
                container.innerHTML = `
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <p class="text-muted mb-3">You haven't added any projects yet.</p>
                            <a href="/candidate/projects/new" class="btn btn-primary">Add Your First Project</a>
                        </div>
                    </div>
                `;
                return;
            }

            container.innerHTML = projects.map(form => {
                const data = form.data || {};
                const projectTitle = data.project_title || 'Untitled Project';
                const roleTitle = data.role_title || '';
                const company = data.company || '';
                const timeframe = data.timeframe || '';
                const summary = data.summary_one_liner || '';
                const metrics = data.metrics || [];

                let roleCompanyText = '';
                if (roleTitle && company) {
                    roleCompanyText = `${roleTitle} · ${company}`;
                } else if (roleTitle) {
                    roleCompanyText = roleTitle;
                } else if (company) {
                    roleCompanyText = company;
                }

                let metricsHtml = '';
                if (metrics.length > 0) {
                    metricsHtml = `
                        <div class="mt-3">
                            <p class="text-xs font-semibold text-muted text-uppercase mb-2">Key Metrics</p>
                            <ul class="list-unstyled mb-0">
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
                                    
                                    return `<li class="text-sm text-muted mb-1">${escapeHtml(metricText)}</li>`;
                                }).join('')}
                            </ul>
                        </div>
                    `;
                }

                return `
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <h5 class="mb-2" style="color: #FF3B6B;">${escapeHtml(projectTitle)}</h5>
                                    ${roleCompanyText ? `<p class="text-muted mb-1 small">${escapeHtml(roleCompanyText)}</p>` : ''}
                                    ${timeframe ? `<p class="text-muted mb-2 small"><small>${escapeHtml(timeframe)}</small></p>` : ''}
                                    ${summary ? `<p class="mb-2">${escapeHtml(summary)}</p>` : ''}
                                    ${metricsHtml}
                                </div>
                                <div class="text-end ms-3">
                                    <a href="/candidate/projects/${form.id}/edit" class="btn btn-sm btn-primary mb-2 d-block">Edit</a>
                                    <p class="text-muted small mb-0" style="font-size: 0.75rem;">
                                        Updated ${new Date(form.updated_at).toLocaleDateString()}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
        } catch (error) {
            console.error('Error:', error);
            document.getElementById('projects-container').innerHTML = 
                '<p class="text-danger">Error loading projects. Please try again.</p>';
        }
    }

    loadProjects();
</script>
@endsection

