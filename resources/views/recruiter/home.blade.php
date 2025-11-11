@extends('layouts.app')

@section('title', 'Recruiter Dashboard')

@section('content')
<div class="row px-3 px-lg-5">
    <div class="col-12">
        <h2>Recruiter Dashboard</h2>
        
        <div class="card mb-4">
            <div class="card-header">
                <h5>Search Candidates</h5>
            </div>
            <div class="card-body">
                <form id="search-form">
                    <div class="row">
                        <div class="col-md-3">
                            <input type="text" class="form-control" name="role" placeholder="Role Title">
                        </div>
                        <div class="col-md-3">
                            <input type="number" class="form-control" name="years_exp" placeholder="Min Years Experience" min="0">
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" name="behavioral_category">
                                <option value="">All Behavioral Categories</option>
                                <option value="Analytical">Analytical</option>
                                <option value="Collaborative">Collaborative</option>
                                <option value="Persistent">Persistent</option>
                                <option value="Social">Social</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" name="aptitude_category">
                                <option value="">All Aptitude Categories</option>
                                <option value="High Aptitude">High Aptitude</option>
                                <option value="Moderate Aptitude">Moderate Aptitude</option>
                                <option value="Low Aptitude">Low Aptitude</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary mt-3">Search</button>
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
                                        <a href="/recruiter/candidate/${candidate.id}" class="btn btn-sm btn-primary me-2">View Profile</a>
                                        <button class="btn btn-sm btn-danger" onclick="removeSavedCandidate(${saved.id})">Remove</button>
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
    document.getElementById('search-form').addEventListener('submit', function(e) {
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
                    alert('Years of experience must be a non-negative number.');
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
                                        <a href="/recruiter/candidate/${candidate.id}" class="btn btn-sm btn-primary me-2">View Profile</a>
                                        <button class="btn btn-sm btn-success" onclick="saveCandidate(${candidate.id})">Save Candidate</button>
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
    
    function saveCandidate(candidateId) {
        fetch('/api/recruiter/save', {
            method: 'POST',
            headers: {
                'Authorization': 'Bearer ' + token,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ candidate_id: candidateId })
        })
        .then(response => {
            if (response.ok) {
                return response.json().then(data => {
                    alert('Candidate saved successfully!');
                    loadSavedCandidates(); // Refresh saved candidates list
                });
            } else {
                return response.json().then(data => {
                    alert(data.message || 'Error saving candidate');
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error saving candidate: ' + error.message);
        });
    }
    
    function removeSavedCandidate(savedCandidateId) {
        if (!confirm('Are you sure you want to remove this candidate from your saved list?')) {
            return;
        }
        
        fetch(`/api/recruiter/save/${savedCandidateId}`, {
            method: 'DELETE',
            headers: {
                'Authorization': 'Bearer ' + token,
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (response.ok) {
                return response.json().then(data => {
                    alert('Candidate removed from saved list.');
                    loadSavedCandidates(); // Refresh saved candidates list
                });
            } else {
                return response.json().then(data => {
                    alert(data.message || 'Error removing candidate');
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error removing candidate: ' + error.message);
        });
    }
    
    loadSavedCandidates();
</script>
@endsection

