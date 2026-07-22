variable "region" {
  description = "AWSリージョン"
  type        = string
  default     = "ap-northeast-1"
}

variable "project_name" {
  description = "リソース名のプレフィックス"
  type        = string
  default     = "shop"
}

variable "instance_type" {
  description = "EC2インスタンスタイプ（app+web+db+redisを同居させるためt3.micro以上を推奨）"
  type        = string
  default     = "t3.small"
}

variable "root_volume_size" {
  description = "EBSルートボリュームサイズ(GB)"
  type        = number
  default     = 20
}

variable "my_ip_cidr" {
  description = "SSH(22番)接続を許可する自分のグローバルIP（例: 203.0.113.10/32）"
  type        = string
}

variable "ssh_public_key_path" {
  description = "EC2にインポートするSSH公開鍵ファイルのパス（例: ~/.ssh/id_ed25519.pub）"
  type        = string
}
