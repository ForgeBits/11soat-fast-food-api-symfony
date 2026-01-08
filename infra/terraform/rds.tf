resource "random_password" "db" {
  length  = 16
  special = false
}

locals {
  db_name     = "producao"
  db_username = "symfony"
}

resource "aws_db_instance" "postgres" {
  identifier                  = "${local.name_prefix}-pg"
  engine                      = "postgres"
  engine_version              = "16"
  instance_class              = "db.t3.micro"
  allocated_storage           = 20
  storage_type                = "gp3"
  db_name                     = local.db_name
  username                    = local.db_username
  password                    = random_password.db.result
  db_subnet_group_name        = aws_db_subnet_group.rds.name
  vpc_security_group_ids      = [aws_security_group.rds.id]
  multi_az                    = false
  publicly_accessible         = false
  skip_final_snapshot         = true
  deletion_protection         = false
  backup_retention_period     = 0

  tags = {
    Name = "${local.name_prefix}-postgres"
  }
}

output "rds_endpoint" {
  value = aws_db_instance.postgres.address
}
