output "public_ip" {
  description = "EC2のElastic IP（ブラウザ確認・SSH接続用）"
  value       = aws_eip.this.public_ip
}

output "ssh_command" {
  description = "SSH接続コマンド"
  value       = "ssh -i <秘密鍵のパス> ec2-user@${aws_eip.this.public_ip}"
}

output "app_url" {
  description = "アプリのURL"
  value       = "http://${aws_eip.this.public_ip}"
}
