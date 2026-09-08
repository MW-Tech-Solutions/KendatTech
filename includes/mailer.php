<?php
declare(strict_types=1);

require_once __DIR__ . '/env.php';
require_once __DIR__ . '/functions.php';

/**
 * Send an email via SSL Socket SMTP using environment credentials.
 */
function send_smtp_email(string $toEmail, string $toName, string $subject, string $htmlContent): bool {
    @ignore_user_abort(true);
    @set_time_limit(60);

    $envHost = trim((string)env('MAIL_HOST', 'kisltd.com.ng'), " \t\n\r\0\x0B\"'");
    $hosts = array_values(array_unique([
        $envHost,
        "mail.{$envHost}",
        'mail.kisprojectslab.com',
        'kisltd.com.ng',
        'mail.kisltd.com.ng'
    ]));
    $port = (int)env('MAIL_PORT', 465);
    $username = trim((string)env('MAIL_USERNAME', 'hello@kisprojectslab.com'), " \t\n\r\0\x0B\"'");
    $password = (string)env('MAIL_PASSWORD', '');
    $fromEmail = trim((string)env('MAIL_FROM_ADDRESS', 'hello@kisprojectslab.com'), " \t\n\r\0\x0B\"'");
    $fromName = trim((string)env('MAIL_FROM_NAME', 'Kendat Integrated Services'), " \t\n\r\0\x0B\"'");

    $isDev = env('APP_ENV', 'production') === 'development';
    $verifyPeer = $isDev ? (env('SMTP_VERIFY_PEER', 'false') === 'true') : (env('SMTP_VERIFY_PEER', 'false') === 'true');

    $socket = null;
    $context = stream_context_create([
        'ssl' => [
            'verify_peer' => $verifyPeer,
            'verify_peer_name' => false,
            'allow_self_signed' => true,
        ]
    ]);

    foreach ($hosts as $host) {
        $socket = @stream_socket_client("ssl://{$host}:{$port}", $errno, $errstr, 6, STREAM_CLIENT_CONNECT, $context);
        if ($socket) {
            stream_set_timeout($socket, 5);
            break;
        }
    }

    if (!$socket) {
        error_log("SMTP Connection Error: {$errstr} ({$errno})");
        return false;
    }

    try {

        $read = function() use ($socket): string {
            $response = '';
            while ($str = fgets($socket, 515)) {
                $response .= $str;
                if (substr($str, 3, 1) === ' ') break;
            }
            return $response;
        };

        $write = function(string $cmd) use ($socket): void {
            fputs($socket, $cmd . "\r\n");
        };

        $read(); // Initial 220 banner

        $write("EHLO " . gethostname());
        $read();

        $write("AUTH LOGIN");
        $read();

        $write(base64_encode($username));
        $read();

        $write(base64_encode($password));
        $authResp = $read();

        if (strpos($authResp, '235') === false) {
            error_log("SMTP Auth Failed: " . trim($authResp));
            fclose($socket);
            return false;
        }

        $write("MAIL FROM: <{$fromEmail}>");
        $read();

        $write("RCPT TO: <{$toEmail}>");
        $read();

        $write("DATA");
        $read();

        $msgId = time() . '.' . substr(md5($toEmail . microtime()), 0, 12) . '@kisprojectslab.com';
        $cleanToName = preg_replace('/[^\w\s\.-]/', '', $toName);
        $cleanFromName = preg_replace('/[^\w\s\.-]/', '', $fromName);

        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "Content-Transfer-Encoding: quoted-printable\r\n";
        $headers .= "From: {$cleanFromName} <{$fromEmail}>\r\n";
        $headers .= "Reply-To: {$fromEmail}\r\n";
        $headers .= "To: {$cleanToName} <{$toEmail}>\r\n";
        $headers .= "Subject: {$subject}\r\n";
        $headers .= "Date: " . date('r') . "\r\n";
        $headers .= "Message-ID: <{$msgId}>\r\n";
        $headers .= "X-Mailer: Kendat-Enterprise-Mailer/4.2\r\n";

        $encodedHtml = quoted_printable_encode($htmlContent);
        $fullPayload = $headers . "\r\n" . $encodedHtml . "\r\n.";
        $write($fullPayload);
        $dataResp = $read();

        $write("QUIT");
        fclose($socket);

        return strpos($dataResp, '250') !== false;
    } catch (Throwable $e) {
        error_log("SMTP Exception: " . $e->getMessage());
        return false;
    }
}

/**
 * Wrap email body content inside official Kendat Tech branded HTML shell.
 */
function build_branded_email_html(string $title, string $bodyHtml): string {
    $year = date('Y');
    return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$title}</title>
    <style>
        body { margin: 0; padding: 0; background-color: #f1f5f9; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #0f172a; line-height: 1.6; }
        .wrapper { width: 100%; table-layout: fixed; background-color: #f1f5f9; padding: 40px 10px; }
        .main-table { width: 100%; max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 20px; overflow: hidden; border: 1px solid #e2e8f0; box-shadow: 0 12px 40px rgba(15, 23, 42, 0.08); }
        .email-header { background: linear-gradient(180deg, #050b18 0%, #0b1736 100%); padding: 36px 30px; text-align: center; border-bottom: 3px solid #00E5FF; }
        .email-header .brand-title { color: #ffffff; font-size: 24px; font-weight: 900; letter-spacing: -0.02em; margin: 0; text-transform: uppercase; }
        .email-header .brand-sub { color: #00E5FF; font-size: 11px; font-weight: 800; letter-spacing: 0.12em; text-transform: uppercase; margin-top: 6px; }
        .email-body { padding: 40px 32px; background-color: #ffffff; }
        .email-body h2 { font-size: 22px; font-weight: 800; color: #0f172a; margin-top: 0; margin-bottom: 16px; letter-spacing: -0.02em; }
        .email-body p { font-size: 15px; color: #475569; margin: 0 0 20px; line-height: 1.65; }
        .btn-wrapper { text-align: center; margin: 32px 0; }
        .btn-primary { display: inline-block; padding: 14px 32px; background: linear-gradient(135deg, #0087FF 0%, #0056b3 100%); color: #ffffff !important; text-decoration: none; border-radius: 999px; font-weight: 800; font-size: 14px; letter-spacing: 0.02em; box-shadow: 0 6px 18px rgba(0, 135, 255, 0.35); }
        .info-card { background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 14px; padding: 20px 24px; margin: 24px 0; }
        .info-card .label { font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 4px; display: block; }
        .info-card .value { font-size: 15px; font-weight: 700; color: #0f172a; margin: 0; }
        .badge-pill { display: inline-block; padding: 4px 12px; border-radius: 999px; background: rgba(0, 135, 255, 0.1); color: #0087FF; font-size: 12px; font-weight: 800; text-transform: uppercase; }
        .email-footer { background-color: #f8fafc; padding: 28px 24px; text-align: center; border-top: 1px solid #e2e8f0; font-size: 12px; color: #64748b; }
        .email-footer a { color: #0087FF; text-decoration: none; font-weight: 600; }
    </style>
</head>
<body>
    <div class="wrapper">
        <table class="main-table" cellpadding="0" cellspacing="0">
            <tr>
                <td class="email-header">
                    <div class="brand-title">KENDAT TECH</div>
                    <div class="brand-sub">Enterprise Software &amp; AI Platform</div>
                </td>
            </tr>
            <tr>
                <td class="email-body">
                    {$bodyHtml}
                </td>
            </tr>
            <tr>
                <td class="email-footer">
                    &copy; {$year} Kendat Integrated Services. All rights reserved.<br>
                    Need assistance? Contact our engineering team at <a href="mailto:hello@kisprojectslab.com">hello@kisprojectslab.com</a>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
HTML;
}

function get_public_email_base_url(): string {
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    if (strpos($host, 'localhost') !== false || strpos($host, '127.0.0.1') !== false) {
        return 'https://www.kisprojectslab.com/';
    }
    $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    return $scheme . '://' . $host . get_base_url();
}

/**
 * 1. Account Activation Email
 */
function send_account_activation_email(string $userEmail, string $userName, string $activationToken): bool {
    $baseUrl = get_public_email_base_url();
    $activationLink = $baseUrl . "activate.php?email=" . urlencode($userEmail) . "&token=" . urlencode($activationToken);

    $subject = "Activate Your Account - Kendat Integrated Services";
    $body = <<<HTML
<h2>Welcome to Kendat Tech, {$userName}!</h2>
<p>Thank you for registering your client workspace. To complete your registration and activate your account, please click the button below:</p>

<div class="btn-wrapper">
    <a href="{$activationLink}" class="btn-primary" target="_blank">Activate My Account &rarr;</a>
</div>

<div class="info-card">
    <span class="label">Direct Activation Link</span>
    <p style="margin:6px 0 0; word-break:break-all; font-size:13px; color:#0087FF;"><a href="{$activationLink}" style="color:#0087FF;">{$activationLink}</a></p>
</div>

<p>Once activated, you can log in, book consultation demos, submit project requests, and track your ongoing software builds.</p>
<p>If you did not register for an account, you can safely ignore this email.</p>
HTML;

    $html = build_branded_email_html($subject, $body);
    return send_smtp_email($userEmail, $userName, $subject, $html);
}

/**
 * 2. Project Request Receipt Confirmation to User
 */
function send_project_request_receipt_email(string $userEmail, string $userName, string $projectType, string $category, string $budget): bool {
    $subject = "Project Request Received - " . $projectType;
    $body = <<<HTML
<h2>We Have Received Your Project Request!</h2>
<p>Dear <strong>{$userName}</strong>,</p>
<p>Thank you for submitting your enterprise software requirement to Kendat Integrated Services. Our software architecture team has received your submission and is reviewing your specifications.</p>

<div class="info-card">
    <span class="label">Requirement Summary</span>
    <p class="value" style="margin-bottom:8px;"><strong>System Type:</strong> {$projectType}</p>
    <p class="value" style="margin-bottom:8px;"><strong>Category:</strong> {$category}</p>
    <p class="value"><strong>Budget Range:</strong> {$budget}</p>
</div>

<p>One of our lead solution engineers will review your request and get back to you shortly with technical feedback and project scope options.</p>
HTML;

    $html = build_branded_email_html($subject, $body);
    return send_smtp_email($userEmail, $userName, $subject, $html);
}

/**
 * 3. Project Request Alert to Admin
 */
function send_admin_project_request_alert(string $userName, string $userEmail, string $phone, string $projectType, string $budget, string $description): bool {
    $adminEmail = 'hello@kisprojectslab.com';
    $subject = "⚡ New Project Request: " . $projectType . " (from " . $userName . ")";
    
    $descEscaped = nl2br(htmlspecialchars($description));
    $body = <<<HTML
<h2>New Project Request Submitted</h2>
<p>A client has submitted a new project requirement on the Kendat platform.</p>

<div class="info-card">
    <span class="label">Client Details</span>
    <p class="value" style="margin-bottom:4px;"><strong>Name:</strong> {$userName}</p>
    <p class="value" style="margin-bottom:4px;"><strong>Email:</strong> {$userEmail}</p>
    <p class="value"><strong>Phone:</strong> {$phone}</p>
</div>

<div class="info-card">
    <span class="label">Project Specifications</span>
    <p class="value" style="margin-bottom:4px;"><strong>Type:</strong> {$projectType}</p>
    <p class="value" style="margin-bottom:8px;"><strong>Budget:</strong> {$budget}</p>
    <span class="label" style="margin-top:10px;">Requirement Details:</span>
    <p style="margin:4px 0 0; font-size:14px; color:#334155;">{$descEscaped}</p>
</div>

<p>Please log in to the admin console to update status and send feedback to the client.</p>
HTML;

    $html = build_branded_email_html($subject, $body);
    return send_smtp_email($adminEmail, "Kendat Admin", $subject, $html);
}

/**
 * 4. Project Request Status Attended/Updated Email to User
 */
function send_project_request_status_email(string $userEmail, string $userName, string $projectType, string $status, string $adminFeedback): bool {
    $statusFormatted = strtoupper(str_replace('_', ' ', $status));
    $subject = "Project Request Status Updated: " . $projectType . " [" . $statusFormatted . "]";
    
    $feedbackBlock = '';
    if (!empty($adminFeedback)) {
        $feedbackEscaped = nl2br(htmlspecialchars($adminFeedback));
        $feedbackBlock = <<<HTML
<div class="info-card">
    <span class="label">Admin Notes &amp; Feedback</span>
    <p style="margin:6px 0 0; font-size:14px; color:#0f172a; font-weight:600;">{$feedbackEscaped}</p>
</div>
HTML;
    }

    $body = <<<HTML
<h2>Update on Your Project Request</h2>
<p>Dear <strong>{$userName}</strong>,</p>
<p>Our engineering team has reviewed and attended to your project request for <strong>{$projectType}</strong>.</p>

<div class="info-card">
    <span class="label">Current Status</span>
    <p class="value"><span class="badge-pill">{$statusFormatted}</span></p>
</div>

{$feedbackBlock}

<p>Log in to your client dashboard at any time to monitor status updates or communicate further with our team.</p>
HTML;

    $html = build_branded_email_html($subject, $body);
    return send_smtp_email($userEmail, $userName, $subject, $html);
}

/**
 * 5. Demo / Appointment Receipt Email to User
 */
function send_appointment_receipt_email(string $userEmail, string $userName, string $date, string $time, string $type): bool {
    $subject = "Demo Consultation Booking Received - Kendat Tech";
    $body = <<<HTML
<h2>Demo &amp; Consultation Booking Received</h2>
<p>Dear <strong>{$userName}</strong>,</p>
<p>Thank you for scheduling a demo consultation session with Kendat Integrated Services.</p>

<div class="info-card">
    <span class="label">Booking Details</span>
    <p class="value" style="margin-bottom:4px;"><strong>Topic / Type:</strong> {$type}</p>
    <p class="value" style="margin-bottom:4px;"><strong>Preferred Date:</strong> {$date}</p>
    <p class="value"><strong>Preferred Time:</strong> {$time}</p>
</div>

<p>Our team is reviewing your schedule request and will confirm your meeting slot shortly.</p>
HTML;

    $html = build_branded_email_html($subject, $body);
    return send_smtp_email($userEmail, $userName, $subject, $html);
}

/**
 * 6. Demo / Appointment Alert to Admin
 */
function send_admin_appointment_alert(string $userName, string $userEmail, string $phone, string $date, string $time, string $type, string $message): bool {
    $adminEmail = 'hello@kisprojectslab.com';
    $subject = "📅 New Demo Booking: " . $type . " (from " . $userName . ")";
    
    $msgEscaped = nl2br(htmlspecialchars($message));
    $body = <<<HTML
<h2>New Demo Consultation Booking</h2>
<p>A client has requested a demo consultation session on the platform.</p>

<div class="info-card">
    <span class="label">Client Information</span>
    <p class="value" style="margin-bottom:4px;"><strong>Name:</strong> {$userName}</p>
    <p class="value" style="margin-bottom:4px;"><strong>Email:</strong> {$userEmail}</p>
    <p class="value"><strong>Phone:</strong> {$phone}</p>
</div>

<div class="info-card">
    <span class="label">Session Details</span>
    <p class="value" style="margin-bottom:4px;"><strong>Type:</strong> {$type}</p>
    <p class="value" style="margin-bottom:4px;"><strong>Date:</strong> {$date}</p>
    <p class="value" style="margin-bottom:8px;"><strong>Time:</strong> {$time}</p>
    <span class="label">Notes / Objectives:</span>
    <p style="margin:4px 0 0; font-size:14px; color:#334155;">{$msgEscaped}</p>
</div>

<p>Please log in to the admin console to confirm or update this consultation appointment.</p>
HTML;

    $html = build_branded_email_html($subject, $body);
    return send_smtp_email($adminEmail, "Kendat Admin", $subject, $html);
}

/**
 * 7. Demo / Appointment Status Update Email to User
 */
function send_appointment_status_email(string $userEmail, string $userName, string $date, string $time, string $status, string $adminNote): bool {
    $statusFormatted = strtoupper(str_replace('_', ' ', $status));
    $subject = "Demo Consultation Status Update: [" . $statusFormatted . "]";
    
    $noteBlock = '';
    if (!empty($adminNote)) {
        $noteEscaped = nl2br(htmlspecialchars($adminNote));
        $noteBlock = <<<HTML
<div class="info-card">
    <span class="label">Admin Meeting Notes</span>
    <p style="margin:6px 0 0; font-size:14px; color:#0f172a; font-weight:600;">{$noteEscaped}</p>
</div>
HTML;
    }

    $body = <<<HTML
<h2>Update on Your Demo Consultation</h2>
<p>Dear <strong>{$userName}</strong>,</p>
<p>Your demo consultation appointment scheduled for <strong>{$date} at {$time}</strong> has been updated.</p>

<div class="info-card">
    <span class="label">Appointment Status</span>
    <p class="value"><span class="badge-pill">{$statusFormatted}</span></p>
</div>

{$noteBlock}

<p>Thank you for choosing Kendat Integrated Services for your software and AI solutions.</p>
HTML;

    $html = build_branded_email_html($subject, $body);
    return send_smtp_email($userEmail, $userName, $subject, $html);
}

/**
 * 8. Admin Broadcast / Direct Custom Email
 */
function send_admin_custom_email(string $toEmail, string $toName, string $subject, string $categoryBadge, string $messageBody, string $ctaText = '', string $ctaUrl = ''): bool {
    $badgePill = '';
    if (!empty($categoryBadge)) {
        $badgeFormatted = strtoupper(htmlspecialchars($categoryBadge));
        $badgePill = "<span class=\"badge-pill\" style=\"margin-bottom:12px; display:inline-block;\">{$badgeFormatted}</span>";
    }

    $ctaBlock = '';
    if (!empty($ctaText) && !empty($ctaUrl)) {
        $ctaTextEsc = htmlspecialchars($ctaText);
        $ctaUrlEsc = htmlspecialchars($ctaUrl);
        $ctaBlock = <<<HTML
<div class="btn-wrapper">
    <a href="{$ctaUrlEsc}" class="btn-primary" target="_blank">{$ctaTextEsc} &rarr;</a>
</div>
HTML;
    }

    $formattedMessage = nl2br(htmlspecialchars($messageBody));

    $body = <<<HTML
{$badgePill}
<h2>{$subject}</h2>
<p>Dear <strong>{$toName}</strong>,</p>

<div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:14px; padding:24px; margin:20px 0; font-size:15px; color:#334155; line-height:1.65;">
    {$formattedMessage}
</div>

{$ctaBlock}

<p style="font-size:13px; color:#64748b; margin-top:24px;">This is an official communication from Kendat Integrated Services Engineering &amp; Client Relations Team.</p>
HTML;

    $html = build_branded_email_html($subject, $body);
    return send_smtp_email($toEmail, $toName, $subject, $html);
}
