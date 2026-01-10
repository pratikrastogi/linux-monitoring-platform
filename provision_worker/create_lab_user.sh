#!/bin/bash
set -e

USERNAME="$1"
DURATION_MINUTES="${2:-60}"

if [ -z "$USERNAME" ]; then
  echo "ERROR|Username missing"
  exit 1
fi

PASSWORD=$(openssl rand -base64 10)
NAMESPACE="lab-${USERNAME}"
ROLE="student-role"
HOME_DIR="/labs/${USERNAME}"
KUBECONFIG_PATH="${HOME_DIR}/.kube"
EXPIRY_DATE=$(date -d "+${DURATION_MINUTES} minutes" +"%Y-%m-%d %H:%M:%S")

echo "▶ Creating Linux user (expiry enforced)..."
id "$USERNAME" &>/dev/null || useradd -m -d "$HOME_DIR" "$USERNAME"
echo "${USERNAME}:${PASSWORD}" | chpasswd
chage -E "$(date -d "+${DURATION_MINUTES} minutes" +%F)" "$USERNAME"

echo "▶ Kubernetes namespace..."
kubectl create namespace "$NAMESPACE" --dry-run=client -o yaml | kubectl apply -f -

echo "▶ ServiceAccount..."
kubectl create serviceaccount "$USERNAME" -n "$NAMESPACE" \
  --dry-run=client -o yaml | kubectl apply -f -

echo "▶ Role..."
kubectl apply -f - <<EOF
apiVersion: rbac.authorization.k8s.io/v1
kind: Role
metadata:
  name: $ROLE
  namespace: $NAMESPACE
rules:
- apiGroups: ["", "apps"]
  resources: ["pods","services","deployments","replicasets"]
  verbs: ["get","list","create","delete","watch"]
EOF

echo "▶ RoleBinding..."
kubectl create rolebinding ${USERNAME}-rb \
  --role=$ROLE \
  --serviceaccount=${NAMESPACE}:${USERNAME} \
  -n $NAMESPACE \
  --dry-run=client -o yaml | kubectl apply -f -

echo "▶ Token (time-bound)..."
TOKEN=$(kubectl create token "$USERNAME" -n "$NAMESPACE" --duration=${DURATION_MINUTES}m)

API_SERVER=$(kubectl config view --minify -o jsonpath='{.clusters[0].cluster.server}')
CA_DATA=$(kubectl config view --raw --minify -o jsonpath='{.clusters[0].cluster.certificate-authority-data}')

mkdir -p "$KUBECONFIG_PATH"

cat <<EOF > "$KUBECONFIG_PATH/config"
apiVersion: v1
kind: Config
clusters:
- name: lab-cluster
  cluster:
    server: $API_SERVER
    certificate-authority-data: $CA_DATA
users:
- name: $USERNAME
  user:
    token: $TOKEN
contexts:
- name: ${USERNAME}-context
  context:
    cluster: lab-cluster
    user: $USERNAME
    namespace: $NAMESPACE
current-context: ${USERNAME}-context
EOF

chown -R ${USERNAME}:${USERNAME} "$HOME_DIR"
chmod 700 "$HOME_DIR"
chmod 600 "$KUBECONFIG_PATH/config"

echo "SUCCESS|USER=$USERNAME|PASS=$PASSWORD|EXPIRES=$EXPIRY_DATE|NAMESPACE=$NAMESPACE"

