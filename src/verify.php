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
<html>
<head>
	<meta charset="UTF-8">
	<title>Verify Subscription</title>
</head>
<body>
	<h2 id="verification-heading">Subscription Verification</h2>
	<p>
		<?php if ($verified): ?>
			Your subscription has been successfully verified.
		<?php else: ?>
			Verification failed. The link may be invalid or expired.
		<?php endif; ?>
	</p>
</body>
</html>
