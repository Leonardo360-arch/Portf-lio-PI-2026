# PI - 2º Semestre

## Danny Duarte Photograph

## Sobre o projeto

Este projeto integrador do 2º semestre tem como objetivo o desenvolvimento de um sistema web para gerenciamento de agendamentos fotográficos da cliente **Dany Cardozo**.

A proposta do sistema é oferecer uma plataforma moderna, organizada e funcional, permitindo que clientes conheçam os serviços, solicitem agendamentos e acompanhem suas solicitações. Além disso, o sistema conta com uma área administrativa para controle de serviços, cenários, fotógrafos, agendamentos, histórico e comunicação com os clientes.

## Objetivo do projeto

Desenvolver um sistema web responsivo, intuitivo e funcional para auxiliar na organização dos atendimentos fotográficos, facilitando tanto a experiência do cliente quanto o gerenciamento administrativo.

O sistema tem como objetivos principais:

* Apresentar a fotógrafa e seus serviços;
* Facilitar o contato com clientes;
* Permitir o cadastro e login de clientes;
* Permitir solicitações de agendamento online;
* Controlar serviços, cenários e fotógrafos;
* Evitar conflitos de horários;
* Permitir aprovação, recusa e cancelamento de agendamentos;
* Disponibilizar histórico com filtros e valores;
* Melhorar a organização geral dos atendimentos.

## Tecnologias utilizadas

O projeto foi desenvolvido utilizando as seguintes tecnologias:

* **HTML** - estrutura das páginas;
* **CSS** - estilização e identidade visual;
* **JavaScript** - interatividade no front-end;
* **Bootstrap** - apoio na responsividade e componentes visuais;
* **PHP** - lógica de back-end;
* **MySQL** - armazenamento e gerenciamento dos dados;
* **Docker** - ambiente de desenvolvimento;
* **PHPMailer / Mailpit** - fluxo de e-mails em ambiente de teste;
* **WhatsApp** - comunicação direta com clientes.

## Estrutura do projeto

O sistema foi organizado em módulos para facilitar o desenvolvimento e a manutenção:

* **Site público:** apresentação da fotógrafa, serviços, contato e WhatsApp;
* **Área do cliente:** cadastro, login, perfil, solicitação e acompanhamento de agendamentos;
* **Área administrativa:** gerenciamento de solicitações, serviços, cenários, fotógrafos e histórico;
* **Banco de dados:** armazenamento das informações do sistema;
* **Actions:** arquivos responsáveis pelo processamento das ações do sistema;
* **Documentação:** requisitos, modelagem, testes e descrição do projeto.

## Funcionalidades principais

Entre as principais funcionalidades implementadas, estão:

* Cadastro de clientes;
* Login de clientes e administradores;
* Recuperação de senha;
* Edição de perfil do cliente;
* Solicitação de agendamento;
* Escolha de serviço, cenário, fotógrafo, data e horário;
* Acompanhamento do status pelo cliente;
* Cancelamento de solicitação pendente pelo cliente;
* Aprovação, recusa, edição, conclusão e cancelamento de agendamentos pelo administrador;
* Cadastro e edição de serviços;
* Cadastro e edição de cenários;
* Cadastro e edição de fotógrafos;
* Histórico administrativo com busca, filtros, valores e total;
* Validação de conflitos de horário;
* Controle de duração dos serviços;
* Validação de telefone e e-mail;
* Botões de contato via WhatsApp;
* Notificações administrativas.

## Banco de dados

O banco de dados foi modelado para armazenar as principais informações do sistema, garantindo organização e integridade dos dados.

Principais tabelas:

* **admins / clientes**
* **agendamentos**
* **tipos_servico**
* **cenarios**
* **fotografos**
* **nichos**

O sistema utiliza relacionamentos entre as tabelas para controlar clientes, serviços, fotógrafos, cenários e agendamentos.

## Público-alvo

O sistema é voltado para:

* Clientes interessados em contratar serviços fotográficos;
* Pessoas que desejam conhecer o portfólio e serviços da fotógrafa;
* Administradores responsáveis por organizar solicitações e atendimentos;
* Profissionais que precisam controlar horários, cenários e serviços oferecidos.

## Equipe do projeto

A equipe responsável pelo desenvolvimento do projeto é formada por:

| Integrante                      | Função                                           | GitHub                                                  |
| ------------------------------- | ------------------------------------------------ | ------------------------------------------------------- |
| Michael Pierre Nintz de Freitas | Back-end / PO                                    | [MichaelDeFreitas](https://github.com/MichaelDeFreitas) |
| Leonardo Marson Coral           | Banco de dados                                   | [Adicionar GitHub](#)                                   |
| Denis Diego Brandão da Cunha    | Back-end / Front-end                             | [Adicionar GitHub](#)                                   |
| Eduardo Ferreira Lopes          | Documentação / Casos de uso                      | [Adicionar GitHub](#)                                   |
| Gabriel Neves                   | Documentação / Casos de uso                      | [Adicionar GitHub](#)                                   |

## Organização das funções

Cada integrante contribuiu em uma área específica do projeto, permitindo melhor divisão das tarefas e organização do desenvolvimento.

As funções foram divididas entre levantamento de requisitos, modelagem do banco de dados, desenvolvimento do front-end, desenvolvimento do back-end, testes, documentação e organização geral do sistema.

## Resultado esperado

Ao final do projeto, espera-se entregar um sistema funcional, organizado e de fácil utilização, capaz de auxiliar a cliente no gerenciamento dos seus atendimentos fotográficos.

O sistema deve facilitar o contato com os clientes, reduzir conflitos de horários, centralizar informações importantes e melhorar a experiência tanto do cliente quanto da administradora.

## Como executar o projeto

Para executar o projeto localmente, é necessário possuir um ambiente com PHP e MySQL configurados.

Passos básicos:

1. Clonar o repositório;
2. Instalar as dependências com Composer;
3. Criar o arquivo `.env` com base no `.env.example`;
4. Configurar o banco de dados;
5. Importar o arquivo `database.sql`;
6. Iniciar o servidor local ou ambiente Docker;
7. Acessar o sistema pelo navegador.

Exemplo:

```bash
composer install
```

Depois, configure o `.env` com os dados do banco de dados.

## Observações

Este repositório será utilizado para armazenar os arquivos, códigos e documentação relacionados ao desenvolvimento do Projeto Integrador do 2º semestre.

Arquivos sensíveis, como `.env`, senhas, dependências instaladas e arquivos locais, não devem ser enviados para o repositório.
