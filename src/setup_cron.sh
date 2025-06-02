#!/bin/bash

CRON_FILE="/tmp/task_scheduler_cron"
PHP_PATH=$(which php)
PROJECT_DIR=$(cd "$(dirname "$0")"; pwd)
CRON_CMD="$PHP_PATH $PROJECT_DIR/cron.php > /dev/null 2>&1"

# Remove existing matching cron job
crontab -l 2>/dev/null | grep -v "$CRON_CMD" > "$CRON_FILE"

# Add the new cron job
echo "*/2 * * * * $CRON_CMD" >> "$CRON_FILE"

# Install updated cron
crontab "$CRON_FILE"
rm "$CRON_FILE"

echo "CRON job set to run every hour."
