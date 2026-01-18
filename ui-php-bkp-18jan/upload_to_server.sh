#!/bin/bash
#####################################################
# PHASE 2 - UPLOAD FILES TO SERVER
# Run this from your local machine (Mac)
#####################################################

SERVER="root@192.168.1.46"
REMOTE_DIR="/root/phase2_deployment"

echo "================================================"
echo "  Uploading Phase 2 Files to Server"
echo "================================================"
echo ""

# Create remote directory
echo "Creating remote directory..."
ssh $SERVER "mkdir -p $REMOTE_DIR/src/widgets"

# Upload deployment script
echo "Uploading deployment script..."
scp deploy_phase2.sh $SERVER:$REMOTE_DIR/

# Upload widget file
echo "Uploading lab widgets..."
scp src/widgets/lab_widgets.php $SERVER:$REMOTE_DIR/src/widgets/

# Upload my_labs page
echo "Uploading my_labs.php..."
scp src/my_labs.php $SERVER:$REMOTE_DIR/src/

# Upload updated index.php
echo "Uploading updated index.php..."
scp src/index.php $SERVER:$REMOTE_DIR/src/

# Upload updated sidebar.php
echo "Uploading updated sidebar.php..."
scp src/includes/sidebar.php $SERVER:$REMOTE_DIR/src/includes/

echo ""
echo "✅ All files uploaded successfully!"
echo ""
echo "Next steps on the server:"
echo "1. ssh $SERVER"
echo "2. cd $REMOTE_DIR"
echo "3. chmod +x deploy_phase2.sh"
echo "4. ./deploy_phase2.sh"
echo ""
