FROM python:3.11

RUN apt-get update && apt-get install -y \
    openssh-client \
    sshpass \
    bash \
    openssl

RUN pip install mysql-connector-python

WORKDIR /opt/lab

COPY provision_worker.py .
COPY cleanup_worker.py .
COPY create_lab_user.sh .
COPY cleanup_lab_user.sh .

RUN chmod +x create_lab_user.sh cleanup_lab_user.sh

# Default command overridden per deployment
CMD ["python", "provision_worker.py"]

