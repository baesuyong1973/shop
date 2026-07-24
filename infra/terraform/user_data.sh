#!/bin/bash
set -eux

dnf update -y
dnf install -y docker git cronie

systemctl enable --now docker
systemctl enable --now crond
usermod -aG docker ec2-user

mkdir -p /usr/local/lib/docker/cli-plugins
curl -SL "https://github.com/docker/compose/releases/latest/download/docker-compose-linux-x86_64" \
  -o /usr/local/lib/docker/cli-plugins/docker-compose
chmod +x /usr/local/lib/docker/cli-plugins/docker-compose

# dnfのdocker付属buildxは古く、compose v2の一部機能（build時のbake）が
# buildx 0.17.0以上を要求するため最新版に差し替える
curl -fsSL -o /usr/libexec/docker/cli-plugins/docker-buildx \
  "https://github.com/docker/buildx/releases/latest/download/buildx-$(curl -fsSL https://api.github.com/repos/docker/buildx/releases/latest | grep -m1 '"tag_name"' | cut -d'"' -f4).linux-amd64"
chmod +x /usr/libexec/docker/cli-plugins/docker-buildx
