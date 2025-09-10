#!/bin/bash

# SAMPARK Cron Job Setup Script
# This script sets up the recommended cron jobs for SAMPARK

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}SAMPARK Cron Job Setup${NC}"
echo "======================"

# Get the current directory
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )"
PROJECT_DIR="$(dirname "$SCRIPT_DIR")"
CRON_FILE="$PROJECT_DIR/public/cron.php"

# Check if cron.php exists
if [ ! -f "$CRON_FILE" ]; then
    echo -e "${RED}Error: cron.php not found at $CRON_FILE${NC}"
    exit 1
fi

# Check if PHP is available
if ! command -v php &> /dev/null; then
    echo -e "${RED}Error: PHP is not installed or not in PATH${NC}"
    exit 1
fi

PHP_PATH=$(which php)
echo "PHP found at: $PHP_PATH"
echo "Project directory: $PROJECT_DIR"
echo

# Create cron jobs
echo "Setting up cron jobs..."

# Backup existing crontab
echo "Backing up existing crontab..."
crontab -l > /tmp/crontab_backup_$(date +%Y%m%d_%H%M%S) 2>/dev/null || echo "No existing crontab found"

# Create temporary cron file
TEMP_CRON="/tmp/sampark_cron"

# Get existing crontab (if any)
crontab -l 2>/dev/null > "$TEMP_CRON" || touch "$TEMP_CRON"

# Check if SAMPARK cron jobs already exist
if grep -q "SAMPARK" "$TEMP_CRON"; then
    echo -e "${YELLOW}Warning: SAMPARK cron jobs already exist. Removing old entries...${NC}"
    grep -v "SAMPARK" "$TEMP_CRON" > "${TEMP_CRON}.tmp" && mv "${TEMP_CRON}.tmp" "$TEMP_CRON"
fi

# Add SAMPARK cron jobs
echo "" >> "$TEMP_CRON"
echo "# SAMPARK Automated Tasks" >> "$TEMP_CRON"
echo "# ========================" >> "$TEMP_CRON"
echo "" >> "$TEMP_CRON"

# SLA monitoring every 15 minutes
echo "# SLA monitoring every 15 minutes" >> "$TEMP_CRON"
echo "*/15 * * * * $PHP_PATH $CRON_FILE sla_monitoring > /dev/null 2>&1" >> "$TEMP_CRON"
echo "" >> "$TEMP_CRON"

# Auto-escalation every hour
echo "# Auto-escalation every hour" >> "$TEMP_CRON"
echo "0 * * * * $PHP_PATH $CRON_FILE auto_escalation > /dev/null 2>&1" >> "$TEMP_CRON"
echo "" >> "$TEMP_CRON"

# Priority escalation every 2 hours
echo "# Priority escalation every 2 hours" >> "$TEMP_CRON"
echo "0 */2 * * * $PHP_PATH $CRON_FILE priority_escalation > /dev/null 2>&1" >> "$TEMP_CRON"
echo "" >> "$TEMP_CRON"

# Auto-closure every 4 hours
echo "# Auto-closure every 4 hours" >> "$TEMP_CRON"
echo "0 */4 * * * $PHP_PATH $CRON_FILE auto_closure > /dev/null 2>&1" >> "$TEMP_CRON"
echo "" >> "$TEMP_CRON"

# Daily cleanup at 2 AM
echo "# Daily cleanup at 2 AM" >> "$TEMP_CRON"
echo "0 2 * * * $PHP_PATH $CRON_FILE cleanup > /dev/null 2>&1" >> "$TEMP_CRON"
echo "" >> "$TEMP_CRON"

# Daily reports at 3 AM
echo "# Daily reports at 3 AM" >> "$TEMP_CRON"
echo "0 3 * * * $PHP_PATH $CRON_FILE reports > /dev/null 2>&1" >> "$TEMP_CRON"
echo "" >> "$TEMP_CRON"

# Digest notifications at 9 AM
echo "# Digest notifications at 9 AM" >> "$TEMP_CRON"
echo "0 9 * * * $PHP_PATH $CRON_FILE digest_notifications > /dev/null 2>&1" >> "$TEMP_CRON"
echo "" >> "$TEMP_CRON"

# Comprehensive run once daily at 1 AM (as backup)
echo "# Comprehensive run once daily at 1 AM (backup)" >> "$TEMP_CRON"
echo "0 1 * * * $PHP_PATH $CRON_FILE --verbose >> $PROJECT_DIR/logs/cron.log 2>&1" >> "$TEMP_CRON"

# Install the new crontab
echo "Installing cron jobs..."
if crontab "$TEMP_CRON"; then
    echo -e "${GREEN}✓ Cron jobs installed successfully!${NC}"
else
    echo -e "${RED}✗ Failed to install cron jobs${NC}"
    rm -f "$TEMP_CRON"
    exit 1
fi

# Clean up
rm -f "$TEMP_CRON"

# Display installed cron jobs
echo
echo "Installed cron jobs:"
echo "==================="
crontab -l | grep -A 20 "SAMPARK"

echo
echo -e "${GREEN}Setup complete!${NC}"
echo
echo "The following automated tasks are now scheduled:"
echo "• SLA monitoring: Every 15 minutes"
echo "• Auto-escalation: Every hour"
echo "• Priority escalation: Every 2 hours"
echo "• Auto-closure: Every 4 hours"
echo "• Daily cleanup: 2:00 AM"
echo "• Daily reports: 3:00 AM"
echo "• Digest notifications: 9:00 AM"
echo "• Comprehensive backup run: 1:00 AM (with logging)"
echo
echo "Logs will be written to: $PROJECT_DIR/logs/cron.log"
echo
echo "To remove these cron jobs later, run:"
echo "  crontab -e"
echo "  # Remove lines containing 'SAMPARK'"
echo
echo -e "${YELLOW}Note: Make sure the logs directory exists and is writable:${NC}"
echo "  mkdir -p $PROJECT_DIR/logs"
echo "  chmod 755 $PROJECT_DIR/logs"
