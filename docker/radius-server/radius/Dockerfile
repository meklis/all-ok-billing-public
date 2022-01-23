FROM ubuntu:18.04
LABEL maintainer="Max Boyar <max.boyar.a@gmail.com>"
RUN apt update && apt -y upgrade
ADD https://github.com/meklis/all-ok-radius-server/releases/download/0.2.9/radius-server-linux-amd64 /opt/all-ok-radius-server
COPY radius.conf.yml /opt/radius.conf.yml
RUN chmod +x /opt/all-ok-radius-server
ENTRYPOINT ["/opt/all-ok-radius-server", "-c", "/opt/radius.conf.yml"]

