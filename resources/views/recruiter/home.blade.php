@extends('layouts.app')

@section('title', 'Recruiter Dashboard')

@section('content')
<div class="row px-3 px-lg-5 mt-4 mt-lg-5">
    <div class="col-12">
        <h2>Recruiter Dashboard</h2>
        
        <div class="card mb-4">
            <div class="card-header">
                <h5>Search Candidates</h5>
            </div>
            <div class="card-body">
                <form id="search-form">
                    <div class="row g-2">
                        <div class="col-12 col-md-3">
                            <input type="text" class="form-control" name="role" placeholder="Role Title">
                        </div>
                        <div class="col-6 col-md-2">
                            <input type="number" class="form-control" name="years_exp" placeholder="Min Years" min="0">
                        </div>
                        <div class="col-6 col-md-3">
                            <select class="form-select" name="behavioral_category">
                                <option value="">All Behavioral Categories</option>
                                <option value="Analytical">Analytical</option>
                                <option value="Collaborative">Collaborative</option>
                                <option value="Persistent">Persistent</option>
                                <option value="Social">Social</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-3">
                            <select class="form-select" name="aptitude_category">
                                <option value="">All Aptitude Categories</option>
                                <option value="High Aptitude">High Aptitude</option>
                                <option value="Moderate Aptitude">Moderate Aptitude</option>
                                <option value="Low Aptitude">Low Aptitude</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-1 d-grid">
                            <button type="submit" class="btn btn-primary btn-touch w-100">Search</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card mb-4" id="search-results-card" style="display: none;">
            <div class="card-header">
                <h5>Search Results</h5>
            </div>
            <div class="card-body">
                <div id="search-results-container">
                    <p>No results found.</p>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5>Saved Candidates</h5>
            </div>
            <div class="card-body">
                <div id="saved-candidates-container">
                    <p>Loading saved candidates...</p>
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
    
    if (userRole !== 'recruiter') {
        window.location.href = '/candidate/home';
    }
    
    // Load saved candidates
    function loadSavedCandidates() {
        fetch('/api/recruiter/home', {
            headers: {
                'Authorization': 'Bearer ' + token,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('saved-candidates-container');
            if (data.saved_candidates && data.saved_candidates.length > 0) {
                container.innerHTML = data.saved_candidates.map(saved => {
                    const candidate = saved.candidate;
                    return `
                        <div class="card mb-3">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h5>${candidate.first_name} ${candidate.last_name}</h5>
                                        <p>Role: ${candidate.role_title || 'N/A'}</p>
                                        <p>Experience: ${candidate.years_exp || 0} years</p>
                                    </div>
                                    <div>
                                        <a href="/recruiter/candidate/${candidate.id}" class="btn btn-sm btn-primary me-2 btn-touch">View Profile</a>
                                        <button class="btn btn-sm btn-danger btn-touch" onclick="removeSavedCandidate(${saved.id})">Remove</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                }).join('');
            } else {
                container.innerHTML = '<p>No saved candidates yet.</p>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }
    
    // Search form handler
    document.getElementById('search-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const params = new URLSearchParams(formData);
        
        // Remove empty parameters and validate years_exp
        for (const [key, value] of params.entries()) {
            if (!value || value.trim() === '') {
                params.delete(key);
            } else if (key === 'years_exp') {
                // Ensure years_exp is non-negative
                const yearsExp = parseInt(value);
                if (isNaN(yearsExp) || yearsExp < 0) {
                    await showAppModal({
                        title: 'Invalid Input',
                        message: 'Years of experience must be a non-negative number.',
                        variant: 'warning'
                    });
                    return;
                }
            }
        }
        
        const searchResultsCard = document.getElementById('search-results-card');
        const searchResultsContainer = document.getElementById('search-results-container');
        
        // Show loading state
        searchResultsCard.style.display = 'block';
        searchResultsContainer.innerHTML = '<p>Searching...</p>';
        
        const searchUrl = '/api/recruiter/search' + (params.toString() ? '?' + params.toString() : '');
        console.log('Search URL:', searchUrl); // Debug log
        
        fetch(searchUrl, {
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
            if (!response.ok) {
                return response.json().then(err => {
                    throw new Error(err.message || 'Search failed');
                });
            }
            return response.json();
        })
        .then(data => {
            console.log('Search results:', data); // Debug log

            // Track recruiter search with Mixpanel (if available)
            if (typeof trackEvent === 'function' && data.candidates) {
                trackEvent('Recruiter Search Performed', {
                    role: params.get('role') || null,
                    years_exp: params.get('years_exp') ? parseInt(params.get('years_exp'), 10) : null,
                    behavioral_category: params.get('behavioral_category') || null,
                    aptitude_category: params.get('aptitude_category') || null,
                    results_count: data.candidates.length,
                });
            }

            if (data.candidates && data.candidates.length > 0) {
                searchResultsContainer.innerHTML = data.candidates.map(candidate => {
                    const user = candidate.user || {};
                    const forms = candidate.forms || [];
                    const behavioralAssessment = forms.find(f => f.form_type === 'behavioral' && f.assessment);
                    const aptitudeAssessment = forms.find(f => f.form_type === 'aptitude' && f.assessment);
                    
                    return `
                        <div class="card mb-3">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h5>${candidate.first_name} ${candidate.last_name}</h5>
                                        <p class="mb-1"><strong>Username:</strong> ${user.username || 'N/A'}</p>
                                        <p class="mb-1"><strong>Role Title:</strong> ${candidate.role_title || 'N/A'}</p>
                                        <p class="mb-1"><strong>Years of Experience:</strong> ${candidate.years_exp || 0}</p>
                                        ${behavioralAssessment ? `<p class="mb-1"><strong>Behavioral Category:</strong> ${behavioralAssessment.assessment.category || 'N/A'}</p>` : ''}
                                        ${aptitudeAssessment ? `<p class="mb-1"><strong>Aptitude Category:</strong> ${aptitudeAssessment.assessment.category || 'N/A'} (Score: ${aptitudeAssessment.assessment.total_score || 0}%)</p>` : ''}
                                    </div>
                                    <div>
                                        <a href="/recruiter/candidate/${candidate.id}" class="btn btn-sm btn-primary me-2 btn-touch">View Profile</a>
                                        <button class="btn btn-sm btn-success btn-touch" onclick="saveCandidate(${candidate.id})">Save Candidate</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                }).join('');
            } else {
                searchResultsContainer.innerHTML = '<p class="text-muted">No candidates found matching your search criteria.</p>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            searchResultsContainer.innerHTML = '<p class="text-danger">Error performing search. Please try again.</p>';
        });
    });
    
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

                // Track candidate save with Mixpanel (if available)
                if (typeof trackEvent === 'function') {
                    trackEvent('Candidate Saved', {
                        candidateId: candidateId,
                        context: 'recruiter_dashboard',
                    });
                }

                await showAppModal({
                    title: 'Candidate Saved',
                    message: 'Candidate saved to your list successfully.',
                    variant: 'success'
                });
                loadSavedCandidates();
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
    
    async function removeSavedCandidate(savedCandidateId) {
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
            const response = await fetch(`/api/recruiter/save/${savedCandidateId}`, {
                method: 'DELETE',
                headers: {
                    'Authorization': 'Bearer ' + token,
                    'Accept': 'application/json'
                }
            });

            if (response.ok) {
                await response.json();

                // Track candidate removal with Mixpanel (if available)
                if (typeof trackEvent === 'function') {
                    trackEvent('Candidate Removed From Saved', {
                        savedCandidateId: savedCandidateId,
                        context: 'recruiter_dashboard',
                    });
                }

                await showAppModal({
                    title: 'Candidate Removed',
                    message: 'Candidate removed from your saved list.',
                    variant: 'success'
                });
                loadSavedCandidates();
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

    // Track recruiter dashboard view (candidate list view) on load
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof trackEvent === 'function') {
            trackEvent('Recruiter Dashboard Viewed', {});
        }
    });
    
    loadSavedCandidates();
</script>
@endsection

