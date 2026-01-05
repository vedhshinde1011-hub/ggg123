<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Load environment variables
function loadEnv($path) {
    if (!file_exists($path)) {
        return false;
    }
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        
        if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
            putenv(sprintf('%s=%s', $name, $value));
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
}

loadEnv('.env');

// Set timezone to Indian Standard Time
date_default_timezone_set('Asia/Kolkata');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
    exit;
}

// Validate required fields based on request type
$requestType = isset($input['requestType']) ? $input['requestType'] : 'contact';

if ($requestType === 'card_order') {
    // Card order validation
    $required = ['cardHolderName', 'emergencyContact', 'selectedCard'];
    foreach ($required as $field) {
        if (empty($input[$field])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => "Field '$field' is required for card orders"]);
            exit;
        }
    }
    
    // Sanitize card order input
    $cardHolderName = htmlspecialchars(trim($input['cardHolderName']));
    $bloodGroup = isset($input['bloodGroup']) ? htmlspecialchars(trim($input['bloodGroup'])) : 'Not specified';
    $countryCode = isset($input['countryCode']) ? htmlspecialchars(trim($input['countryCode'])) : '+91';
    $emergencyContact = htmlspecialchars(trim($input['emergencyContact']));
    $backSideNumber = isset($input['backSideNumber']) ? htmlspecialchars(trim($input['backSideNumber'])) : 'Not specified';
    $medicalConditions = isset($input['medicalConditions']) ? htmlspecialchars(trim($input['medicalConditions'])) : 'Not specified';
    $selectedCard = htmlspecialchars(trim($input['selectedCard']));
    
} else {
    // Regular contact form validation
    $required = ['fullName', 'phoneNumber', 'email', 'cardCount'];
    foreach ($required as $field) {
        if (empty($input[$field])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => "Field '$field' is required"]);
            exit;
        }
    }
    
    // Sanitize contact form input
    $fullName = htmlspecialchars(trim($input['fullName']));
    $phoneNumber = htmlspecialchars(trim($input['phoneNumber']));
    $email = filter_var(trim($input['email']), FILTER_VALIDATE_EMAIL);
    $cardCount = htmlspecialchars(trim($input['cardCount']));
    $message = isset($input['message']) ? htmlspecialchars(trim($input['message'])) : '';
    
    if (!$email) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid email address']);
        exit;
    }
}

try {
    $mail = new PHPMailer(true);
    
    // Server settings
    $mail->isSMTP();
    $mail->Host = getenv('SMTP_HOST');
    $mail->SMTPAuth = true;
    $mail->Username = getenv('SMTP_USER');
    $mail->Password = getenv('SMTP_PASS');
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = getenv('SMTP_PORT');
    
    // Recipients
    $mail->setFrom(getenv('SMTP_FROM'), 'Ekam Care Website');
    $mail->addAddress(getenv('SMTP_TO'));
    
    if ($requestType === 'card_order') {
        // Card order email
        $mail->Subject = 'New Ekam Care Card Order - ' . $cardHolderName;
        
        $emailBody = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #dc2626; color: white; padding: 20px; text-align: center; }
                .content { background: #f9f9f9; padding: 20px; }
                .field { margin-bottom: 15px; }
                .label { font-weight: bold; color: #dc2626; }
                .value { margin-left: 10px; }
                .card-info { background: #e3f2fd; padding: 15px; border-left: 4px solid #2196f3; margin: 20px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>🎯 New Ekam Care Card Order</h2>
                </div>
                <div class='content'>
                    <div class='card-info'>
                        <h3>Card Details</h3>
                        <div class='field'>
                            <span class='label'>Card Type:</span>
                            <span class='value'>" . ucfirst($selectedCard) . " Card</span>
                        </div>
                        <div class='field'>
                            <span class='label'>Price:</span>
                            <span class='value'>₹299</span>
                        </div>
                    </div>
                    
                    <h3>Cardholder Information</h3>
                    <div class='field'>
                        <span class='label'>Name:</span>
                        <span class='value'>$cardHolderName</span>
                    </div>
                    <div class='field'>
                        <span class='label'>Blood Group:</span>
                        <span class='value'>$bloodGroup</span>
                    </div>
                    <div class='field'>
                        <span class='label'>Emergency Contact:</span>
                        <span class='value'>$countryCode $emergencyContact</span>
                    </div>
                    <div class='field'>
                        <span class='label'>Back Side Number:</span>
                        <span class='value'>$backSideNumber</span>
                    </div>
                    <div class='field'>
                        <span class='label'>Medical Conditions:</span>
                        <span class='value'>$medicalConditions</span>
                    </div>
                    <div class='field'>
                        <span class='label'>Order Time:</span>
                        <span class='value'>" . date('d-m-Y H:i:s', time() + 19800) . " IST</span>
                    </div>
                </div>
            </div>
        </body>
        </html>";
        
    } else {
        // Regular contact form email
        $mail->addReplyTo($email, $fullName);
        $mail->Subject = 'New Ekam Care Card Request - ' . $fullName;
        
        $emailBody = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #dc2626; color: white; padding: 20px; text-align: center; }
                .content { background: #f9f9f9; padding: 20px; }
                .field { margin-bottom: 15px; }
                .label { font-weight: bold; color: #dc2626; }
                .value { margin-left: 10px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>🚨 New Ekam Care Card Request</h2>
                </div>
                <div class='content'>
                    <div class='field'>
                        <span class='label'>Full Name:</span>
                        <span class='value'>$fullName</span>
                    </div>
                    <div class='field'>
                        <span class='label'>Phone Number:</span>
                        <span class='value'>$phoneNumber</span>
                    </div>
                    <div class='field'>
                        <span class='label'>Email:</span>
                        <span class='value'>$email</span>
                    </div>
                    <div class='field'>
                        <span class='label'>Card Count:</span>
                        <span class='value'>$cardCount</span>
                    </div>";
        
        if (!empty($message)) {
            $emailBody .= "
                    <div class='field'>
                        <span class='label'>Message:</span>
                        <span class='value'>$message</span>
                    </div>";
        }
        
        $emailBody .= "
                    <div class='field'>
                        <span class='label'>Submitted:</span>
                        <span class='value'>" . date('d-m-Y H:i:s', time() + 19800) . " IST</span>
                    </div>
                </div>
            </div>
        </body>
        </html>";
    }
    
    // Content
    $mail->isHTML(true);
    $mail->Body = $emailBody;
    
    $mail->send();
    
    if ($requestType === 'card_order') {
        echo json_encode([
            'success' => true, 
            'message' => 'Card order placed successfully! We will contact you within 24 hours to confirm details and arrange delivery.'
        ]);
    } else {
        echo json_encode([
            'success' => true, 
            'message' => 'Request submitted successfully! We will contact you within 24 hours.'
        ]);
    }
    
} catch (Exception $e) {
    error_log("Mail Error: " . $mail->ErrorInfo);
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Failed to send email. Please try again or contact us directly.'
    ]);
}
?>