# CRUD em Drupal 11

Módulo (CRUD) para drupal 11

## Como rodar o teste?

- Instale o docker e docker-compose: https://www.docker.com/
- Acesse a raiz do diretório e rode o seguinte comando: ```docker-compose up -d```;
- Acesse a url: http://localhost:8080/ e siga o passo a passo para a instalação do Drupal (Os dados solicitados para a configuração do banco de dados estão em docker-compose.yml em environment do container drupal);
- Após concluir a configuração inicial do Drupal é necessário instalar o módulo cases 'desenvolvido para o teste', acesse o bash do container principal rodando: ```docker exec -ti drupal_drupal_1 bash```;
- Execute ```drush en cases``` para confirmar a instalação e limpe a cache executando ```drush cr```;
- Para executar o crud acesse http://localhost:8080/cases;
- Para executar o endpoint de visualização de um case acesse: http://localhost:8080/api/cases/1 passe no final do endereço o identrificador do case como no exemplo.