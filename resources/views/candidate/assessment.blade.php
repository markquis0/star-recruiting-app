@extends('layouts.app')

@section('title', 'Take Assessment')

@section('content')
<div class="row px-3 px-lg-5 mt-4 mt-lg-5">
    <div class="col-12">
        <h2 id="assessment-title">Assessment</h2>
        <div class="card">
            <div class="card-body">
                <form id="assessment-form">
                    <div id="questions-container">
                        <p>Loading questions...</p>
                    </div>
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary btn-lg btn-touch">Submit Assessment</button>
                        <a href="/candidate/home" class="btn btn-secondary btn-lg ms-2 btn-touch">Cancel</a>
                    </div>
                </form>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
    const token = localStorage.getItem('api_token');
    const urlParams = new URLSearchParams(window.location.search);
    const formId = window.location.pathname.split('/').pop();
    const assessmentType = urlParams.get('type');
    
    if (!token) {
        window.location.href = '/login';
    }

    function escapeHtml(value) {
        return value
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }
    
    // Load questions for this assessment type
    async function loadQuestions() {
        try {
            const container = document.getElementById('questions-container');
            const title = document.getElementById('assessment-title');
            
            // Fetch questions from API
            const response = await fetch(`/api/candidate/questions/${assessmentType}`, {
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
                const errorText = await response.text();
                console.error('API Error:', response.status, errorText);
                container.innerHTML = `<p class="text-danger">Error loading questions (${response.status}). Please try again.</p>`;
                return;
            }
            
            const result = await response.json();
            console.log('Questions loaded:', result.questions ? result.questions.length : 0);
            
            if (!result.questions || result.questions.length === 0) {
                container.innerHTML = `<p class="text-danger">No questions available for this assessment. ${result.message || ''}</p><p class="text-muted">Please contact support or try refreshing the page.</p>`;
                console.error('API Response:', result);
                return;
            }
            
            if (assessmentType === 'behavioral') {
                title.textContent = 'Behavioral Assessment';
                container.innerHTML = `
                    <p class="lead">Rate yourself on a scale of 1-5 for each statement (1 = Strongly Disagree, 5 = Strongly Agree)</p>
                    ${generateBehavioralQuestions(result.questions)}
                `;
            } else if (assessmentType === 'aptitude') {
                title.textContent = 'Aptitude Test';
                container.innerHTML = `
                    <p class="lead">Answer each question below. The assessment is grouped into thinking style categories and includes both multiple-choice and open-ended prompts.</p>
                    ${generateAptitudeQuestions(result.questions)}
                `;
            }
        } catch (error) {
            console.error('Error:', error);
            document.getElementById('questions-container').innerHTML = '<p class="text-danger">Error loading questions</p>';
        }
    }
    
    function generateBehavioralQuestions(questions) {
        return questions.map((question, index) => `
            <div class="mb-4">
                <label class="form-label">${index + 1}. ${escapeHtml(question.question_text)}</label>
                <select class="form-select" name="answers[${index}][answer]" required>
                    <option value="">Select rating...</option>
                    <option value="1">1 - Strongly Disagree</option>
                    <option value="2">2 - Disagree</option>
                    <option value="3">3 - Neutral</option>
                    <option value="4">4 - Agree</option>
                    <option value="5">5 - Strongly Agree</option>
                </select>
                <input type="hidden" name="answers[${index}][question_id]" value="${question.id}">
            </div>
        `).join('');
    }
    
    function generateAptitudeQuestions(questions) {
        if (!Array.isArray(questions) || questions.length === 0) {
            return '';
        }

        const categoryLabels = {
            'logic_reasoning': 'Category 1: Logic & Reasoning',
            'conceptual_strategic': 'Category 2: Conceptual & Strategic Thinking',
            'decision_prioritization': 'Category 3: Decision-Making & Prioritization',
            'people_insight': 'Category 4: People Insight & Communication',
            'general': 'General Aptitude'
        };

        const ordering = ['logic_reasoning', 'conceptual_strategic', 'decision_prioritization', 'people_insight', 'general'];
        const grouped = {};

        questions.forEach(question => {
            const key = question.trait || 'general';
            if (!grouped[key]) {
                grouped[key] = [];
            }
            grouped[key].push(question);
        });

        let questionIndex = 0;
        let html = '';

        ordering.forEach(category => {
            const items = grouped[category];
            if (!items || items.length === 0) {
                return;
            }

            html += `
                <div class="mt-4">
                    <h4 class="text-info">${escapeHtml(categoryLabels[category] || (category.charAt(0).toUpperCase() + category.slice(1)))}</h4>
                </div>
            `;

            items.forEach(question => {
                const currentIndex = questionIndex;
                questionIndex += 1;

                if (question.question_type === 'open_ended') {
                    html += `
                        <div class="mb-4">
                            <label class="form-label">${currentIndex + 1}. ${escapeHtml(question.question_text)}</label>
                            <textarea class="form-control" name="answers[${currentIndex}][answer]" rows="4" placeholder="Type your response" required></textarea>
                            <input type="hidden" name="answers[${currentIndex}][question_id]" value="${question.id}">
                        </div>
                    `;
                } else {
                    let options = question.options || [];
                    if (typeof options === 'string') {
                        try {
                            options = JSON.parse(options);
                        } catch (e) {
                            options = [];
                        }
                    }

                    let optionEntries = [];
                    if (Array.isArray(options)) {
                        optionEntries = options.map((opt, optIndex) => {
                            const letter = String.fromCharCode(65 + optIndex);
                            const optionText = opt.includes(':') || opt.includes(')') ? opt : `${letter}: ${opt}`;
                            return [letter, optionText];
                        });
                    } else {
                        optionEntries = Object.entries(options);
                    }

                    html += `
                        <div class="mb-4">
                            <label class="form-label">${currentIndex + 1}. ${escapeHtml(question.question_text)}</label>
                            ${optionEntries.map(([letter, text], optIndex) => {
                                const displayText = (typeof text === 'string' && (text.includes(':') || text.includes(')'))) ? text : `${letter}) ${text}`;
                                return `
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="answers[${currentIndex}][answer]" id="q${currentIndex}_${optIndex}" value="${letter}" required>
                                        <label class="form-check-label" for="q${currentIndex}_${optIndex}">${escapeHtml(displayText)}</label>
                                    </div>
                                `;
                            }).join('')}
                            <input type="hidden" name="answers[${currentIndex}][question_id]" value="${question.id}">
                        </div>
                    `;
                }
            });
        });

        return html;
    }
    
    document.getElementById('assessment-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const answers = [];
        
        // Collect answers
        const answerInputs = this.querySelectorAll('[name^="answers["]');
        const answerMap = {};
        
        answerInputs.forEach(input => {
            const match = input.name.match(/answers\[(\d+)\]\[(\w+)\]/);
            if (match) {
                const index = match[1];
                const field = match[2];
                if (!answerMap[index]) answerMap[index] = {};
                answerMap[index][field] = input.value;
            }
        });
        
        // Convert to array format
        Object.keys(answerMap).forEach(key => {
            if (answerMap[key].answer !== undefined) {
                answers.push({
                    question_id: parseInt(answerMap[key].question_id),
                    answer: answerMap[key].answer
                });
            }
        });
        
        try {
            const response = await fetch(`/api/candidate/assessment/${formId}`, {
                method: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + token,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ answers })
            });
            
            const result = await response.json();
            
            if (response.ok) {
                // Track assessment completion with Mixpanel (if available)
                if (typeof trackEvent === 'function') {
                    const params = new URLSearchParams(window.location.search);
                    const type = params.get('type') || 'unknown';

                    trackEvent('Assessment Completed', {
                        assessment_type: type,
                        assessmentType: type, // Keep both for backwards compatibility
                        form_id: formId || null,
                        assessment_id: result.assessment?.id || null,
                    });
                }

                await showAppModal({
                    title: 'Assessment Submitted',
                    message: 'Your assessment has been submitted successfully.',
                    variant: 'success'
                });
                window.location.href = '/candidate/home';
            } else {
                const message = result.message || (typeof result === 'string' ? result : JSON.stringify(result));
                await showAppModal({
                    title: 'Submission Error',
                    message: message,
                    variant: 'danger'
                });
            }
        } catch (error) {
            console.error('Error:', error);
            await showAppModal({
                title: 'Submission Error',
                message: error.message,
                variant: 'danger'
            });
        }
    });
    
    // Track assessment start when questions page loads
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof trackEvent === 'function') {
            const params = new URLSearchParams(window.location.search);
            const type = params.get('type') || 'unknown';
            const formId = window.location.pathname.split('/').pop();

            trackEvent('Assessment Started', {
                assessment_type: type,
                assessmentType: type, // Keep both for backwards compatibility
                form_id: formId !== 'assessment' ? formId : null,
            });
        }
    });
    
    loadQuestions();
</script>
@endsection

