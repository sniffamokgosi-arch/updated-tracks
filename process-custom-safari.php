<?php
// process-custom-safari.php
// Process custom safari form submissions

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
    $special_requests = sanitize_input($_POST['special_requests']);
    
    // Collect form selection data
    $destinations = json_decode($_POST['destinations'], true) ?? [];
    $duration = sanitize_input($_POST['duration']);
    $activities = json_decode($_POST['activities'], true) ?? [];
    $accommodation = sanitize_input($_POST['accommodation']);
    $group_size = sanitize_input($_POST['group_size']);
    $budget_range = sanitize_input($_POST['budget_range']);
    $travel_month = sanitize_input($_POST['travel_month']);
    $travel_year = sanitize_input($_POST['travel_year']);
    
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
    
    // If there are errors, show them
    if (!empty($errors)) {
        show_error_page($errors);
        exit();
    }
    
    // Prepare email content
    $subject = "New Custom Safari Request - TRACKS Adventure Safaris";
    
    $message = "========================================\n";
    $message .= "CUSTOM SAFARI REQUEST\n";
    $message .= "========================================\n\n";
    
    $message .= "CLIENT INFORMATION:\n";
    $message .= "-------------------\n";
    $message .= "Name: $first_name $last_name\n";
    $message .= "Email: $email\n";
    $message .= "Phone: $phone\n";
    $message .= "Country: $country\n\n";
    
    $message .= "SAFARI PREFERENCES:\n";
    $message .= "-------------------\n";
    
    // Destinations
    if (!empty($destinations)) {
        $message .= "Destinations: ";
        $dest_names = [];
        foreach ($destinations as $dest) {
            $dest_names[] = format_destination_name($dest);
        }
        $message .= implode(", ", $dest_names) . "\n";
    } else {
        $message .= "Destinations: Not specified\n";
    }
    
    // Duration
    $message .= "Duration: " . format_duration($duration) . "\n";
    
    // Activities
    if (!empty($activities)) {
        $message .= "Activities: ";
        $act_names = [];
        foreach ($activities as $act) {
            $act_names[] = format_activity_name($act);
        }
        $message .= implode(", ", $act_names) . "\n";
    } else {
        $message .= "Activities: Not specified\n";
    }
    
    // Accommodation
    $message .= "Accommodation: " . format_accommodation($accommodation) . "\n";
    
    // Group size
    $message .= "Group Size: " . format_group_size($group_size) . "\n";
    
    // Budget
    $message .= "Budget Range: $budget_range per person\n";
    
    // Travel timing
    if (!empty($travel_month)) {
        $message .= "Preferred Travel: " . format_travel_month($travel_month) . " $travel_year\n";
    } else {
        $message .= "Preferred Travel: $travel_year (flexible dates)\n";
    }
    
    // Special requests
    if (!empty($special_requests)) {
        $message .= "\nSPECIAL REQUESTS:\n";
        $message .= "-----------------\n";
        $message .= "$special_requests\n";
    }
    
    $message .= "\n========================================\n";
    $message .= "Submission Date: " . date("F j, Y, g:i a") . "\n";
    $message .= "IP Address: " . $_SERVER['REMOTE_ADDR'] . "\n";
    $message .= "========================================\n";
    
    // Email headers
    $headers = "From: TRACKS Adventure Safaris <noreply@tracksadventuresafaris.com>\r\n";
    $headers .= "Reply-To: $email\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();
    
    // Additional headers for better deliverability
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    
    // Send email to you
    $mail_sent = mail($to_email, $subject, $message, $headers);
    
    // Also send confirmation email to client
    if ($mail_sent) {
        send_confirmation_email($email, $first_name . " " . $last_name);
    }
    
    // Store in database if needed (optional)
    store_inquiry_in_database($first_name, $last_name, $email, $phone, $country, $destinations, 
                              $duration, $activities, $accommodation, $group_size, 
                              $budget_range, $travel_month, $travel_year, $special_requests);
    
    // Redirect to thank you page
    header("Location: thank-you-custom.html");
    exit();
    
} else {
    // If someone tries to access this page directly, redirect to custom safari page
    header("Location: custom-safari.html");
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

// Function to format destination names
function format_destination_name($dest) {
    $names = [
        'okavango' => 'Okavango Delta',
        'chobe' => 'Chobe National Park',
        'kalahari' => 'Kalahari Desert',
        'makgadikgadi' => 'Makgadikgadi Pans',
        'moremi' => 'Moremi Game Reserve',
        'savuti' => 'Savuti Marsh'
    ];
    return $names[$dest] ?? ucfirst($dest);
}

// Function to format duration
function format_duration($duration) {
    $durations = [
        '3-5' => '3-5 Days (Short Break)',
        '6-8' => '6-8 Days (Standard Safari)',
        '9-12' => '9-12 Days (Extended Safari)',
        '13+' => '13+ Days (Comprehensive Tour)'
    ];
    return $durations[$duration] ?? $duration;
}

// Function to format activity names
function format_activity_name($activity) {
    $names = [
        'game-drives' => 'Game Drives',
        'walking-safaris' => 'Walking Safaris',
        'mokoro' => 'Mokoro Excursions',
        'river-cruises' => 'River Cruises',
        'bird-watching' => 'Bird Watching',
        'photography' => 'Photography',
        'cultural' => 'Cultural Experiences',
        'stargazing' => 'Stargazing'
    ];
    return $names[$activity] ?? ucwords(str_replace('-', ' ', $activity));
}

// Function to format accommodation
function format_accommodation($accommodation) {
    $types = [
        'luxury-lodge' => 'Luxury Lodge',
        'tented-camp' => 'Tented Camp',
        'mobile-camping' => 'Mobile Camping',
        'mixed' => 'Mixed (Lodge & Camping)'
    ];
    return $types[$accommodation] ?? ucwords(str_replace('-', ' ', $accommodation));
}

// Function to format group size
function format_group_size($size) {
    $sizes = [
        '1' => 'Solo Traveler',
        '2' => '2 People (Couple/Friends)',
        '3-4' => '3-4 People (Small Group)',
        '5-6' => '5-6 People (Family/Group)',
        '7-8' => '7-8 People (Large Group)',
        '9+' => '9+ People (Custom Group)'
    ];
    return $sizes[$size] ?? $size . " People";
}

// Function to format travel month
function format_travel_month($month) {
    $months = [
        'jan-mar' => 'January - March (Green Season)',
        'apr-jun' => 'April - June (Shoulder Season)',
        'jul-sep' => 'July - September (Peak Season)',
        'oct-dec' => 'October - December (Hot Season)'
    ];
    return $months[$month] ?? $month;
}

// Function to send confirmation email to client
function send_confirmation_email($client_email, $client_name) {
    $subject = "Custom Safari Request Received - TRACKS Adventure Safaris";
    
    $message = "Dear $client_name,\n\n";
    $message .= "Thank you for submitting your custom safari request to TRACKS Adventure Safaris!\n\n";
    $message .= "We have received your safari preferences and one of our safari specialists will review your request. We aim to respond within 24-48 hours with a personalized itinerary and quote.\n\n";
    $message .= "In the meantime, you can:\n";
    $message .= "1. Browse our safari packages: https://www.tracksadventuresafaris.com/#packages\n";
    $message .= "2. View our gallery: https://www.tracksadventuresafaris.com/gallery.html\n";
    $message .= "3. Read about our company: https://www.tracksadventuresafaris.com/about.html\n\n";
    $message .= "If you have any urgent questions, please contact us directly:\n";
    $message .= "Phone: +267 72114790 / +267 73654794 / +267 74207072\n";
    $message .= "Email: tracksadventuresafaris2016@gmail.com\n";
    $message .= "WhatsApp: +267 72795629\n\n";
    $message .= "Best regards,\n";
    $message .= "The TRACKS Adventure Safaris Team\n";
    $message .= "www.tracksadventuresafaris.com\n";
    $message .= "\"Life is a safari, let's conquer it together\"\n";
    
    $headers = "From: TRACKS Adventure Safaris <noreply@tracksadventuresafaris.com>\r\n";
    $headers .= "Reply-To: tracksadventuresafaris2016@gmail.com\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    
    mail($client_email, $subject, $message, $headers);
}

// Function to store inquiry in database (optional - requires database setup)
function store_inquiry_in_database($first_name, $last_name, $email, $phone, $country, 
                                   $destinations, $duration, $activities, $accommodation, 
                                   $group_size, $budget_range, $travel_month, $travel_year, 
                                   $special_requests) {
    
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
        $sql = "INSERT INTO custom_safari_requests (
                    first_name, last_name, email, phone, country,
                    destinations, duration, activities, accommodation,
                    group_size, budget_range, travel_month, travel_year,
                    special_requests, submitted_at
                ) VALUES (
                    :first_name, :last_name, :email, :phone, :country,
                    :destinations, :duration, :activities, :accommodation,
                    :group_size, :budget_range, :travel_month, :travel_year,
                    :special_requests, NOW()
                )";
        
        $stmt = $conn->prepare($sql);
        
        // Bind parameters
        $stmt->bindParam(':first_name', $first_name);
        $stmt->bindParam(':last_name', $last_name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':country', $country);
        $stmt->bindParam(':destinations', json_encode($destinations));
        $stmt->bindParam(':duration', $duration);
        $stmt->bindParam(':activities', json_encode($activities));
        $stmt->bindParam(':accommodation', $accommodation);
        $stmt->bindParam(':group_size', $group_size);
        $stmt->bindParam(':budget_range', $budget_range);
        $stmt->bindParam(':travel_month', $travel_month);
        $stmt->bindParam(':travel_year', $travel_year);
        $stmt->bindParam(':special_requests', $special_requests);
        
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
                <i class="fas fa-envelope"></i> tracksadventuresafaris2016@gmail.com
            </p>
        </div>
    </body>
    </html>
    <?php
}
?>