FROM ubuntu:14.04

ENV DEBIAN_FRONTEND noninteractive

RUN apt-get update \
    && apt-get -y install wget curl zip openjdk-7-jre-headless \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

RUN curl -s https://api.github.com/repos/lightbody/browsermob-proxy/releases | grep browser_download_url | head -n 1 | cut -d '"' -f 4 | wget --base=http://github.com/ -i - -O /"browsermob-proxy.zip"
RUN unzip -q /browsermob-proxy.zip \
    && rm -f /browsermob-proxy.zip
RUN mv /browsermob* /browsermob-proxy

COPY start.sh /
RUN chmod +x /start.sh

EXPOSE 9090-9091
CMD ["/start.sh"]
