# Cine Pálacio
## Objetivo
Criar um site que gera lista de filmes Brasileiros filtrados por mais ou menos conhecidos, usando de referencia 
vamos puxar os filmes para nossa base deste site https://www.imdb.com/pt/
os usuários vão poder classificar o filmes clicando em estrelas.

### Stack Tecnlógico
- Backend: PHP estruturado com sessões nativas
- Banco de dados: MySQL (PDO para segurança)
- Frontend: HTML5, PHP, CSS, Tailwind CSS

#### Regras de negócio (CORE)
- Tratar senhas de usuários com hash bcrypt
- O sistema deve ter uma página de históricos e manter sempre os logs de qualquer alteração feita por qualquer usuário, para auditorias futuras.
- cada usuário tem seu espaço onde ele marca os filmes que ele gostou ou assistiu.
- todo usuário que quiser ter essa lista personalizada precisa de se cadastrar.
- o cadastro deve ser simples, com Email, e senha.
- o site deve dar a opção de redefinição de senha para o usuario.
- o site deve ter filtros de gêneros, e filmes marcados como assistido não deve aparecer na lista de novas sugestões.
- as buscas deve ser dinamica, na barra de busca deve aparcer as sugestões ao digitar.
- todas as paginas de filmes deve aparecer a classificação indicativa, a sinopse e a capa, se não tiver a capa no imdb procurar pelo nome do filme e o diretor para encontra-la
- a cores usadas para o projeto deve ter as cores na estetica do material de divulgação do filme Agente Secreto de 2025, estilo anos 70 e que a estetica fique entre cidade de Deus. https://upload.wikimedia.org/wikipedia/pt/6/60/O_Agente_Secreto_%28Cartaz_brasileiro%29.jpg?utm_source=pt.wikipedia.org&utm_campaign=index&utm_content=original

##### Regras Globais
- Use sempre PDO para conexão e queries no MySQL para evitar SQL Injections
- Mantenha o código limpo e comente apenas logicas complexas.
- Separe os arquivos de forma lógica: um arquivo para conexão com a base (bd.php) e scripts de backend isolados e views em HTML5/PHP, nunca faça o sistema como um monolito, deixe sempre separados todas as regras para facilitar os futuros upgrades.
 - Estilize as telas em Tailwind de forma responsiva priorizando o MobileFrist.
 - Retorne sempre as mensagens de erros de forma claras na interface para o usuário (TOAST)
 - sempre trate as mensagens de caixa de mensagens nativas do navegador em um MODAL
