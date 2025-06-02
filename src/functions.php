<?php

function addTask(string $task_name): bool {
    $file = __DIR__ . '/tasks.txt';
    $tasks = getAllTasks();

    foreach ($tasks as $task) {
        if (strcasecmp($task['name'], $task_name) === 0) {
            return false;
        }
    }

    $id = uniqid();
    $line = implode('|', [$id, $task_name, '0']) . PHP_EOL;
    return file_put_contents($file, $line, FILE_APPEND) !== false;
}

function getAllTasks(): array {
    $file = __DIR__ . '/tasks.txt';
    if (!file_exists($file)) return [];

    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $tasks = [];

    foreach ($lines as $line) {
        [$id, $name, $completed] = explode('|', $line);
        $tasks[] = [
            'id' => $id,
            'name' => $name,
            'completed' => $completed === '1'
        ];
    }

    return $tasks;
}

function markTaskAsCompleted(string $task_id, bool $is_completed): bool {
    $file = __DIR__ . '/tasks.txt';
    $tasks = getAllTasks();
    $updated = false;

    foreach ($tasks as &$task) {
        if ($task['id'] === $task_id) {
            $task['completed'] = $is_completed;
            $updated = true;
        }
    }

    if (!$updated) return false;

    $lines = array_map(fn($t) => implode('|', [$t['id'], $t['name'], $t['completed'] ? '1' : '0']), $tasks);
    return file_put_contents($file, implode(PHP_EOL, $lines) . PHP_EOL) !== false;
}

function deleteTask(string $task_id): bool {
    $file = __DIR__ . '/tasks.txt';
    $tasks = getAllTasks();

    $tasks = array_filter($tasks, fn($task) => $task['id'] !== $task_id);
    $lines = array_map(fn($t) => implode('|', [$t['id'], $t['name'], $t['completed'] ? '1' : '0']), $tasks);
    return file_put_contents($file, implode(PHP_EOL, $lines) . PHP_EOL) !== false;
}

function generateVerificationCode(): string {
    return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
}


function subscribeEmail(string $email): bool {
    // TODO: Implement this function
    $file = __DIR__ . '/pending_subscriptions.txt';
    $code = generateVerificationCode();

    $lines = file_exists($file) ? file($file, FILE_IGNORE_NEW_LINES) : [];
    foreach ($lines as $line) {
        [$e] = explode('|', $line);
        if (strcasecmp($e, $email) === 0) return false;
    }

    file_put_contents($file, $email . '|' . $code . PHP_EOL, FILE_APPEND);

    // $link = 'http://' . $_SERVER['HTTP_HOST'] . '/verify.php?';

    $link = 'http://' . $_SERVER['HTTP_HOST'] . '/verify.php?email=' . urlencode($email) . '&code=' . urlencode($code);

    // $body = "<p>Click the link below to verify your subscription to Task Planner:</p>
    // <p><a id=\"verification-link\" href=\"$link\">Verify Subscription</a></p>";

    $subject = "Your Verification Code";
    $message = "
    <p>Click the link below to verify your subscription to Task Planner:</p>
    <p><a id=\"verification-link\" href=\"$link\">Verify Subscription</a></p>
    <p>Your verification code is: <strong>{$code}</strong> </p>";
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8\r\n";
    $headers .= "From: no-reply@example.com\r\n";
    return mail($email, $subject, $message, $headers);
}




// function verifySubscription(string $email, string $code): bool {
//     $pending_file = __DIR__ . '/pending_subscriptions.txt';
//     $subs_file = __DIR__ . '/subscribers.txt';

//     if (!file_exists($pending_file)) return false;

//     $lines = file($pending_file, FILE_IGNORE_NEW_LINES);
//     $verified = false;

//     $lines = array_filter($lines, function ($line) use ($email, $code, &$verified, $subs_file) {
//         [$e, $c] = explode('|', $line);
//         if (strcasecmp($e, $email) === 0 && $c === $code) {
//             file_put_contents($subs_file, $email . PHP_EOL, FILE_APPEND);
//             $verified = true;
//             return false;
//         }
//         return true;
//     });

//     file_put_contents($pending_file, implode(PHP_EOL, $lines) . PHP_EOL);
//     return $verified;
// }

function verifySubscription(string $email, string $code): bool {
    $pending_file = __DIR__ . '/pending_subscriptions.txt';
    $subs_file = __DIR__ . '/subscribers.txt';

    if (!file_exists($pending_file)) return false;

    $lines = file($pending_file, FILE_IGNORE_NEW_LINES);
    $verified = false;

    $lines = array_filter($lines, function ($line) use ($email, $code, &$verified, $subs_file) {
        $parts = explode('|', $line);

        // ✅ Check if we got exactly 2 parts (email and code)
        if (count($parts) !== 2) {
            return true; // Keep malformed or blank lines
        }

        [$e, $c] = $parts;
        if (strcasecmp($e, $email) === 0 && $c === $code) {
            file_put_contents($subs_file, $email . PHP_EOL, FILE_APPEND);
            $verified = true;
            return false; // Remove this matched line from pending
        }

        return true; // Keep this line
    });

    file_put_contents($pending_file, implode(PHP_EOL, $lines) . PHP_EOL);
    return $verified;
}


function unsubscribeEmail(string $email): bool {
    $file = __DIR__ . '/subscribers.txt';
    if (!file_exists($file)) return false;

    $lines = file($file, FILE_IGNORE_NEW_LINES);
    $filtered = array_filter($lines, fn($e) => strcasecmp(trim($e), $email) !== 0);
    return file_put_contents($file, implode(PHP_EOL, $filtered) . PHP_EOL) !== false;
}

function sendTaskReminders(): void {
    $subs_file = __DIR__ . '/subscribers.txt';
    $subs = file_exists($subs_file) ? file($subs_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];
    $tasks = array_filter(getAllTasks(), fn($t) => !$t['completed']);
    $task_names = array_map(fn($t) => $t['name'], $tasks);

    foreach ($subs as $email) {
        sendTaskEmail($email, $task_names);
    }
}
// NOTE: mail() works only if your PHP environment has a properly configured SMTP server.
// The function logic is correct, but you need to set up an SMTP server or use a mail service
// for emails to actually be sent. This warning means no SMTP server found at localhost:25.


// function sendTaskEmail(string $email, array $pending_tasks): bool {
//     $items = array_map(fn($t) => "<li>$t</li>", $pending_tasks);
//     $unsubscribe_link = 'http://' . $_SERVER['HTTP_HOST'] . '/unsubscribe.php?email=' . urlencode($email);

//      $link = 'http://' . $_SERVER['HTTP_HOST'] . '/verify.php?email=' . urlencode($email);

//     $body = "<h2>Pending Tasks Reminder</h2>
// <p>Here are the current pending tasks:</p>
// <ul>" . implode('', $items) . "</ul>
// <p><a id=\"unsubscribe-link\" href=\"$unsubscribe_link\">Unsubscribe from notifications</a></p>";

//     return mail($email, 'Task Planner - Pending Tasks Reminder', $body, "Content-Type: text/html\r\nFrom: no-reply@example.com");
// }


function sendTaskEmail(string $email, array $pending_tasks): bool {
    // Convert each task into an HTML list item, ensuring safety via escaping
    $items = array_map(
        fn($t) => "<li>" . htmlspecialchars($t) . "</li>",
        $pending_tasks
    );

    // Generate custom port-aware base URL
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $port = $_SERVER['SERVER_PORT'] ?? '8000';

    // If port is not already part of the host, append it manually
    if (!str_contains($host, ':')) {
        $host .= ':' . $port;
    }

    // Construct the unsubscribe link
    $unsubscribe_link = 'http://' . $host . '/unsubscribe.php?email=' . urlencode($email);

    // Build the HTML email body
    $body  = "<html><body>";
    $body .= "<h2>Pending Tasks Reminder</h2>";
    $body .= "<p>Here are the current pending tasks:</p>";
    $body .= "<ul>" . implode('', $items) . "</ul>";
    $body .= "<p><a id=\"unsubscribe-link\" href=\"$unsubscribe_link\">Unsubscribe from notifications</a></p>";
    $body .= "</body></html>";

    // Set email headers
    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: no-reply@example.com\r\n";

    // Send the email and return the result
    return mail($email, 'Task Planner - Pending Tasks Reminder', $body, $headers);
}




