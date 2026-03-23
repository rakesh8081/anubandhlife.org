<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Interest Inventory | Mind Lab</title>
    <link rel="stylesheet" href="style.css?v=999">
    <style>
        /* Test-specific styling to append to your style.css later */
        .q-row {
            background: var(--bg-offset);
            padding: 20px;
            margin-bottom: 15px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
        }
        .q-text {
            font-weight: 600;
            margin-bottom: 15px;
            font-size: 1.05rem;
        }
        .scale-wrapper {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: var(--bg-main);
            padding: 10px 15px;
            border-radius: 6px;
            border: 1px solid #e0e0e0;
        }
       
        .radio-group {
            display: flex;
            gap: 15px;
        }
        .radio-group label {
            display: flex;
            flex-direction: column;
            align-items: center;
            cursor: pointer;
            font-size: 0.9rem;
        }
        .radio-group input[type="radio"]:checked {
            accent-color: var(--primary-green);
        }
        
        .scale-label {
            font-size: 0.85rem;
            color: var(--primary-green); /* Labels turn Green */
            font-weight: 600;
        }
    </style>
</head>
<body>

<div class="container">
    <header class="header-nav">
        <h2 class="brand-name">Anubandh Life</h2>
        <div style="text-align: right;">
            <span style="font-size: 0.85rem; color: var(--text-muted);">Participant:</span><br>
            <strong id="displayUserCard">Loading...</strong>
        </div>
    </header>

    <main style="max-width: 800px; margin: 0 auto;">
        <h1 style="margin-bottom: 10px;">Examine Your Interests</h1>
        <p style="color: var(--text-muted); margin-bottom: 30px;">
            Score each statement based on how much you agree with it. <br>
            <strong>1 = Strongly Disagree</strong> up to <strong>5 = Strongly Agree</strong>.
        </p>

        <form id="assessmentForm">
            <div id="questions-target">
                </div>
            
            <div style="margin-top: 40px; text-align: right;">
                <p id="errorMsg" style="color: red; display: none; margin-bottom: 15px;">Please answer all 36 statements before proceeding.</p>
                <button type="submit" class="btn btn-full" style="padding: 15px; font-size: 1.1rem;">Calculate My Profile</button>
            </div>
        </form>
    </main>
</div>

<script src="logic-interest.js"></script>
<script>
    // 1. Security Check: Ensure user came from the Gateway
    const userData = JSON.parse(localStorage.getItem('mindLabUser'));
    if (!userData) {
        window.location.href = 'index.php'; // Bounce unauthorized access
    } else {
        document.getElementById('displayUserCard').innerText = userData.name;
    }

    // 2. Initialize the Test
    window.onload = renderInterestTest;

    // 3. Handle Submission

    // Handle Submission
    document.getElementById('assessmentForm').addEventListener('submit', async function(e) {
        e.preventDefault();
    
        // Disable button to prevent double-clicks
        const submitBtn = e.target.querySelector('button[type="submit"]');
        submitBtn.innerText = "Saving Results...";
        submitBtn.disabled = true;
    
        // Wait for the logic to finish saving to the database
        const success = await calculateAndSaveScores(); 
        
        if (success) {
            window.location.href = 'result-interest.php';
        } else {
            submitBtn.innerText = "Calculate My Profile";
            submitBtn.disabled = false;
            document.getElementById('errorMsg').style.display = 'block';
        }
    });

    
</script>

</body>
</html>