variable "image_tag" {
  description = "Docker image tag to deploy"
  type        = string
  default     = "latest"
}

locals {
  container_app_name   = "php-fpm"
  container_nginx_name = "nginx"
}

resource "aws_ecs_cluster" "this" {
  name = "${local.name_prefix}-cluster"
}

## Sem Load Balancer: o acesso será direto ao IP público do task (porta 80 no Nginx)

resource "aws_ecs_task_definition" "app" {
  family                   = "${local.name_prefix}-task"
  network_mode             = "awsvpc"
  requires_compatibilities = ["FARGATE"]
  cpu                      = "512"
  memory                   = "1024"
  execution_role_arn       = aws_iam_role.ecs_task_execution.arn
  task_role_arn            = aws_iam_role.ecs_task.arn

  container_definitions = jsonencode([
    {
      name      = local.container_app_name,
      image     = "${aws_ecr_repository.app.repository_url}:${var.image_tag}",
      essential = true,
      command   = ["php-fpm"],
      workingDirectory = "/var/www/html",
      portMappings = [
        {
          containerPort = 9000,
          protocol      = "tcp"
        }
      ],
      environment = [
        { name = "APP_ENV", value = "prod" }
      ],
      secrets = [
        { name = "APP_SECRET", valueFrom = aws_ssm_parameter.app_secret.arn },
        { name = "DATABASE_URL", valueFrom = aws_ssm_parameter.database_url.arn }
      ],
      mountPoints = []
    },
    {
      name      = local.container_nginx_name,
      image     = "${aws_ecr_repository.nginx.repository_url}:${var.image_tag}",
      essential = true,
      portMappings = [
        {
          containerPort = 80,
          protocol      = "tcp"
        }
      ],
      dependsOn = [
        { containerName = local.container_app_name, condition = "START" }
      ],
      environment = []
    }
  ])
}

resource "aws_ecs_service" "app" {
  name            = "${local.name_prefix}-svc"
  cluster         = aws_ecs_cluster.this.id
  task_definition = aws_ecs_task_definition.app.arn
  desired_count   = 1
  launch_type     = "FARGATE"

  network_configuration {
    subnets         = [for s in aws_subnet.public : s.id]
    assign_public_ip = true
    security_groups = [aws_security_group.ecs_tasks.id]
  }
}
