FROM python:3.11-slim

# -----------------------------
# System dependencies
# -----------------------------
RUN apt-get update && apt-get install -y --no-install-recommends \
    openssh-client \
    sshpass \
    bash \
    openssl \
    ca-certificates \
 && rm -rf /var/lib/apt/lists/*

# -----------------------------
# Python dependencies
# -----------------------------
RUN pip install --no-cache-dir mysql-connector-python paramiko

# -----------------------------
# App layout
# -----------------------------
WORKDIR /opt/lab

# -----------------------------
# Copy workers + scripts
# -----------------------------
COPY provision_worker.py .
COPY cleanup_worker.py .
COPY create_lab_user.sh .
COPY cleanup_lab_user.sh .

# -----------------------------
# Permissions
# -----------------------------
RUN chmod +x create_lab_user.sh cleanup_lab_user.sh

# -----------------------------
# Default command
# (overridden per Deployment)
# -----------------------------
CMD ["python", "provision_worker.py"]

