const inventoryQuestions = [
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

const interestDescriptions = {
    "P": "Practical: You enjoy working with tools, machines, or animals rather than people. You prefer solving manual, mechanical, or electronic problems in a logical way.",
    "E": "Enterprising: You enjoy working on projects, taking risks, organising or influencing other people. You may be ambitious, outgoing, and energetic.",
    "S": "Social: You enjoy working with people. You tend to be friendly, sympathetic, helpful and sensitive to others.",
    "C": "Creative: You enjoy developing your skills in art, music, drama or writing. You prefer to work with your mind, body or feelings.",
    "I": "Investigative: You enjoy intellectual challenges, focusing on ideas and using your thinking skills. You tend to be curious, independent, and logical.",
    "O": "Organisational: You enjoy working with people, data and things where you can establish clear systems for work. You are accurate and reliable."
};

function renderInterestTest() {
    const target = document.getElementById('test-questions-container');
    target.innerHTML = inventoryQuestions.map((item, i) => `
        <div class="q-row">
            <p><strong>${i+1}.</strong> I like ${item.q}</p>
            <div class="radio-bar">
                <span>Disagree</span>
                ${[1,2,3,4,5].map(n => `<label><input type="radio" name="q${i}" value="${n}" data-cat="${item.cat}" required> ${n}</label>`).join('')}
                <span>Agree</span>
            </div>
        </div>
    `).join('');
}

function calculateResults() {
    const radios = document.querySelectorAll('#test-questions-container input:checked');
    if(radios.length < 36) { alert("Please answer all 36 statements."); return; }
    
    let totals = {P:0, E:0, S:0, C:0, I:0, O:0};
    radios.forEach(r => totals[r.dataset.cat] += parseInt(r.value));

    const sorted = Object.entries(totals).sort((a,b) => b[1] - a[1]).slice(0, 3);
    
    document.getElementById('test-ui').classList.add('hidden');
    document.getElementById('result-ui').classList.remove('hidden');
    
    const user = JSON.parse(sessionStorage.getItem('mindLabUser'));
    const reportTarget = document.getElementById('report-content');

    reportTarget.innerHTML = `
        <h2 style="text-align:center;">Interest Profile for ${user.name}</h2>
        <p style="text-align:center;">Age: ${user.age} | Sex: ${user.sex}</p>
        <hr><br>
        <h3>Your Major Interest Areas:</h3>
        ${sorted.map((item, idx) => `
            <div class="res-box">
                <strong>#${idx+1}: ${item[0]} Score (${item[1]} pts)</strong>
                <p>${interestDescriptions[item[0]]}</p>
            </div>
        `).join('')}
    `;
}