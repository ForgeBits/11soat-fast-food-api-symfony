locals {
  ecr_repo_app  = "${var.project_name}-${var.env}-app"
  ecr_repo_nginx = "${var.project_name}-${var.env}-nginx"
}

resource "aws_ecr_repository" "app" {
  name                 = local.ecr_repo_app
  image_tag_mutability = "MUTABLE"
  image_scanning_configuration { scan_on_push = true }
  force_delete = true
  tags = { Name = local.ecr_repo_app }
}

resource "aws_ecr_repository" "nginx" {
  name                 = local.ecr_repo_nginx
  image_tag_mutability = "MUTABLE"
  image_scanning_configuration { scan_on_push = true }
  force_delete = true
  tags = { Name = local.ecr_repo_nginx }
}

output "ecr_app_url" {
  value = aws_ecr_repository.app.repository_url
}

output "ecr_nginx_url" {
  value = aws_ecr_repository.nginx.repository_url
}
