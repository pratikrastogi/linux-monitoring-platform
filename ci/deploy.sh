#!/bin/bash
set -e

export BUILDAH_ISOLATION=chroot


PROJECT_ROOT="/opt/linux-monitoring-platform"
NAMESPACE="linux-monitoring"

UI_IMAGE="pratikrastogi/linux-monitoring"
COLLECTOR_IMAGE="pratikrastogi/linux-monitor-collector"

UI_TAG=$(date +%Y%m%d%H%M)
COLLECTOR_TAG=$(date +%Y%m%d%H%M)

echo "🚀 Starting CI/CD Pipeline..."

cd $PROJECT_ROOT

# ==============================
# Detect changes
# ==============================
CHANGED_FILES=$(git diff --name-only HEAD~1)

BUILD_UI=false
BUILD_COLLECTOR=false

if echo "$CHANGED_FILES" | grep -q "^ui-php/"; then
  BUILD_UI=true
fi

if echo "$CHANGED_FILES" | grep -q "^collector/"; then
  BUILD_COLLECTOR=true
fi

# ==============================
# Build & Deploy UI
# ==============================
if [ "$BUILD_UI" = true ]; then
  echo "🔧 Building PHP UI image..."
  docker build -t $UI_IMAGE:$UI_TAG ui-php/
  docker push $UI_IMAGE:$UI_TAG

  echo "🚢 Deploying PHP UI..."
  kubectl set image deployment/php-ui php-ui=$UI_IMAGE:$UI_TAG -n $NAMESPACE
fi

# ==============================
# Build & Deploy Collector
# ==============================
if [ "$BUILD_COLLECTOR" = true ]; then
  echo "🔧 Building Collector image..."
  docker build -t $COLLECTOR_IMAGE:$COLLECTOR_TAG collector/
  ocker push $COLLECTOR_IMAGE:$COLLECTOR_TAG

  echo "🚢 Deploying Collector..."
  kubectl set image deployment/collector collector=$COLLECTOR_IMAGE:$COLLECTOR_TAG -n $NAMESPACE
fi

echo "✅ CI/CD Pipeline completed successfully!"

