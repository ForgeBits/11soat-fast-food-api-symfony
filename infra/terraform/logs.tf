// Logs desabilitados por solicitação: não criar CloudWatch Log Groups.
// Para habilitar novamente, reintroduza os recursos abaixo e adicione
// logConfiguration nos containers em ecs_alb.tf.
//
// resource "aws_cloudwatch_log_group" "ecs_app" {
//   name              = "/ecs/${local.name_prefix}-app"
//   retention_in_days = 7
// }
//
// resource "aws_cloudwatch_log_group" "ecs_nginx" {
//   name              = "/ecs/${local.name_prefix}-nginx"
//   retention_in_days = 7
// }
