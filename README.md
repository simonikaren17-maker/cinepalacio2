# Cine Palácio • Cinema Brasileiro

> Plataforma cinematográfica dedicada ao resgate e celebração do **Cinema Brasileiro**, com referências diretas ao acervo do **IMDb Brasil**. Desenvolvido com arquitetura modular em PHP estruturado, banco de dados MySQL via PDO com prepared statements, interface responsiva em Tailwind CSS e estética inspirada na identidade visual de **"O Agente Secreto" (2025)** de Kleber Mendonça Filho e no calor granulado dos anos 70 de **"Cidade de Deus"**.

---

## 🎬 Funcionalidades e Regras de Negócio (CORE)

1. **Catálogo Completo com Filtros de Popularidade**:
   - Acervo ampliado com **70 filmes icônicos** da história do cinema brasileiro.
   - Divisão equilibrada entre **35 Mais Conhecidos** (grandes bilheterias e sucessos consagrados) e **35 Menos Conhecidos (Lado B, Cult, Cinema Marginal, Cinema Novo, Experimental e Documentários)**.
   - Filtros dinâmicos por múltiplos gêneros: Drama, Crime, Comédia, Suspense, Ficção Científica, Faroeste, Documentário, Terror, Animação, etc.
   - **Novas Sugestões**: Opção para **ocultar filmes já marcados como assistidos**, exibindo apenas novas recomendações para o cinéfilo.

2. **Classificação Interativa por Estrelas (1 a 5)**:
   - Sistema dinâmico com avaliação interativa em estrelas diretamente nos cartazes e na página detalhada do filme, com atualização em tempo real via AJAX.

3. **Busca Dinâmica com Autocomplete ao Digitar**:
   - Barra de busca na navegação que consulta o acervo em tempo real com debounce, exibindo mini cartaz, título, diretor, ano, classificação indicativa e nota IMDb.

4. **Meu Espaço (Área Personalizada)**:
   - Área exclusiva para usuários cadastrados acompanharem seus filmes **Assistidos**, **Curtidos (Favoritos)** e **Notas em Estrelas**, com estatísticas e métricas de consumo cultural.

5. **Histórico Completo de Auditoria**:
   - Registro automático e persistente de **todas as ações e alterações** efetuadas por usuários (cadastros, logins, avaliações, mudanças de status, redefinição de senhas) com carimbo de data/hora, endereço IP e agente de navegação, acessível na página de **Histórico & Auditoria**.

6. **Segurança e Criptografia**:
   - Senhas criptografadas utilizando hash **Bcrypt** (`password_hash` com cost 12 e `password_verify`).
   - Conexão e queries 100% blindadas contra SQL Injection utilizando **PDO com Prepared Statements**.
   - Fluxo seguro de **redefinição de senha** via tokens temporários criptográficos de 1 hora.

7. **Tratamento de Interface Retrô**:
   - **Notificações Toast**: Feedback claro e visual para qualquer erro, confirmação ou aviso do sistema.
   - **Modais Retrô Customizados**: Tratamento elegante de caixas de mensagens e confirmações, eliminando popups nativos do navegador.
   - **Busca Automática de Capa**: Integração com API aberta da Wikimedia/Wikipedia por título e diretor caso a capa esteja ausente, além de gerador de cartaz vetorial SVG estilizado em anos 70.

---

## 🎨 Identidade Visual e Estética

A paleta de cores e tipografia foram projetadas com base no cartaz oficial do filme **O Agente Secreto (2025)** e na atmosfera de **Cidade de Deus**:
- **Âmbar Solar & Ouro Envelhecido**: `#E5A93C`, `#F59E0B`, `#D97706`
- **Terracota & Laranja Queimado**: `#C2410C`, `#9A3412`
- **Tabaco & Sepia Profundo**: `#140E0A`, `#201610`, `#18110D`
- **Linho e Creme Envelhecido**: `#FAF0E6`, `#EAD8C0`
- **Selos Oficiais de Classificação Indicativa**: Padrão Ministério da Justiça (L, 10, 12, 14, 16, 18).
- **Tipografia**: Fontes com sabor de cinema de rua dos anos 70 (*Bebas Neue*, *Cinzel*, *Special Elite*).

---

## 📁 Estrutura Modular do Projeto

```
cinepalacio/
├── config/
│   ├── database.php        # Constantes de configuração do banco (Host, Porta, Usuário, Senha)
│   └── bd.php              # Conexão PDO segura (MySQL com suporte a fallback de desenvolvimento)
├── database/
│   ├── schema.sql          # Estrutura DDL e carga dos 30 filmes brasileiros clássicos e Lado B
│   └── seed.php            # Verificador e seeder da base via linha de comando
├── includes/
│   ├── auth.php            # Gerenciamento de sessões nativas, CSRF e helpers de autenticação
│   ├── logger.php          # Função registrarLog() para auditoria de todas as alterações
│   ├── header.php          # Cabeçalho HTML5 responsivo com busca dinâmica e navegação MobileFirst
│   ├── footer.php          # Rodapé estilizado anos 70, créditos e disparador de Toasts
│   └── modal.php           # Componente de modal para substituir caixas de diálogo nativas
├── actions/
│   ├── auth_cadastro.php   # Cadastro de usuário com validação e hash Bcrypt
│   ├── auth_login.php      # Login com verificação Bcrypt e log de acesso
│   ├── auth_logout.php     # Encerramento de sessão com log de auditoria
│   ├── auth_recuperar.php  # Solicitação de redefinição de senha e geração de token seguro
│   ├── auth_redefinir.php  # Aplicação da nova senha com hash Bcrypt
│   ├── interacao.php       # Ações de avaliar estrelas, marcar assistido e curtir
│   ├── buscar_sugestoes.php# API JSON de busca dinâmica ao digitar no input
│   └── buscar_capa.php     # Serviço de busca de capa alternativa na web por nome e diretor
├── assets/
│   ├── css/
│   │   └── custom.css      # Granulação anos 70, letreiro neon, selos e modais
│   ├── js/
│   │   ├── main.js         # Interações AJAX com estrelas, toasts e controle de modais
│   │   └── busca.js        # Autocomplete com debounce para a barra de busca
│   └── images/
│       └── placeholder.svg # Cartaz de fallback estilizado
├── index.php               # Catálogo com filtros de mais/menos conhecidos, gêneros e assistidos
├── filme.php               # Detalhes do filme com capa, classificação indicativa e sinopse
├── imagem.php              # Servidor e cache local de cartazes em alta resolução (sem bloqueio de CORS/hotlink)
├── meu_espaco.php          # Área do usuário logado (filmes curtidos, assistidos e notas)
├── historico.php           # Página de auditoria com histórico de todas as alterações
├── login.php               # Tela de autenticação retrô
├── cadastro.php            # Tela de cadastro simples (email + senha)
├── esqueci_senha.php       # Solicitação de link de redefinição de senha
├── redefinir_senha.php     # Formulário de nova senha com validação de token
├── setup.php               # Painel de diagnóstico e testes do banco de dados
├── tests/
│   └── test_flow.php       # Suíte de testes automatizados das regras de negócio
├── README.md               # Documentação do projeto
└── requisitos.md           # Especificação original dos requisitos
```

---

## 🚀 Como Executar o Projeto

### Pré-requisitos:
- **PHP 8.1+** com extensões `pdo_mysql`, `pdo_sqlite` e `curl` habilitadas.
- **MySQL / MariaDB** (via XAMPP, WampServer, Laragon, Docker ou serviço nativo).

### 1. Iniciar o Servidor Web Embutido do PHP:
No diretório do projeto, execute:
```bash
php -S localhost:8000
```

### 2. Acessar a Aplicação:
Abra seu navegador em:
```
http://localhost:8000
```

### 3. Configuração do MySQL:
- As configurações padrão em `config/database.php` conectam em `127.0.0.1:3306`, usuário `root` e senha vazia `""`.
- Caso utilize outras credenciais, ajuste as constantes em `config/database.php` ou defina as variáveis de ambiente `DB_HOST`, `DB_USER`, `DB_PASS`, `DB_NAME`.
- Você também pode importar diretamente o arquivo `database/schema.sql` ou utilizar a tela de diagnóstico em `http://localhost:8000/setup.php`.
- O sistema possui **fallback automático transparente** que mantém 100% das funções operacionais mesmo se o MySQL ainda não tiver sido iniciado.

---

## 🧪 Testes Automatizados

O projeto inclui uma suíte completa de testes unitários e de integração das regras de negócio em `tests/test_flow.php`:
```bash
php tests/test_flow.php
```
Testa:
- Hashing e validação com **Bcrypt**
- Inserção e sanitização com **PDO Prepared Statements**
- Auditoria de logs em **todas as ações**
- Avaliação com **1 a 5 estrelas**
- Marcação de **Assistido** e **Favorito**
- Filtro de **ocultar filmes assistidos** nas novas sugestões
- Geração e expiração de **token seguro de redefinição**
- **Busca dinâmica** e integridade do catálogo
