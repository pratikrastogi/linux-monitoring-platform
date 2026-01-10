FROM python:3.11

RUN apt-get update && apt-get install -y \
    openssl \
    bash \
    kubectl \
    passwd \
    util-linux

RUN pip install mysql-connector-python

COPY provision_worker.py /worker.py
COPY create_lab_user.sh /scripts/create_lab_user.sh
RUN chmod +x /scripts/create_lab_user.sh

CMD ["python","/worker.py"]

