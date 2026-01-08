variable "aws_region" {
  description = "AWS region"
  type        = string
  default     = "us-east-1"
}

variable "project_name" {
  description = "Prefix/name for resources"
  type        = string
  default     = "fastfood-symfony"
}

variable "env" {
  description = "Environment name"
  type        = string
  default     = "dev"
}
