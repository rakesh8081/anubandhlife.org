<?php
// mailer.php

// 1. Security: Only accept POST requests containing JSON
header('Content-Type: application/json');
$requestData = json_decode(file_get_contents('php://input'), true);

if (!$requestData) {
    echo json_encode(['success' => false, 'message' => 'Invalid request format.']);
    exit;
}

$name = htmlspecialchars($requestData['participantName']);
$email = filter_var($requestData['participantEmail'], FILTER_SANITIZE_EMAIL);
$reportHtml = $requestData['reportContent'];

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email address provided.']);
    exit;
}

// 2. Load PHPMailer (You will need to download PHPMailer to your server)
// Download from: https://github.com/PHPMailer/PHPMailer
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

try {
    // 3. Server Settings (Your Hostinger SMTP Credentials)
    $mail->isSMTP();
    $mail->Host       = 'smtp.hostinger.com'; // Standard Hostinger SMTP server
    $mail->SMTPAuth   = true;
    $mail->Username   = 'results@anubandhlife.org'; // Your domain email
    $mail->Password   = '$o9yjBQ$I84E2UfE'; // Replace with the actual password for this email
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Enable SSL encryption
    $mail->Port       = 465; // Standard port for Hostinger SSL

    // 4. Recipients
    $mail->setFrom('results@anubandhlife.org', 'Mind Lab 2.0 | Anubandh Life');
    $mail->addAddress($email, $name); // Send to the participant
    
    // Optional: Blind-copy yourself to keep a record of completed tests
    $mail->addBCC('rakesh8081@gmail.com'); 

    // 5. Content Structure
    $mail->isHTML(true);
    $mail->Subject = 'Your Psychological Interest Profile - Mind Lab 2.0';
    
    // Wrap their results in a clean email template
    $mail->Body    = "
        <div style='font-family: Arial, sans-serif; color: #333; max-width: 600px; margin: 0 auto;'>
            <h2 style='color: #1a1a1a;'>Hello $name,</h2>
            <p>Thank you for exploring your dimensions with Mind Lab 2.0. Below is a snapshot of your Interest Profile.</p>
            <hr style='border: 1px solid #eaeaea;'>
            $reportHtml
            <hr style='border: 1px solid #eaeaea;'>
            <p style='font-size: 0.85rem; color: #666;'>This assessment is for self-exploration and educational purposes. <br>&copy; Anubandh Life.</p>
        </div>
    ";

    // Fallback for non-HTML email clients
    $mail->AltBody = "Hello $name, thank you for taking the Interest Inventory. Please view this email in an HTML-compatible client to see your full results.";

    // 6. Send the payload
    $mail->send();
    echo json_encode(['success' => true, 'message' => 'Email sent successfully.']);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => "Mailer Error: {$mail->ErrorInfo}"]);
}
?>