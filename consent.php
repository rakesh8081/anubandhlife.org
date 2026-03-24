<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration & Consent | Mind Lab</title>
    <link rel="stylesheet" href="style.css?v=999">
</head>
<body>

<div class="container">
    <header class="header-nav">
        <h2 class="brand-name">Anubandh Life</h2>
        <a href="index.php" style="text-decoration: none; color: #666; font-size: 0.9rem;">&larr; Back to Hub</a>
    </header>

    <main>
        <div class="form-wrapper">
    <h2 style="color: #2e7d32; margin-bottom: 10px;">Participant Details</h2>
    <p style="margin-bottom: 30px; color: #666;">Please provide your information to begin. All fields are mandatory.</p>

    <form id="gatewayForm">
        <div class="form-group">
            <label for="pName">Full Name</label>
            <input type="text" id="pName" required placeholder="Enter your name">
        </div>

        <div class="form-group">
            <label for="pEmail">Email Address</label>
            <input type="email" id="pEmail" required placeholder="email@example.com">
        </div>

        <div class="form-group">
            <label for="pAge">Age</label>
            <input type="number" id="pAge" min="13" max="100" required>
        </div>

        <div class="form-group">
            <label for="pSex">Sex</label>
            <select id="pSex" required>
                <option value="">Select...</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
                <option value="Other">Other</option>
            </select>
        </div>

        <div class="form-group" style="margin-bottom: 30px;">
            <label for="pPhone">Phone Number</label>
            <input type="tel" id="pPhone" required placeholder="+91...">
        </div>

        <div class="consent-scroll">
            <h4 style="margin-bottom: 10px;">Informed Consent Statement</h4>
            <p><strong>1. Purpose:</strong> This assessment is designed for self-exploration and educational purposes.</p>
            <p><strong>2. Data Privacy:</strong> Responses are processed locally. If emailed, data is transmitted securely via our servers.</p>
            <p><strong>3. Voluntary Participation:</strong> You may exit at any time without submitting data.</p>
        </div>

        <div style="display: flex; gap: 10px; align-items: flex-start; margin-bottom: 30px;">
            <input type="checkbox" id="pConsent" required style="margin-top: 4px; width: 18px; height: 18px; accent-color: #2e7d32;">
            <label for="pConsent" style="font-size: 0.95rem; cursor: pointer;">I have read the Informed Consent Statement and agree to proceed.</label>
        </div>

        <button type="submit" class="btn-full">Proceed to Assessment</button>
    </form>
</div>
    </main>
</div>

<script>
    document.getElementById('gatewayForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // 1. Gather the form data
        const participantData = {
            action: 'register',
            name: document.getElementById('pName').value,
            email: document.getElementById('pEmail').value,
            age: document.getElementById('pAge').value,
            sex: document.getElementById('pSex').value,
            phone: document.getElementById('pPhone').value
        };
    
        try {
            // 2. Send data to the server to get the participant_id
            const response = await fetch('save_data.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(participantData)
            });
    
            const result = await response.json();
            
            if (result.success && result.participant_id) {
                // 3. CRITICAL: Save the ID exactly where test-awareness.php is looking for it
                localStorage.setItem('participant_id', result.participant_id);
                
                // (Optional) Keep the full user object stored as well
                participantData.db_id = result.participant_id;
                localStorage.setItem('mindLabUser', JSON.stringify(participantData));
                
                // 4. NOW route the user to the correct assessment based on the URL
                const urlParams = new URLSearchParams(window.location.search);
                const testType = urlParams.get('test');

                if (testType === 'awareness') {
                    window.location.href = 'test-awareness.php';
                } else {
                    window.location.href = 'test-interest.php'; // Default fallback
                }
            } else {
                alert("Registration Error: " + (result.message || "Unknown server error"));
            }
        } catch (err) {
            console.error("Fetch Error:", err);
            alert("Connection failed. Please check your internet or server status.");
        }
    });
</script>

</body>
</html>
