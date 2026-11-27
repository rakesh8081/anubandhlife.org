<?php
// export_pdf.php
require 'db_config.php';
require 'vendor/autoload.php';
require 'auth.php';
require 'report_templates.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// 1. Get request parameters
$participant_id = $_GET['id'] ?? null;
$type = $_GET['type'] ?? 'interest';

if (!$participant_id) {
    die("Error: Missing Participant ID.");
}

// Only the browser that registered this participant (or a researcher with the admin key) may view the report
if (!mindlab_owns_participant($participant_id) && !mindlab_is_admin($_GET['key'] ?? '')) {
    http_response_code(403);
    die("Error: This report is only available in the browser where the assessment was taken.");
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
$options->set('isRemoteEnabled', false);
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
} elseif ($type === 'assessment') {
    $def = mindlab_get_assessment($_GET['test'] ?? '');
    if (!$def) {
        die("Error: Unknown assessment.");
    }
    $row = mindlab_latest_assessment_result($pdo, $def['id'], [$participant_id]);
    $scored = $row ? mindlab_score_assessment($def, $row['responses']) : null;
    if (!$scored) {
        die("Error: No results found for this assessment.");
    }
    $html = buildAssessmentTemplate($user, $def, $row, $scored);
    $filename = strtoupper($def['id']) . '_' . preg_replace('/[^A-Za-z0-9]/', '_', $user['full_name']) . '.pdf';
} else {
    die("Error: Invalid report type.");
}

// 5. Render and Stream
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream($filename, array("Attachment" => false)); // false = open in browser

