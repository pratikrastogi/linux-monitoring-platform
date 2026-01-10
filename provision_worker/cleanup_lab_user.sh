#!/bin/bash
set -e

USERNAME="$1"
NAMESPACE="lab-${USERNAME}"

if [ -z "$USERNAME" ]; then
  echo "ERROR|Username missing"
  exit 1
fi

echo "▶ Locking Linux user..."
usermod -L "$USERNAME" || true
passwd -l "$USERNAME" || true

echo "▶ Killing active SSH sessions..."
pkill -u "$USERNAME" || true

echo "▶ Removing Kubernetes namespace..."
kubectl delete namespace "$NAMESPACE" --ignore-not-found=true

echo "SUCCESS|CLEANED=$USERNAME"

