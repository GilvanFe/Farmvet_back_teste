<h1 align="center">FarmVet - Sistema de Gerenciamento Farmacêutico Veterinário</h1>

<p align="center">
  <img src="https://img.shields.io/badge/php-8.3%2B-blue" />
  <img src="https://img.shields.io/badge/framework-CakePHP-red" />
  <img src="https://img.shields.io/badge/database-PostgreSQL-blue" />
</p>

A Farmácia do Hospital Veterinário realiza a gestão dos produtos farmacêuticos e hospitalares necessários para as atividades clínicas e acadêmicas do Hospital Veterinário, Fazenda Escola e laboratórios da Faculdade de Medicina Veterinária e Zootecnia da UFMS. Essa gestão inclui as atividades de: planejamento e previsão de consumo, solicitações de compra, pareceres técnicos, recebimento, armazenamento, controle de estoque, distribuição e dispensação.

## Índice

- [Funcionalidades](#funcionalidades)
- [Requisitos](#requisitos)
- [Instalação e Implantação](#instalação-e-implantação)
- [Documentação da API (Swagger)](#documentação-da-api-swagger)
- [Testes e Cobertura](#testes-e-cobertura)
- [Estrutura do Código](#estrutura-do-código)
- [Autores](#autores)
- [Licença](#licença)

## Funcionalidades

As funcionalidades a seguir estão implementadas. As mais recentes estão na branch `release/2.0.1`.

- [x] Cadastro e listagem de itens
- [x] Paginação de itens
- [x] Cadastro e busca de fornecedores
- [x] Cadastro e busca de lotes
- [x] Cadastro e listagem de movimentações
- [x] Cadastro de setores
- [x] Listagem de movimentações por item
- [x] Busca de lote por nome de fornecedor
- [x] Busca por vencimento
- [x] Alerta de estoque baixo
- [x] Busca de movimentações por subtipo/perda



## Requisitos

- [PHP](https://www.php.net/) 8.3 ou superior
- [Composer](https://getcomposer.org/) 2.7.8 ou superior
- [PostgreSQL](https://www.postgresql.org/) 16 ou superior

## Requisitos

- [Composer](https://getcomposer.org/) (para gerenciamento de dependências PHP, versão 2.7.8 ou superior)
- [PHP](https://www.php.net/) (versão 8.3 ou superior)
- [PostgreSQL](https://www.postgresql.org/) (versão 16 ou superior)
- Extensões do PHP:
    - pdo
    - mbstring
    - intl
    - openssl
    - dom
    - gd
    - zip
    - fileinfo

## Instalação / Implantação

### Clone o repositório

```bash
git clone git@github.com:FarmVet-UFMS/FarmVet-Back.git
```

ou via https

```bash
git clone https://github.com/FarmVet-UFMS/FarmVet-Back.git
```

```bash
cd FarmVet-Back
```

### Instale as dependências PHP
Instale as dependências:
```bash
composer install
```

Atualize as dependências:
```sh
composer update
```

### Instale as dependências utilizando o Composer.
#### Windows:
Edite o arquivo php.ini e descomente as seguintes linhas:
```ini
extension=php_intl.dll
extension=php_xml.dll
extension=php_curl.dll
extension=zip
extension=pgsql
extension=pdo_pgsql
extension=mbstring
extension=openssl
```

#### Linux:
Descomentar:
```ini
extension=xml
```

executar os comandos:
```bash
sudo apt-get install php8.3-intl php8.3-xml php8.3-curl, sudo apt-get install php-pgsql, sudo apt-get install php-pdo_pgsql
```

### Crie o banco de dados

Acesse o PostgreSQL e execute:
```sh
CREATE DATABASE farm_vet;
```
Para testes:
```sh
CREATE DATABASE farm_vet_test;
```

### Rode as migrations
```sh
bin/cake migrations migrate
bin/cake migrations migrate --connection test
```

### Rode a seed do setor
```sh
bin/cake migrations seed --seed SetorSeed
```

### Geração de Chave de Segurança
Para gerar uma chave aleatória:
```sh
bin/cake.php security:generateRandomKey
```
Esse passo é opcional em desenvolvimento, pois já existe uma chave padrão no app_local.example.php.


### Rode as migrations
```bash
bin/cake migrations migrate
```
Para testes:
```bash
bin/cake migrations migrate --connection test
```

### Rode a seed:
```bash
bin/cake migrations seed --seed SetorSeed
```

### Configuração de Variáveis de Ambiente

Copie o arquivo de configuração:
```sh
cp config/app_local.example.php config/app_local.php
```

#### 🌐 Variáveis de Ambiente
Configure as variáveis no seu ambiente local ou servidor de produção:

| Variável                | Valor padrão (desenvolvimento) | Valor para produção         |
|-------------------------|--------------------------------|------------------------------|
| `DATABASE_USERNAME`     | postgres                       | postgres                     |
| `DATABASE_PASSWORD`     | password                       | (sua senha)                  |
| `DATABASE_NAME`         | farm_vet                       | farm_vet                     |
| `DATABASE_HOST`         | localhost                      | (host do banco de produção)  |
| `DATABASE_PORT`         | 5432                           | 5432                         |
| `DATABASE_SCHEMA`       | public                         | public                       |
| `SECURITY_SALT`         | (chave gerada ou padrão)       | (chave gerada)               |
| `DEBUG`                 | true                           | false                        |
| `DATABASE_TEST_USERNAME`| postgres                       | postgres                     |
| `DATABASE_TEST_PASSWORD`| password                       | (sua senha)                  |
| `DATABASE_TEST_NAME`    | farm_vet_test                  | farm_vet_test                |
| `DATABASE_TEST_HOST`    | localhost                      | (host do banco de testes)    |
| `DATABASE_TEST_PORT`    | 5432                           | 5432                         |
| `DATABASE_TEST_SCHEMA`  | public                         | public                       |

### Inicie o servidor local
```sh
php -S localhost:8000 -t webroot
```

O backend estará acessível em http://localhost:8000

### Executando os testes
```sh
vendor/bin/phpunit
```

### Implantação em Produção
- Configure o ambiente com DEBUG=false.
- Aponte o domínio para a pasta webroot/.
- Execute as migrations e seeds conforme necessário.
- Ajuste permissões nos diretórios obrigatórios:

```sh
chmod -R 775 logs tmp
```
Isso garante que o CakePHP possa gravar arquivos temporários e de log corretamente.

### Problemas conhecidos
Ao rodar o projeto no ambiente linux pode ser necessário rodar o comando abaixo:
```sh
sudo apt install php8.3-gd
```

## Documentação da API (Swagger)
O projeto utiliza Swagger para documentar as rotas da API.

```bash
bin/cake swagger install
bin/cake swagger bake
```

Acesse a documentação em: http://localhost:8000/docs

## Testes e Cobertura
### Testes

```bash
vendor/bin/phpunit tests/TestCase/
```

### Visual com TestDox
```bash
vendor/bin/phpunit --testdox --display-deprecations tests/TestCase/
```

### Gerar cobertura em HTML
```bash
vendor/bin/phpunit --configuration phpunit.xml.dist --coverage-html coverage/html
```

### Abrir resultado no Windows
```bash
Start-Process -FilePath ".\coverage\html\index.html"
```

## Estrutura do código:
```explain
.
├── bin/                     # Scripts executáveis (CakePHP CLI)
├── config/                  # Configurações gerais do projeto
│   ├── Migrations/          # Migrations do banco
│   └── Seeds/               # Seeds iniciais
├── src/                     # Código-fonte principal
│   ├── Controller/          # Controladores
│   ├── Model/               # Entidades, tabelas, behaviors
│   ├── Service/             # Serviços de negócio
│   └── View/                # Camada de apresentação
├── templates/               # Templates HTML renderizados
├── tests/                   # Testes automatizados
│   ├── Fixture/             # Dados de teste
│   └── TestCase/            # Testes unitários
└── webroot/                 # Diretório público acessível via navegador
```

## Autores

Esse sistema foi desenvolvido pela seguinte equipe:

Proposto por Elza Domingues e Mayara Rodrigues.
Orientado durante a primeira iteração pela professora [Maria Istela Cagnin Machado](https://github.com/istela) e desenvolvido por:
- [Alice Strassburger Araújo Filippi](https://github.com/alice-strass) (alice.s@ufms.br)
- [Enzo Haruo França Okita](https://github.com/enzookita) (enzo.okita@ufms.br)
- [Felipe Corrêa Rocha](https://github.com/FelipeRochaTI) (felipe_rocha@ufms.br)
- [Jorge Luis Freitas Costa](https://github.com/myrdiaclonix) (freitas.costa@ufms.br)
- [Kelvisck Aureliano Cabral](https://github.com/Kelvisck) (kelvisck.cabral@ufms.br)

Orientado durante a segunda iteração pelo professor [Hudson Silva Borges](https://github.com/hsborges) e desenvolvido por:
- [Pedro Henrique Weber Carvalhaes](https://github.com/PedroWC) (henrique.carvalhaes@ufms.br)
- [Wagner Rodrigues da Silva](https://github.com/WagnerRdeV) (wagner_r@ufms.br)
- [Lucas Ulbrecht Patrizi](https://github.com/LucasUlbrecht) (l_ulbrecht@ufms.br)
- [Felipe Franco Pellissari](https://github.com/FeliPellissari) (felipe.pellissari@ufms.br)
- [Lucas César Ken Hakoma](https://github.com/Lucashokama) (lucas.hokama@ufms.br)
- [Vinicius Feitosa Gonçalves](https://github.com/viniEng) (goncalves.feitosa@ufms.br)
