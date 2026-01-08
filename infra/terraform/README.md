# Terraform: ECS Fargate + RDS (sem NAT, sem ALB)

Este diretório provisiona a infra mínima na AWS (us-east-1) para rodar sua API Symfony no ECS Fargate e RDS PostgreSQL em sub-redes privadas. Não há NAT Gateway (custos menores) e não há Load Balancer (ALB). Sem autoscaling, mensageria ou domínio customizado.

Componentes:
- VPC 10.10.0.0/16, 2 subnets públicas (ECS/ALB) e 2 privadas (RDS)
- ECS Fargate (1 task) em subnets públicas com IP público
- Task com 2 containers: nginx (porta 80) e php-fpm (porta 9000)
- RDS PostgreSQL 16 db.t3.micro (free tier), 20GB gp3, single-AZ, privado
- SSM Parameter Store para APP_ENV, APP_SECRET e DATABASE_URL
- ECR: 2 repositórios (app e nginx)
  
Observação: CloudWatch Logs desabilitado a pedido (containers não enviarão logs para a AWS). Sem ALB: o acesso é feito diretamente ao IP público da task (porta 80 do Nginx).

Requisitos locais:
- Terraform >= 1.5, AWS CLI, credenciais configuradas (perfil ou env)

1) Inicializar e criar infraestrutura
```
cd infra/terraform
terraform init
terraform plan -out tfplan
terraform apply tfplan
```

Saídas importantes:
- ecr_app_url e ecr_nginx_url: URLs dos repositórios no ECR
- rds_endpoint: endpoint privado do Postgres (usado no DATABASE_URL gerado)

2) Build e push das imagens no ECR

Você precisa publicar DUAS imagens com o MESMO tag (ex: latest):
- app (php-fpm) — deve conter o código da aplicação em /var/www/html e rodar php-fpm na porta 9000
- nginx — deve conter o mesmo código em /var/www/html e a config do Nginx apontando para o php-fpm dentro do task

Exemplo de login e push (substitua <account_id> e região se necessário):
```
aws ecr get-login-password --region us-east-1 | docker login --username AWS --password-stdin <account_id>.dkr.ecr.us-east-1.amazonaws.com

# APP (php-fpm)
APP_REPO=$(terraform output -raw ecr_app_url)
docker build -t ${APP_REPO}:latest -f docker/php/Dockerfile .
docker push ${APP_REPO}:latest

# NGINX
NGINX_REPO=$(terraform output -raw ecr_nginx_url)
docker build -t ${NGINX_REPO}:latest -f docker/nginx/Dockerfile .
docker push ${NGINX_REPO}:latest
```

Observações importantes sobre as imagens:
- Produção: ajuste seus Dockerfiles para copiar o código da pasta `app/` para `/var/www/html` em AMBAS as imagens (nginx e php-fpm), e para instalar dependências do Composer em modo `--no-dev` com autoloader otimizado. O Dockerfile atual de desenvolvimento (docker/php/Dockerfile) não copia o código nem roda `composer install`. Você pode criar variantes `Dockerfile.ecs` para produção.
- Nginx no ECS: a configuração deve usar `fastcgi_pass php-fpm:9000` (o nome do container php-fpm no task). Veja o arquivo `docker/nginx/default.conf` como base e ajuste o upstream se necessário.

3) Atualizar o serviço com a nova imagem (se já existir)
Se você mudar o tag (var.image_tag), atualize via Terraform:
```
terraform apply -var image_tag=latest
```

4) Acessar a aplicação (sem ALB)
Como não há Load Balancer, você acessará diretamente o IP público do ENI da task do ECS. Esse IP pode mudar quando a task é recriada.

Formas de descobrir o IP público:
- Console AWS → ECS → Clusters → Serviços → Tarefas (Tasks) → selecione a task → na seção de rede verifique o ENI e o Public IPv4.
- CLI: `aws ecs list-tasks --cluster <cluster> --service-name <svc>` → pegue o task arn; `aws ecs describe-tasks --cluster <cluster> --tasks <arn>` → pegue o `eni` em attachments; então descreva o ENI no EC2 para achar o `PublicIp`.

Acesse via HTTP:
```
http://<task_public_ip>
```

5) Variáveis de ambiente e segredos
- `APP_ENV` = `prod` (SSM)
- `APP_SECRET` = valor placeholder `change-me` (altere no SSM depois)
- `DATABASE_URL` é gerado automaticamente apontando para o RDS

6) Custos e limites
- Sem NAT Gateway. O RDS fica privado; o ECS tem IP público para baixar imagens do ECR e se comunicar com a internet quando necessário.
- Tamanhos: ECS 0.5 vCPU / 1GB RAM; RDS db.t3.micro.

7) Logs e observabilidade
- Logs do container no CloudWatch foram desabilitados. Para habilitar no futuro, adicione `logConfiguration` nos containers da Task Definition (ecs_alb.tf) e reintroduza os recursos de `aws_cloudwatch_log_group` em `logs.tf`.

8) Observações importantes (sem ALB)
- O IP público da task pode mudar após atualizações/rollouts. Se precisar de estabilidade, considere usar um ALB (quando sua conta permitir) ou um Elastic IP/NLB com Service Connect/Service Discovery (mais complexo).
- Sem ALB, não há health check e balanceamento; a disponibilidade é a da própria task.

Destruir ambiente:
```
terraform destroy
```
