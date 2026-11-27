<?php
// report_templates.php — shared PDF templates used by export_pdf.php and mailer.php
require_once __DIR__ . '/assessments.php';


function getBaseStyles() {
    return '
    <style>
        body { font-family: Helvetica, Arial, sans-serif; color: #222; font-size: 14px; line-height: 1.4; }
        .header { text-align: center; border-bottom: 2px solid #2e7d32; padding-bottom: 15px; margin-bottom: 25px; }
        .title { color: #2e7d32; margin: 0; font-size: 24px; }
        .subtitle { color: #555; margin: 5px 0 0 0; font-size: 16px; }
        .meta-table { width: 100%; margin-bottom: 25px; background-color: #f9f9f9; border: 1px solid #eee; }
        .meta-table td { padding: 8px; border-bottom: 1px solid #eee; }
        .data-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .data-table th, .data-table td { border: 1px solid #ccc; padding: 10px; text-align: left; vertical-align: top; }
        .data-table th { background-color: #e8f5e9; color: #1b5e20; }
        .footer { margin-top: 40px; font-size: 11px; color: #777; text-align: center; border-top: 1px solid #ddd; padding-top: 10px; }
        .section-title { color: #2e7d32; border-bottom: 1px solid #2e7d32; padding-bottom: 5px; margin-top: 30px; }
    </style>';
}

function buildInterestTemplate($user, $scores) {
    // 1. The Descriptions Dictionary
    $descriptions = [
        "P" => [ 
            "name" => "Practical (Doer)", 
            "core" => "You prefer working with tools, machines, or physical systems rather than extensive interpersonal interactions.",
            "env" => "Hands-on settings, outdoors, engineering workshops, or technical environments.",
            "roles" => "Mechanical Engineering, Construction Management, Skilled Trades, Systems Architecture, or Network Engineering."
        ],
        "E" => [ 
            "name" => "Enterprising (Persuader)", 
            "core" => "You enjoy taking risks, leading projects, and influencing others to achieve organizational goals.",
            "env" => "Corporate management, law firms, entrepreneurship hubs, or competitive business environments.",
            "roles" => "Sales, Business Management, Entrepreneurship, Law, Real Estate, or Public Relations."
        ],
        "S" => [ 
            "name" => "Social (Helper)", 
            "core" => "You are drawn to roles that involve teaching, healing, or developing others.",
            "env" => "Counseling centers, classrooms, healthcare facilities, or community organizations.",
            "roles" => "Teaching/Education, Counseling and Therapy, Human Resources, Nursing, Social Work, or the Non-Profit Sector."
        ],
        "C" => [ 
            "name" => "Creative (Creator)", 
            "core" => "You enjoy developing your skills in art, music, or writing, and prefer to work with your mind, body, or feelings.",
            "env" => "Design studios, media outlets, the arts, or any workspace that values originality over strict, repetitive routines.",
            "roles" => "Graphic Design, Writing/Journalism, Architecture, Fine Arts, Marketing/Advertising, or Film and Video Production."
        ],
        "I" => [ 
            "name" => "Investigative (Thinker)", 
            "core" => "You enjoy intellectual challenges, focusing on complex ideas, and using your analytical skills to uncover truths.",
            "env" => "Research facilities, academia, data analysis centers, or unstructured environments that allow for deep focus.",
            "roles" => "Scientific Research, Data Science, Psychology, Medical Professions, Forensics, or Software Development."
        ],
        "O" => [ 
            "name" => "Organisational (Organizer)", 
            "core" => "You thrive when working with data, structured systems, and clear procedures.",
            "env" => "Financial institutions, administration, structured corporate offices, or logistical planning.",
            "roles" => "Accounting, Office Administration, Logistics and Supply Chain, Actuarial Science, Quality Control, or Compliance."
        ]
    ];

    // 2. Sort scores highest to lowest
    arsort($scores);

    // 3. Group the Top 3 Ranks (Handling Ties)
    $groupedResults = [];
    $currentGroup = [];
    $currentScore = null;

    foreach ($scores as $key => $score) {
        if ($currentScore === null) {
            $currentScore = $score;
        }

        if ($score < $currentScore) {
            $groupedResults[] = $currentGroup;
            $currentGroup = [];
            $currentScore = $score;
            if (count($groupedResults) == 3) break; // Stop after top 3 ranks
        }
        $currentGroup[] = [
            'id' => $key,
            'score' => $score,
            'data' => $descriptions[$key]
        ];
    }
    if (count($groupedResults) < 3 && count($currentGroup) > 0) {
        $groupedResults[] = $currentGroup;
    }

    // 4. Build the HTML Document
    $html = getBaseStyles() . '
    <div class="header">
        <h1 class="title">Anubandh Life | Mind Lab</h1>
        <h2 class="subtitle">Interest Inventory Report</h2>
    </div>
    
    <table class="meta-table" cellspacing="0">
        <tr>
            <td><strong>Participant:</strong> ' . htmlspecialchars($user['full_name']) . '</td>
            <td><strong>Age:</strong> ' . htmlspecialchars($user['age']) . '</td>
        </tr>
        <tr>
            <td><strong>Email:</strong> ' . htmlspecialchars($user['email']) . '</td>
            <td><strong>Date:</strong> ' . date('F j, Y') . '</td>
        </tr>
    </table>
    
    <div style="margin-bottom: 20px; font-size: 13px; color: #444;">
        <p>This assessment evaluates your personal preferences across six core dimensions to identify your dominant working styles. The results below outline your psychological preferences, the environments where you are most likely to thrive, and suggested paths that align with your unique profile.</p>
    </div>';

    // 5. Inject the Descriptions
    foreach ($groupedResults as $index => $group) {
        $rank = $index + 1;
        
        // Single Winner for this rank
        if (count($group) === 1) {
            $item = $group[0];
            $html .= '
            <div style="margin-bottom: 20px; padding: 15px; background-color: #f9f9f9; border-left: 4px solid #2e7d32; page-break-inside: avoid;">
                <h3 style="margin: 0 0 10px 0; color: #2e7d32; font-size: 16px;">#' . $rank . ': ' . $item['data']['name'] . ' (' . $item['score'] . ' pts)</h3>
                <p style="margin: 5px 0; font-size: 13px;"><strong>Core Trait:</strong> ' . $item['data']['core'] . '</p>
                <p style="margin: 5px 0; font-size: 13px;"><strong>Ideal Environments:</strong> ' . $item['data']['env'] . '</p>
                <p style="margin: 5px 0; font-size: 13px;"><strong>Suggested Roles:</strong> ' . $item['data']['roles'] . '</p>
            </div>';
        } 
        // Tied Scores for this rank
        else {
            $html .= '
            <div style="margin-bottom: 20px; padding: 15px; background-color: #f9f9f9; border-left: 4px solid #2e7d32; page-break-inside: avoid;">
                <h3 style="margin: 0 0 10px 0; color: #2e7d32; font-size: 16px;">#' . $rank . ': Tied Interest Areas (' . $group[0]['score'] . ' pts)</h3>
                <p style="margin: 0 0 10px 0; font-size: 12px; color: #666;">You scored equally high in these distinct areas.</p>';
            
            foreach ($group as $item) {
                $html .= '
                <div style="margin-top: 10px; padding-top: 10px; border-top: 1px dashed #ccc;">
                    <strong style="font-size: 14px;">' . $item['data']['name'] . '</strong>
                    <p style="margin: 5px 0; font-size: 13px;"><strong>Core Trait:</strong> ' . $item['data']['core'] . '</p>
                    <p style="margin: 5px 0; font-size: 13px;"><strong>Ideal Environments:</strong> ' . $item['data']['env'] . '</p>
                    <p style="margin: 5px 0; font-size: 13px;"><strong>Suggested Roles:</strong> ' . $item['data']['roles'] . '</p>
                </div>';
            }
            $html .= '</div>';
        }
    }

    // 6. Add the raw score breakdown at the end
    $html .= '
    <h3 class="section-title" style="page-break-before: auto;">Raw Score Breakdown</h3>
    <table class="data-table">
        <tr>
            <th width="70%">Category</th>
            <th width="30%">Score</th>
        </tr>';
        
    foreach ($scores as $category => $score) {
        $html .= '<tr>
                    <td>' . htmlspecialchars($category) . '</td>
                    <td><strong>' . htmlspecialchars($score) . '</strong></td>
                  </tr>';
    }
        
    $html .= '</table>
    <div class="footer">Report generated securely via Mind Lab. Intended for self-exploration.</div>';
    
    return $html;
}

function buildAwarenessTemplate($user, $demo, $responses) {
    $html = getBaseStyles() . '
    <div class="header">
        <h1 class="title">Psychological Awareness Survey</h1>
        <h2 class="subtitle">Raw Data Export (BPCC 111 Appendix)</h2>
    </div>
    
    <table class="meta-table" cellspacing="0">
        <tr>
            <td width="50%"><strong>Participant ID:</strong> ' . htmlspecialchars($user['id']) . ' (Anonymized)</td>
            <td width="50%"><strong>Age / Sex:</strong> ' . htmlspecialchars($user['age']) . ' / ' . htmlspecialchars($user['sex']) . '</td>
        </tr>
        <tr>
            <td><strong>Education:</strong> ' . htmlspecialchars($demo['eduLevel'] ?? 'N/A') . '</td>
            <td><strong>Occupation:</strong> ' . htmlspecialchars($demo['occupation'] ?? 'N/A') . '</td>
        </tr>
        <tr>
            <td><strong>Family Type:</strong> ' . htmlspecialchars($demo['familyType'] ?? 'N/A') . '</td>
            <td><strong>Marital Status:</strong> ' . htmlspecialchars($demo['maritalStatus'] ?? 'N/A') . '</td>
        </tr>
    </table>
    
    <h3 class="section-title">Survey Responses</h3>
    <table class="data-table">
        <tr>
            <th width="15%">Item ID</th>
            <th width="85%">Recorded Response</th>
        </tr>';
        
    foreach ($responses as $qId => $answer) {
        $displayAnswer = is_array($answer) ? implode(', ', $answer) : $answer;
        
        $html .= '<tr>
                    <td><strong>' . htmlspecialchars($qId) . '</strong></td>
                    <td>' . htmlspecialchars($displayAnswer) . '</td>
                  </tr>';
    }
        
    $html .= '</table>
    <div class="footer">Data collected for academic research purposes.</div>';
    
    return $html;
}

function buildAssessmentTemplate($user, $def, $row, $scored) {
    $h = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
    $html = getBaseStyles() . '
    <style>
        .score { font-size: 28px; font-weight: bold; color: #2e7d32; }
        .crisis { border: 2px solid #c62828; background: #fdecea; padding: 12px; margin-bottom: 20px; font-size: 13px; }
        .crisis ul { margin: 8px 0; padding-left: 18px; }
        .disclaimer { font-size: 12px; color: #555; border-top: 1px solid #ddd; padding-top: 10px; margin-top: 25px; }
    </style>
    <div class="header">
        <h1 class="title">Anubandh Life | Mind Lab</h1>
        <h2 class="subtitle">' . $h($def['title']) . ' Report</h2>
    </div>

    <table class="meta-table" cellspacing="0">
        <tr>
            <td><strong>Participant:</strong> ' . $h($user['full_name']) . '</td>
            <td><strong>Age / Sex:</strong> ' . $h($user['age']) . ' / ' . $h($user['sex']) . '</td>
        </tr>
        <tr>
            <td><strong>Email:</strong> ' . $h($user['email']) . '</td>
            <td><strong>Date taken:</strong> ' . $h(date('F j, Y', strtotime($row['created_at']))) . '</td>
        </tr>
    </table>';

    if ($scored['crisis']) {
        $html .= '<div class="crisis">' . mindlab_crisis_html() . '</div>';
    }

    $html .= '
    <p><span class="score">' . (int)$scored['total'] . ' / ' . (int)$def['max_score'] . '</span>
       &nbsp; <strong>' . $h($scored['severity']) . '</strong></p>
    <p style="font-size: 13px;">' . $h($scored['summary']) . '</p>

    <h3 class="section-title">Your Answers</h3>
    <p style="font-size: 12px; color: #555;">' . $h($def['instructions']) . '</p>
    <table class="data-table">
        <tr><th width="70%">Statement</th><th width="30%">Answer</th></tr>';
    foreach ($def['items'] as $key => $text) {
        $html .= '<tr><td>' . $h($text) . '</td><td>' . $h($def['options'][$scored['answers'][$key]] ?? '') . '</td></tr>';
    }
    foreach ($def['extra'] ?? [] as $key => $q) {
        if (isset($scored['extra'][$key])) {
            $html .= '<tr><td><em>' . $h($q['text']) . '</em></td><td>' . $h($q['options'][$scored['extra'][$key]]) . '</td></tr>';
        }
    }
    $html .= '</table>

    <h3 class="section-title">Score Ranges</h3>
    <table class="data-table">';
    foreach ($def['bands'] as [$min, $max, $label]) {
        $html .= '<tr><td width="30%">' . (int)$min . '–' . (int)$max . '</td><td>' . $h($label) . '</td></tr>';
    }
    $html .= '</table>

    <div class="disclaimer">' . $h(mindlab_disclaimer_text()) . '<br><br>' . $h($def['source']) . '</div>';

    return $html;
}
