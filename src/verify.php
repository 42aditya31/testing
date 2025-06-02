<?php
require_once 'functions.php';

$verified = false;

if (isset($_GET['email'], $_GET['code'])) {
    $email = $_GET['email'];
    $code  = $_GET['code'];

    if (verifySubscription($email, $code)) {
        $verified = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Subscription Verification</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">

    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif;
            background-color: #f4f6f9;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }

        .container {
            background-color: #fff;
            padding: 2rem 2.5rem;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
            max-width: 450px;
            width: 100%;
            text-align: center;
        }

        h2 {
            font-size: 1.8rem;
            margin-bottom: 1rem;
            color: #2d3748;
        }

        p {
            font-size: 1rem;
            color: #4a5568;
        }

        .success {
            color: #38a169; /* green */
            font-weight: 600;
        }

        .error {
            color: #e53e3e; /* red */
            font-weight: 600;
        }

        @media (max-width: 480px) {
            .container {
                padding: 1.5rem;
            }

            h2 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Subscription Verification</h2>
        <p class="<?= $verified ? 'success' : 'error' ?>">
            <?= $verified
                ? ' Your subscription has been successfully verified!'
                : ' Verification failed. The link may be invalid or expired.' ?>
        </p>
    </div>
</body>
</html>
