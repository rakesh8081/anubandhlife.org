<?php
// export_pdf.php
require 'db_config.php';
require 'vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// 1. Get request parameters
$participant_id = $_GET['id'] ?? null;
$type = $_GET['type'] ?? 'interest';

if (!$participant_id) {
    die("Error: Missing Participant ID.");
}

// 2. Fetch Base Participant Data
$stmt = $pdo->prepare("SELECT * FROM participants WHERE id = ?");
$stmt->execute([$participant_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("Error: Participant not found.");
}

// 3. Configure Dompdf
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'Helvetica');
$dompdf = new Dompdf($options);

// 4. Route to the correct template
$html = '';
$filename = 'MindLab_Report.pdf';

if ($type === 'interest') {
    $stmt = $pdo->prepare("SELECT calculated_scores FROM interest_results WHERE participant_id = ? ORDER BY created_at DESC LIMIT 1");
    $stmt->execute([$participant_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if($result) {
        $scores = json_decode($result['calculated_scores'], true);
        $html = buildInterestTemplate($user, $scores);
        $filename = 'Interest_Inventory_' . preg_replace('/[^A-Za-z0-9]/', '_', $user['full_name']) . '.pdf';
    } else {
        die("Error: No interest data found for this user.");
    }

} elseif ($type === 'awareness') {
    $stmt = $pdo->prepare("SELECT demographics, responses FROM awareness_survey_results WHERE participant_id = ? ORDER BY created_at DESC LIMIT 1");
    $stmt->execute([$participant_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if($result) {
        $demo = json_decode($result['demographics'], true);
        $responses = json_decode($result['responses'], true);
        $html = buildAwarenessTemplate($user, $demo, $responses);
        $filename = 'BPCC111_RawData_' . $participant_id . '.pdf';
    } else {
         die("Error: No awareness data found.");
    }
} else {
    die("Error: Invalid report type.");
}

// 5. Render and Stream
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream($filename, array("Attachment" => false)); // false = open in browser, true = force download


// --- TEMPLATE FUNCTIONS ---

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
    
    <h3 class="section-title">Aptitude & Interest Scores</h3>
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
    // Specifically structured to serve as the raw data appendix for the BAPCH assignment
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
        // Handle array responses (like checkboxes)
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
?>