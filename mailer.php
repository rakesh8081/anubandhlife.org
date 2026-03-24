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
function buildEmailTemplate($user, $scores) {
    $html = '
    <style>
        body { font-family: Helvetica, Arial, sans-serif; color: #222; font-size: 14px; line-height: 1.4; }
        .header { text-align: center; border-bottom: 2px solid #2e7d32; padding-bottom: 15px; margin-bottom: 25px; }
        .title { color: #2e7d32; margin: 0; font-size: 24px; }
        .meta-table { width: 100%; margin-bottom: 25px; background-color: #f9f9f9; border: 1px solid #eee; }
        .meta-table td { padding: 8px; border-bottom: 1px solid #eee; }
        .data-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .data-table th, .data-table td { border: 1px solid #ccc; padding: 10px; text-align: left; }
        .data-table th { background-color: #e8f5e9; color: #1b5e20; }
    </style>
    <div class="header">
        <h1 class="title">Anubandh Life | Mind Lab</h1>
        <h2>Interest Inventory Report</h2>
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
    <table class="data-table">
        <tr><th width="70%">Category</th><th width="30%">Score</th></tr>';
        
    foreach ($scores as $category => $score) {
        $html .= '<tr><td>' . htmlspecialchars($category) . '</td><td><strong>' . htmlspecialchars($score) . '</strong></td></tr>';
    }
        
    $html .= '</table>';
    return $html;
}
?>