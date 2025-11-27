<?php
// save as contact.php (place in same folder as index.html)
// Basic contact handler: validates input, prevents header injection, attempts mail(), fallback to file logging.
// NOTE: mail() requires a properly configured mail server on the host. Adjust $to as needed.

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.html');
    exit;
}

function clean_text($text) {
    $text = trim($text);
    $text = stripslashes($text);
    // remove common header-injection vectors
    $text = preg_replace("/(\r|\n|%0a|%0d|content-type:|bcc:|cc:)/i", "", $text);
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

$name    = isset($_POST['name']) ? clean_text($_POST['name']) : '';
$email   = isset($_POST['email']) ? clean_text($_POST['email']) : '';
$subject = isset($_POST['subject']) ? clean_text($_POST['subject']) : '';
$message = isset($_POST['message']) ? clean_text($_POST['message']) : '';

if (!$name || !$email || !$subject || !$message) {
    header('Location: index.html?status=error');
    exit;
}

// basic email format check
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: index.html?status=error');
    exit;
}

// prepare email
$to = 'gowthamveeraiah76@gmail.com'; // change if you want messages to a different address
$mail_subject = "Website Contact: " . $subject;
$body = "You have a new message from your portfolio site.\n\n";
$body .= "Name: $name\n";
$body .= "Email: $email\n";
$body .= "Subject: $subject\n\n";
$body .= "Message:\n$message\n\n";
$headers = "From: noreply@" . $_SERVER['SERVER_NAME'] . "\r\n";
$headers .= "Reply-To: $email\r\n";
$headers .= "X-Mailer: PHP/" . phpversion();

// try to send email
$sent = false;
if (function_exists('mail')) {
    $sent = @mail($to, $mail_subject, $body, $headers);
}

// fallback: append to local file if mail fails or mail() not available
if (!$sent) {
    $logfile = __DIR__ . '/contacts.txt';
    $entry = "---- " . date('Y-m-d H:i:s') . " ----\n";
    $entry .= "Name: $name\nEmail: $email\nSubject: $subject\nMessage:\n$message\n\n";
    // attempt to write
    @file_put_contents($logfile, $entry, FILE_APPEND | LOCK_EX);
    // treat writing as success (so user won't keep seeing errors if mail blocked)
    $sent = true;
}

if ($sent) {
    header('Location: index.html?status=success');
} else {
    header('Location: index.html?status=error');
}
exit;
?>
