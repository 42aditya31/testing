<?php
require_once 'functions.php';

$unsubscribed = false;

if (isset($_GET['email'])) {
    $email = $_GET['email'];
    if (unsubscribeEmail($email)) {
        $unsubscribed = true;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<title>Unsubscribe</title>
</head>
<body>
	<h2 id="unsubscription-heading">Unsubscribe from Task Updates</h2>
	<p>
		<?php if ($unsubscribed): ?>
			You have been unsubscribed successfully.
		<?php else: ?>
			Unsubscription failed. The link may be invalid.
		<?php endif; ?>
	</p>
</body>
</html>
