FROM python:3.11
RUN pip install paramiko mysql-connector-python
COPY collector.py /collector.py
CMD ["python","/collector.py"]

