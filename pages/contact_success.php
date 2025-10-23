<?php
session_start();

// Check if we have a success message
if (isset($_SESSION['contact_success'])) {
    $success_message = $_SESSION['contact_success'];
    unset($_SESSION['contact_success']);
} else {
    // If no success message, redirect to contact page
    header('Location: contact.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Message Sent Successfully - NT2 Taallessen International</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .success-container {
            max-width: 600px;
            margin: 4rem auto;
            padding: 2rem;
            text-align: center;
            background: white;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(26,54,93,0.1);
        }
        .success-icon {
            font-size: 4rem;
            color: #38a169;
            margin-bottom: 1rem;
        }
        .success-title {
            color: #1a365d;
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }
        .success-message {
            color: #4a5568;
            font-size: 1.1rem;
            margin-bottom: 2rem;
            line-height: 1.6;
        }
        .success-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        .btn {
            padding: 0.8rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-primary {
            background: linear-gradient(135deg, #1a365d, #2c5282);
            color: white;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #2c5282, #1a365d);
            transform: translateY(-2px);
        }
        .btn-secondary {
            background: transparent;
            color: #1a365d;
            border: 2px solid #1a365d;
        }
        .btn-secondary:hover {
            background: #1a365d;
            color: white;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <main>
        <div class="success-container">
            <div class="success-icon">✅</div>
            <h1 class="success-title">Message Sent Successfully!</h1>
            <p class="success-message"><?php echo htmlspecialchars($success_message); ?></p>
            
            <div class="success-actions">
                <a href="contact.php" class="btn btn-secondary">Send Another Message</a>
                <a href="../index.php" class="btn btn-primary">Return to Home</a>
            </div>
        </div>
    </main>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>
