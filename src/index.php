<?php
require_once 'functions.php';

$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	if (isset($_POST['task-name'])) {
		$task_name = trim($_POST['task-name']);
		if ($task_name !== '') {
			if (addTask($task_name)) {
				$success_message = 'Task added successfully.';
			} else {
				$error_message = 'Task already exists.';
			}
		}
	}

	if (isset($_POST['complete-task-id'])) {
		$id = $_POST['complete-task-id'];
		$done = isset($_POST['status']) ? true : false;
		markTaskAsCompleted($id, $done);
	}

	if (isset($_POST['delete-task-id'])) {
		deleteTask($_POST['delete-task-id']);
	}

	if (isset($_POST['email'])) {
		$email = trim($_POST['email']);
		if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
			if (subscribeEmail($email)) {
				$success_message = 'Verification email sent.';
			} else {
				$error_message = 'Could not send verification email.';
			}
		} else {
			$error_message = 'Invalid email address.';
		}
	}
}

$tasks = getAllTasks();
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Task Scheduler</title>
	<style>
		* {
			box-sizing: border-box;
		}

		body {
			font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
			background-color: #f4f6f9;
			margin: 0;
			padding: 20px;
			color: #333;
		}

		h1 {
			text-align: center;
			color: #333;
		}

		form {
			margin-bottom: 20px;
		}

		input[type="text"],
		input[type="email"] {
			padding: 10px;
			width: 70%;
			max-width: 400px;
			border: 1px solid #ccc;
			border-radius: 6px;
			outline: none;
			margin-right: 10px;
		}

		button {
			padding: 10px 15px;
			background-color: #007BFF;
			color: white;
			border: none;
			border-radius: 6px;
			cursor: pointer;
			transition: background-color 0.3s ease;
		}

		button:hover {
			background-color: #0056b3;
		}

		.tasks-list {
			list-style: none;
			padding: 0;
			max-width: 600px;
			margin: auto;
		}

		.task-item {
			background-color: white;
			margin-bottom: 10px;
			padding: 15px 20px;
			border-radius: 8px;
			display: flex;
			justify-content: space-between;
			align-items: center;
			box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
		}

		.task-item.completed span {
			text-decoration: line-through;
			color: #888;
		}

		.task-item form {
			display: inline;
		}

		.task-status {
			margin-right: 10px;
		}

		.delete-task {
			background-color: #dc3545;
		}

		.delete-task:hover {
			background-color: #a71d2a;
		}

		.message {
			text-align: center;
			margin-bottom: 20px;
			font-weight: bold;
		}

		.message.success {
			color: #28a745;
		}

		.message.error {
			color: #dc3545;
		}

		.center-form {
			text-align: center;
			margin-bottom: 30px;
		}
	</style>
</head>
<body>

	<h1>Task Scheduler</h1>

	<?php if ($success_message): ?>
		<p class="message success"><?= htmlspecialchars($success_message) ?></p>
	<?php endif; ?>
	<?php if ($error_message): ?>
		<p class="message error"><?= htmlspecialchars($error_message) ?></p>
	<?php endif; ?>

	<!-- Add Task Form -->
	<div class="center-form">
		<form method="POST">
			<input type="text" name="task-name" id="task-name" placeholder="Enter new task" required>
			<button type="submit" id="add-task">Add Task</button>
		</form>
	</div>

	<!-- Tasks List -->
	<ul class="tasks-list">
		<?php foreach ($tasks as $task): ?>
			<li class="task-item <?= $task['completed'] ? 'completed' : '' ?>">
				<span>
					<form method="POST" style="display:inline;">
						<input type="hidden" name="complete-task-id" value="<?= $task['id'] ?>">
						<input type="checkbox" class="task-status" name="status" onchange="this.form.submit()" <?= $task['completed'] ? 'checked' : '' ?>>
					</form>
					<span><?= htmlspecialchars($task['name']) ?></span>
				</span>
				<form method="POST" style="display:inline;">
					<input type="hidden" name="delete-task-id" value="<?= $task['id'] ?>">
					<button type="submit" class="delete-task">Delete</button>
				</form>
			</li>
		<?php endforeach; ?>
	</ul>

	<!-- Subscription Form -->
	<div class="center-form">
		<form method="POST">
			<input type="email" name="email" placeholder="Enter your email" required />
			<button type="submit" id="submit-email">Subscribe</button>
		</form>
	</div>

</body>
</html>
