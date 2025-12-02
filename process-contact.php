<?php
// process-contact.php
// Process contact form submissions

// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Your email address where inquiries will be sent
$to_email = "tracksadventuresafaris2016@gmail.com";

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect and sanitize form data
    $first_name = sanitize_input($_POST['first_name']);
    $last_name = sanitize_input($_POST['last_name']);
    $email = sanitize_input($_POST['email']);
    $phone = sanitize_input($_POST['phone']);
    $country = sanitize_input($_POST['country']);
    $subject = sanitize_input($_POST['subject']);
    $message = sanitize_input($_POST['message']);
    
    // Get client IP address
    $ip_address = $_SERVER['REMOTE_ADDR'];
    
    // Get current date and time
    $submission_date = date("F j, Y, g:i a");
    
    // Validate required fields
    $errors = [];
    
    if (empty($first_name)) $errors[] = "First name is required";
    if (empty($last_name)) $errors[] = "Last name is required";
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    if (empty($phone)) $errors[] = "Phone number is required";
    if (empty($country)) $errors[] = "Country is required";
    if (empty($subject)) $errors[] = "Subject is required";
    if (empty($message)) $errors[] = "Message is required";
    
    // If there are errors, show them
    if (!empty($errors)) {
        show_error_page($errors);
        exit();
    }
    
    // Prepare email content
    $email_subject = "New Contact Form Submission: " . format_subject($subject);
    
    $email_message = "========================================\n";
    $email_message .= "CONTACT FORM SUBMISSION\n";
    $email_message .= "========================================\n\n";
    
    $email_message .= "CLIENT INFORMATION:\n";
    $email_message .= "-------------------\n";
    $email_message .= "Name: $first_name $last_name\n";
    $email_message .= "Email: $email\n";
    $email_message .= "Phone: $phone\n";
    $email_message .= "Country: $country\n";
    $email_message .= "Subject: " . format_subject($subject) . "\n\n";
    
    $email_message .= "MESSAGE:\n";
    $email_message .= "--------\n";
    $email_message .= wordwrap($message, 70) . "\n\n";
    
    $email_message .= "========================================\n";
    $email_message .= "SUBMISSION DETAILS:\n";
    $email_message .= "Submission Date: $submission_date\n";
    $email_message .= "IP Address: $ip_address\n";
    $email_message .= "Form: Main Contact Form\n";
    $email_message .= "========================================\n";
    
    // Email headers
    $headers = "From: TRACKS Adventure Safaris <noreply@tracksadventuresafaris.com>\r\n";
    $headers .= "Reply-To: $email\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();
    
    // Additional headers for better deliverability
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    
    // Send email to you
    $mail_sent = mail($to_email, $email_subject, $email_message, $headers);
    
    // Also send confirmation email to client
    if ($mail_sent) {
        send_confirmation_email($email, $first_name . " " . $last_name, $subject);
    }
    
    // Store in database if needed (optional)
    store_contact_in_database($first_name, $last_name, $email, $phone, $country, $subject, $message, $ip_address);
    
    // Redirect to thank you page
    header("Location: thank-you-contact.html");
    exit();
    
} else {
    // If someone tries to access this page directly, redirect to contact page
    header("Location: contact.html");
    exit();
}

// Function to sanitize form input
function sanitize_input($data) {
    if (empty($data)) return "";
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// Function to format subject for display
function format_subject($subject_key) {
    $subjects = [
        'safari-inquiry' => 'Safari Inquiry',
        'booking-information' => 'Booking Information',
        'custom-safari' => 'Custom Safari Request',
        'group-booking' => 'Group/Corporate Booking',
        'general-question' => 'General Question',
        'partnership' => 'Partnership Opportunity'
    ];
    return $subjects[$subject_key] ?? ucwords(str_replace('-', ' ', $subject_key));
}

// Function to send confirmation email to client
function send_confirmation_email($client_email, $client_name, $subject) {
    $subject_line = "Thank You for Contacting TRACKS Adventure Safaris";
    
    $message = "Dear $client_name,\n\n";
    $message .= "Thank you for contacting TRACKS Adventure Safaris!\n\n";
    $message .= "We have received your message regarding \"" . format_subject($subject) . "\" and one of our safari specialists will review it shortly.\n\n";
    $message .= "We aim to respond to all inquiries within 24-48 hours during our business hours (Monday-Friday, 8:00 AM - 6:00 PM Botswana time).\n\n";
    $message .= "For urgent inquiries, you can also reach us directly:\n";
    $message .= "📞 Phone: +267 72114790 / +267 73654794 / +267 74207072\n";
    $message .= "📱 WhatsApp: +267 72114790\n";
    $message .= "📧 Email: tracksadventuresafaris2016@gmail.com\n";
    $message .= "📍 Office: New mall opposite Museum, Maun, Botswana\n\n";
    $message .= "While you wait, you might want to:\n";
    $message .= "• Browse our safari packages: https://www.tracksadventuresafaris.com/#packages\n";
    $message .= "• View our gallery: https://www.tracksadventuresafaris.com/gallery.html\n";
    $message .= "• Design your own safari: https://www.tracksadventuresafaris.com/custom-safari.html\n\n";
    $message .= "We look forward to helping you plan your Botswana adventure!\n\n";
    $message .= "Best regards,\n";
    $message .= "The TRACKS Adventure Safaris Team\n";
    $message .= "www.tracksadventuresafaris.com\n";
    $message .= "\"Life is a safari, let's conquer it together\"\n";
    $message .= "----------------------------------------\n";
    $message .= "This is an automated response. Please do not reply to this email.\n";
    
    $headers = "From: TRACKS Adventure Safaris <noreply@tracksadventuresafaris.com>\r\n";
    $headers .= "Reply-To: tracksadventuresafaris2016@gmail.com\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    
    mail($client_email, $subject_line, $message, $headers);
}

// Function to store contact in database (optional - requires database setup)
function store_contact_in_database($first_name, $last_name, $email, $phone, $country, $subject, $message, $ip_address) {
    
    // Database configuration - UPDATE THESE WITH YOUR DATABASE CREDENTIALS
    $db_host = "localhost";
    $db_name = "tracks_safaris";
    $db_user = "your_database_username";
    $db_pass = "your_database_password";
    
    try {
        // Create connection
        $conn = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Prepare SQL statement
        $sql = "INSERT INTO contact_submissions (
                    first_name, last_name, email, phone, country,
                    subject, message, ip_address, submitted_at
                ) VALUES (
                    :first_name, :last_name, :email, :phone, :country,
                    :subject, :message, :ip_address, NOW()
                )";
        
        $stmt = $conn->prepare($sql);
        
        // Bind parameters
        $stmt->bindParam(':first_name', $first_name);
        $stmt->bindParam(':last_name', $last_name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':country', $country);
        $stmt->bindParam(':subject', $subject);
        $stmt->bindParam(':message', $message);
        $stmt->bindParam(':ip_address', $ip_address);
        
        // Execute query
        $stmt->execute();
        
        // Close connection
        $conn = null;
        
    } catch(PDOException $e) {
        // Log error but don't show to user (could create error log file)
        error_log("Database error: " . $e->getMessage());
        // Continue processing even if database fails
    }
}

// Function to show error page
function show_error_page($errors) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Form Submission Error - TRACKS Adventure Safaris</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }
            
            body {
                font-family: 'Inter', sans-serif;
                background: linear-gradient(135deg, #7A3E1E, #4A2C1A);
                color: white;
                min-height: 100vh;
                display: flex;
                justify-content: center;
                align-items: center;
                padding: 20px;
            }
            
            .error-container {
                background: white;
                color: #333;
                padding: 40px;
                border-radius: 15px;
                box-shadow: 0 15px 40px rgba(0,0,0,0.2);
                max-width: 600px;
                width: 100%;
                text-align: center;
            }
            
            .error-icon {
                font-size: 60px;
                color: #C62828;
                margin-bottom: 20px;
            }
            
            h1 {
                color: #4A2C1A;
                margin-bottom: 20px;
                font-family: 'Playfair Display', serif;
            }
            
            .error-list {
                text-align: left;
                background: #F8F4E8;
                padding: 20px;
                border-radius: 10px;
                margin: 20px 0;
            }
            
            .error-list ul {
                list-style-position: inside;
            }
            
            .error-list li {
                margin-bottom: 10px;
                color: #C62828;
            }
            
            .btn {
                display: inline-block;
                padding: 12px 30px;
                background: linear-gradient(135deg, #C9A85E, #E2C98D);
                color: #4A2C1A;
                text-decoration: none;
                border-radius: 50px;
                font-weight: 600;
                margin-top: 20px;
                transition: all 0.3s ease;
                border: none;
                cursor: pointer;
                font-size: 16px;
            }
            
            .btn:hover {
                transform: translateY(-3px);
                box-shadow: 0 10px 20px rgba(201, 168, 94, 0.3);
            }
        </style>
    </head>
    <body>
        <div class="error-container">
            <div class="error-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h1>Form Submission Error</h1>
            <p>There were errors with your form submission. Please fix the following issues:</p>
            
            <div class="error-list">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            
            <p>Please go back and correct these errors before submitting again.</p>
            
            <button class="btn" onclick="window.history.back()">
                <i class="fas fa-arrow-left"></i> Go Back & Fix Errors
            </button>
            
            <p style="margin-top: 30px; font-size: 14px; color: #666;">
                If you continue to experience issues, please contact us directly:<br>
                <i class="fas fa-phone"></i> +267 72114790 &nbsp;|&nbsp;
                <i class="fab fa-whatsapp"></i> +267 72114790 &nbsp;|&nbsp;
                <i class="fas fa-envelope"></i> tracksadventuresafaris2016@gmail.com
            </p>
        </div>
    </body>
    </html>
    <?php
}
?>