// The Source of Truth: 36 Statements & P-E-S-C-I-O Mappings
const interestQuestions = [
    { q: "working out how to get things done efficiently", cat: "O" },
    { q: "repairing and fixing machines", cat: "P" },
    { q: "producing designs from my own ideas", cat: "C" },
    { q: "being physically active", cat: "P" },
    { q: "managing a team of people", cat: "E" },
    { q: "working our problems", cat: "I" },
    { q: "working with people", cat: "S" },
    { q: "getting the details right", cat: "O" },
    { q: "to be different", cat: "C" },
    { q: "exploring new ideas for research and purposes", cat: "I" },
    { q: "helping people learn new skills", cat: "S" },
    { q: "making or building things with my hands", cat: "P" },
    { q: "gathering information", cat: "O" },
    { q: "learning new things", cat: "I" },
    { q: "using my imagination in my work", cat: "C" },
    { q: "persuading people to do or to buy something", cat: "E" },
    { q: "organising things, people and events", cat: "O" },
    { q: "providing care for people in some way", cat: "S" },
    { q: "making decisions", cat: "E" },
    { q: "carrying out research projects", cat: "I" },
    { q: "briefing a sales team about a new product", cat: "E" },
    { q: "making lists", cat: "O" },
    { q: "expressing myself in music, painting or writing", cat: "C" },
    { q: "working with community groups", cat: "S" },
    { q: "questioning established theories", cat: "I" },
    { q: "taking calculated risks", cat: "E" },
    { q: "designing or servicing equipment", cat: "P" },
    { q: "analysing statistical data", cat: "I" },
    { q: "working outside in the fresh air", cat: "P" },
    { q: "listening to people's problems", cat: "S" },
    { q: "analysing a company's annual accounts", cat: "E" },
    { q: "selling something I have created", cat: "C" },
    { q: "writing letters, reports and articles", cat: "O" },
    { q: "using hand/machine tools to make things", cat: "P" },
    { q: "being involved in a community arts project", cat: "C" },
    { q: "giving advice on grants or benefits", cat: "S" }
];

// Generates the HTML for the 36 questions automatically
function renderInterestTest() {
    const target = document.getElementById('questions-target');
    
    let htmlContent = '';
    interestQuestions.forEach((item, index) => {
        htmlContent += `
            <div class="q-row">
                <div class="q-text">${index + 1}. I like ${item.q}</div>
                <div class="scale-wrapper">
                    <span class="scale-label">Disagree</span>
                    <div class="radio-group">
                        <label><input type="radio" name="q${index}" value="1" data-cat="${item.cat}" required> 1</label>
                        <label><input type="radio" name="q${index}" value="2" data-cat="${item.cat}" required> 2</label>
                        <label><input type="radio" name="q${index}" value="3" data-cat="${item.cat}" required> 3</label>
                        <label><input type="radio" name="q${index}" value="4" data-cat="${item.cat}" required> 4</label>
                        <label><input type="radio" name="q${index}" value="5" data-cat="${item.cat}" required> 5</label>
                    </div>
                    <span class="scale-label">Agree</span>
                </div>
            </div>
        `;
    });
    
    target.innerHTML = htmlContent;
}

// Calculates scores and saves them for the Results page
// Inside logic-interest.js
async function calculateAndSaveScores() {
    const selectedRadios = document.querySelectorAll('#questions-target input[type="radio"]:checked');
    
    // Check if all 36 questions are answered
    if (selectedRadios.length < 36) {
        return false; 
    }

    const userData = JSON.parse(localStorage.getItem('mindLabUser'));
    
    // Safety check for the Database ID
    if (!userData || !userData.db_id) {
        alert("Session error. Please restart from the home page.");
        return false;
    }

    let scores = { P: 0, E: 0, S: 0, C: 0, I: 0, O: 0 };
    let rawResponses = {};

    selectedRadios.forEach((radio, index) => {
        const category = radio.getAttribute('data-cat');
        const val = parseInt(radio.value);
        scores[category] += val;
        rawResponses[`q${index + 1}`] = val;
    });

    // Save to Database
    try {
        const dbResponse = await fetch('save_data.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'save_results',
                participant_id: userData.db_id,
                responses: rawResponses,
                scores: scores
            })
        });

        const dbResult = await dbResponse.json();

        if (dbResult.success) {
            // ONLY save to localStorage and return true if DB save was successful
            localStorage.setItem('mindLabScores_interest', JSON.stringify(scores));
            return true;
        } else {
            console.error("DB Error:", dbResult.message);
            alert("Database Error: Could not save your results.");
            return false;
        }
    } catch (err) {
        console.error("Network Error:", err);
        alert("Failed to reach the server. Please try again.");
        return false;
    }
}