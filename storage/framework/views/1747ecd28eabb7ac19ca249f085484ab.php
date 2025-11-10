<?php $__env->startSection('title', 'Take Assessment'); ?>

<?php $__env->startSection('content'); ?>
<div class="row">
    <div class="col-12">
        <h2 id="assessment-title">Assessment</h2>
        <div class="card">
            <div class="card-body">
                <form id="assessment-form">
                    <div id="questions-container">
                        <p>Loading questions...</p>
                    </div>
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary btn-lg">Submit Assessment</button>
                        <a href="/candidate/home" class="btn btn-secondary btn-lg ms-2">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    const token = localStorage.getItem('api_token');
    const urlParams = new URLSearchParams(window.location.search);
    const formId = window.location.pathname.split('/').pop();
    const assessmentType = urlParams.get('type');
    
    if (!token) {
        window.location.href = '/login';
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
                    <p class="lead">Answer the following multiple-choice questions</p>
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
                <label class="form-label">${index + 1}. ${question.question_text}</label>
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
        return questions.map((question, index) => {
            // Options might be stored as ["A: 15", "B: 30"] or just ["15", "30"]
            let options = question.options || [];
            if (typeof options === 'string') {
                try {
                    options = JSON.parse(options);
                } catch (e) {
                    options = [];
                }
            }
            
            return `
                <div class="mb-4">
                    <label class="form-label">${index + 1}. ${question.question_text}</label>
                    ${options.map((opt, optIndex) => {
                        const letter = String.fromCharCode(65 + optIndex);
                        // Handle options that might be "A: 15" or just "15"
                        const optionText = opt.includes(':') ? opt : `${letter}: ${opt}`;
                        return `
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="answers[${index}][answer]" id="q${index}_${optIndex}" value="${letter}" required>
                                <label class="form-check-label" for="q${index}_${optIndex}">${optionText}</label>
                            </div>
                        `;
                    }).join('')}
                    <input type="hidden" name="answers[${index}][question_id]" value="${question.id}">
                </div>
            `;
        }).join('');
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
            if (answerMap[key].answer) {
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
                alert('Assessment submitted successfully!');
                window.location.href = '/candidate/home';
            } else {
                alert('Error: ' + (result.message || JSON.stringify(result)));
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error submitting assessment: ' + error.message);
        }
    });
    
    loadQuestions();
</script>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/markquissimmons/star-recruiting-app/resources/views/candidate/assessment.blade.php ENDPATH**/ ?>