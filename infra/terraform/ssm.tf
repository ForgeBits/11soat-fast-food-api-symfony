locals {
  ssm_path = "/${local.name_prefix}/app"
}

# Compose DATABASE_URL from RDS info
locals {
  database_url = "postgresql://${local.db_username}:${random_password.db.result}@${aws_db_instance.postgres.address}:5432/${local.db_name}?serverVersion=16&charset=utf8"
}

resource "aws_ssm_parameter" "app_env" {
  name  = "${local.ssm_path}/APP_ENV"
  type  = "String"
  value = "prod"
}

resource "aws_ssm_parameter" "app_secret" {
  name  = "${local.ssm_path}/APP_SECRET"
  type  = "String"
  value = "change-me"
}

resource "aws_ssm_parameter" "database_url" {
  name  = "${local.ssm_path}/DATABASE_URL"
  type  = "SecureString"
  value = local.database_url
}
