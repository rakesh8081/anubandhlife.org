<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Psychological Awareness Survey | Mind Lab 2.0</title>
    <link rel="stylesheet" href="style.css?v=1.3">
</head>
<body>

<div class="container">
    <header class="header-nav">
        <h2 class="brand-name">Anubandh Life</h2>
        <span style="color: #666; font-size: 0.9rem;">Mind Lab - BPCC 111 Survey</span>
    </header>

    <div class="form-wrapper" style="max-width: 800px;">
        <h2 class="column-title" style="margin-bottom: 10px;">Psychological Awareness Survey</h2>
        <p style="color: #555; margin-bottom: 30px;">Please complete the demographic profile before proceeding to the survey questions. All responses are confidential and will be used solely for academic research.</p>

        <form id="awarenessSurveyForm">
            
            <div style="background: #f1f8e9; padding: 20px; border-radius: 8px; border: 1px solid #c8e6c9; margin-bottom: 40px;">
                <h3 style="color: #2e7d32; margin-bottom: 20px;">Section A: Demographic Profile</h3>
                
                <div class="form-group">
                    <label for="eduLevel">Educational Qualification</label>
                    <select id="eduLevel" required>
                        <option value="">Select...</option>
                        <option value="Higher Secondary">Higher Secondary</option>
                        <option value="Undergraduate">Undergraduate</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="occupation">Occupation</label>
                    <select id="occupation" required>
                        <option value="">Select...</option>
                        <option value="Student Only">Student Only</option>
                        <option value="Employed Part-Time">Employed Part-Time</option>
                        <option value="Employed Full-Time">Employed Full-Time</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="familyType">Type of Family</label>
                    <select id="familyType" required>
                        <option value="">Select...</option>
                        <option value="Nuclear">Nuclear</option>
                        <option value="Joint">Joint</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="maritalStatus">Marital Status</label>
                    <select id="maritalStatus" required>
                        <option value="">Select...</option>
                        <option value="Single">Single</option>
                        <option value="Married">Married</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
            </div>

            <h3 style="color: #2e7d32; margin-bottom: 20px;">Section B: Awareness & Perceptions</h3>
            <div id="questionsContainer"></div>

            <div id="errorMessage" style="color: #d32f2f; margin-bottom: 20px; display: none; font-weight: bold;">
                Please answer all questions before submitting.
            </div>

            <button type="submit" class="btn-full" id="submitBtn">Submit Survey Data</button>
        </form>
    </div>
</div>

<script src="questions-awareness.js"></script>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const container = document.getElementById('questionsContainer');

    // 1. Render the Questions Dynamically
    awarenessQuestions.forEach((q, index) => {
        const questionDiv = document.createElement('div');
        questionDiv.style.marginBottom = '30px';
        questionDiv.style.paddingBottom = '20px';
        questionDiv.style.borderBottom = '1px solid #eaeaea';

        let html = `<p style="font-weight: 600; margin-bottom: 15px; color: #1a1a1a;">${index + 1}. ${q.text}</p>`;

        if (q.type === 'scale') {
            html += `<div class="radio-group" style="display: flex; gap: 20px; align-items: center;">`;
            for (let i = q.min; i <= q.max; i++) {
                html += `<label style="cursor: pointer; display: flex; align-items: center; gap: 5px;">
                            <input type="radio" name="${q.id}" value="${i}" required> ${i}
                         </label>`;
            }
            html += `</div>`;
        } 
        else if (q.type === 'radio') {
            html += `<div style="display: flex; flex-direction: column; gap: 10px;">`;
            q.options.forEach(opt => {
                html += `<label style="cursor: pointer; display: flex; align-items: center; gap: 8px;">
                            <input type="radio" name="${q.id}" value="${opt}" required> ${opt}
                         </label>`;
            });
            html += `</div>`;
        }
        else if (q.type === 'checkbox') {
            html += `<div style="display: flex; flex-direction: column; gap: 10px;">`;
            q.options.forEach(opt => {
                html += `<label style="cursor: pointer; display: flex; align-items: center; gap: 8px;">
                            <input type="checkbox" name="${q.id}" value="${opt}"> ${opt}
                         </label>`;
            });
            html += `</div>`;
        }
        else if (q.type === 'text') {
            html += `<textarea name="${q.id}" required rows="4" style="width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 6px; font-family: inherit;" placeholder="Type your answer here..."></textarea>`;
        }

        questionDiv.innerHTML = html;
        container.appendChild(questionDiv);
    });

    // 2. Handle Form Submission
    document.getElementById('awarenessSurveyForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const submitBtn = document.getElementById('submitBtn');
        submitBtn.innerText = "Processing...";
        submitBtn.style.background = "#555";
        submitBtn.disabled = true;

        // Gather Demographics
        const demographics = {
            eduLevel: document.getElementById('eduLevel').value,
            occupation: document.getElementById('occupation').value,
            familyType: document.getElementById('familyType').value,
            maritalStatus: document.getElementById('maritalStatus').value
        };

        // Gather Responses
        const responses = {};
        const formData = new FormData(this);
        
        awarenessQuestions.forEach(q => {
            if (q.type === 'checkbox') {
                responses[q.id] = formData.getAll(q.id); // Captures multiple checkbox values
            } else {
                responses[q.id] = formData.get(q.id);
            }
        });

        // Get Participant ID from localStorage (set during consent.php)
        const participantId = localStorage.getItem('participant_id');

        // Prepare Payload
        const payload = {
            action: 'save_awareness_results',
            participant_id: participantId,
            demographics: demographics,
            responses: responses
        };

        // Send to Backend
        fetch('save_data.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                // Clear the view and show a thank you message
                document.querySelector('.form-wrapper').innerHTML = `
                    <div style="text-align: center; padding: 40px 20px;">
                        <h2 style="color: #2e7d32; margin-bottom: 20px;">Survey Complete</h2>
                        <p style="margin-bottom: 30px; font-size: 1.1rem;">Thank you for contributing to this research initiative.</p>
                        <a href="index.php" class="btn">Return to Mind Lab</a>
                    </div>
                `;
            } else {
                throw new Error(data.message || "Failed to save data.");
            }
        })
        .catch(err => {
            console.error(err);
            document.getElementById('errorMessage').innerText = "Network Error: Could not save data. Please check your connection.";
            document.getElementById('errorMessage').style.display = "block";
            submitBtn.innerText = "Submit Survey Data";
            submitBtn.style.background = "#2e7d32";
            submitBtn.disabled = false;
        });
    });
});
</script>

</body>
</html>