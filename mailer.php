<?php
// mailer.php
require 'db_config.php';
require 'vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$participant_id = $data['participant_id'] ?? null;
$type = $data['type'] ?? 'interest';

if (!$participant_id) {
    echo json_encode(['success' => false, 'message' => 'Missing Participant ID.']);
    exit;
}

// 1. Fetch Participant Data
$stmt = $pdo->prepare("SELECT * FROM participants WHERE id = ?");
$stmt->execute([$participant_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || empty($user['email'])) {
    echo json_encode(['success' => false, 'message' => 'Valid user email not found.']);
    exit;
}

// 2. Fetch Score Data
$stmt = $pdo->prepare("SELECT calculated_scores FROM interest_results WHERE participant_id = ? ORDER BY created_at DESC LIMIT 1");
$stmt->execute([$participant_id]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$result) {
    echo json_encode(['success' => false, 'message' => 'No test results found to email.']);
    exit;
}

$scores = json_decode($result['calculated_scores'], true);

// 3. Generate PDF in Memory (using the same template styling)
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('defaultFont', 'Helvetica');
$dompdf = new Dompdf($options);

$html = buildEmailTemplate($user, $scores);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$pdfOutput = $dompdf->output();
$filename = 'Interest_Inventory_' . preg_replace('/[^A-Za-z0-9]/', '_', $user['full_name']) . '.pdf';

// 4. Construct Multipart Email with Attachment
$to = $user['email'];
$subject = "Your Mind Lab Interest Profile";
$senderEmail = "noreply@anubandhlife.org"; // Ensure this matches your Hostinger domain
$boundary = md5(time());

$headers = "From: Anubandh Life <$senderEmail>\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: multipart/mixed; boundary=\"$boundary\"\r\n";

// Email Body
$message = "--$boundary\r\n";
$message .= "Content-Type: text/plain; charset=\"UTF-8\"\r\n";
$message .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
$message .= "Hello " . $user['full_name'] . ",\n\n";
$message .= "Thank you for completing the Mind Lab assessment. Please find your official Interest Inventory report attached to this email.\n\n";
$message .= "Best regards,\nAnubandh Life\r\n";

// Attachment
$message .= "--$boundary\r\n";
$message .= "Content-Type: application/pdf; name=\"$filename\"\r\n";
$message .= "Content-Disposition: attachment; filename=\"$filename\"\r\n";
$message .= "Content-Transfer-Encoding: base64\r\n\r\n";
$message .= chunk_split(base64_encode($pdfOutput)) . "\r\n";
$message .= "--$boundary--";

// 5. Send Email
if (mail($to, $subject, $message, $headers)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Server failed to send the email.']);
}

// --- TEMPLATE FUNCTION ---
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
?>
