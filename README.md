# Sistema de Gerenciamento de Biblioteca

Sistema web para gerenciamento de biblioteca desenvolvido em PHP e MySQL.

## Requisitos

- PHP 7.4 ou superior
- MySQL 5.7 ou superior
- Composer
- Servidor web (Apache, Nginx, etc.)

## Instalação

1. Clone o repositório:
```bash
git clone [URL_DO_REPOSITORIO]
cd biblioteca
```

2. Instale as dependências via Composer:
```bash
composer install
```

3. Crie o banco de dados MySQL:
```bash
mysql -u root -p < database/biblioteca.sql
```

4. Configure a conexão com o banco de dados:
- Abra o arquivo `config/database.php`
- Ajuste as credenciais do banco de dados conforme necessário

5. Configure o servidor web:
- Aponte o DocumentRoot para a pasta do projeto
- Certifique-se de que o mod_rewrite está habilitado (se estiver usando Apache)

## Funcionalidades

### Cadastro de Livros
- Adicionar novos livros
- Editar informações dos livros
- Excluir livros
- Controle de quantidade disponível

### Cadastro de Usuários
- Cadastro de usuários da biblioteca
- Diferentes níveis de acesso (admin/usuario)
- Gerenciamento de informações pessoais

### Empréstimos
- Registro de empréstimos
- Controle de devoluções
- Alertas de atraso
- Histórico de empréstimos

### Consulta
- Busca de livros por diferentes critérios
- Filtros por disponibilidade
- Visualização detalhada do acervo

### Relatórios
- Geração de relatórios em PDF
- Estatísticas do sistema
- Listagem de livros emprestados
- Controle de atrasos

## Estrutura do Projeto

```
biblioteca/
├── config/
│   └── database.php
├── database/
│   └── biblioteca.sql
├── includes/
│   ├── header.php
│   └── footer.php
├── vendor/
├── composer.json
├── index.php
├── login.php
├── logout.php
├── livros.php
├── usuarios.php
├── emprestimos.php
├── consulta.php
└── relatorios.php
```

## Segurança

- Senhas são armazenadas com hash seguro
- Proteção contra SQL Injection usando prepared statements
- Validação de dados de entrada
- Controle de sessão
- Diferentes níveis de acesso

## Contribuição

1. Faça um fork do projeto
2. Crie uma branch para sua feature (`git checkout -b feature/AmazingFeature`)
3. Commit suas mudanças (`git commit -m 'Add some AmazingFeature'`)
4. Push para a branch (`git push origin feature/AmazingFeature`)
5. Abra um Pull Request

## Licença

Este projeto está sob a licença MIT. Veja o arquivo `LICENSE` para mais detalhes. 