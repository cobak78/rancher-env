FROM letsbonus/php:5.6

WORKDIR /ha-manager

RUN apt-get update && apt-get install -y --no-install-recommends \
    ssh-client \
    rsync \
 && rm -rf /var/lib/apt/lists/*
