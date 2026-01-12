#!/bin/bash

# KubeArena AdminLTE Migration Script
# This script helps activate the new AdminLTE UI

echo "========================================"
echo "   KubeArena AdminLTE Migration Tool   "
echo "========================================"
echo ""

# Function to backup files
backup_file() {
    if [ -f "$1" ]; then
        echo "✓ Backing up $1 to ${1}.backup"
        cp "$1" "${1}.backup"
    fi
}

# Function to activate new file
activate_new() {
    if [ -f "$1" ]; then
        backup_file "$2"
        echo "✓ Activating $1 as $2"
        cp "$1" "$2"
    else
        echo "✗ File not found: $1"
    fi
}

echo "Step 1: Backing up existing files..."
echo "-----------------------------------"
cd src/

backup_file "login.php"
backup_file "register.php"
backup_file "index.php"
backup_file "charts.php"
backup_file "alerts.php"
backup_file "users.php"

echo ""
echo "Step 2: Activating new AdminLTE pages..."
echo "----------------------------------------"

if [ "$1" == "--full" ]; then
    echo "Full migration mode selected"
    activate_new "login_new.php" "login.php"
    activate_new "register_new.php" "register.php"
    activate_new "index_new.php" "index.php"
    activate_new "charts_new.php" "charts.php"
    activate_new "alerts_new.php" "alerts.php"
    activate_new "users_new.php" "users.php"
    echo ""
    echo "✓ Full migration completed!"
elif [ "$1" == "--test" ]; then
    echo "Test mode - creating symlinks only"
    ln -sf login_new.php login_test.php
    ln -sf register_new.php register_test.php
    ln -sf index_new.php index_test.php
    echo ""
    echo "✓ Test symlinks created (access via *_test.php)"
else
    echo "Usage:"
    echo "  ./migrate.sh --test    # Create test links"
    echo "  ./migrate.sh --full    # Full migration (replaces old files)"
    echo ""
    echo "Available new pages:"
    echo "  - login_new.php"
    echo "  - register_new.php"
    echo "  - index_new.php"
    echo "  - charts_new.php"
    echo "  - alerts_new.php"
    echo "  - users_new.php"
    echo ""
    echo "You can test them directly by accessing the *_new.php files"
fi

echo ""
echo "========================================"
echo "             Migration Complete         "
echo "========================================"
echo ""
echo "Next steps:"
echo "1. Test new pages at http://yourserver/src/*_new.php"
echo "2. Run './migrate.sh --full' to activate fully"
echo "3. Check MIGRATION_GUIDE.md for details"
echo ""
