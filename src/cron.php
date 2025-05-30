<?php
require_once 'functions.php';

// Prevent output during CRON run
ob_start();
sendTaskReminders();
ob_end_clean();
