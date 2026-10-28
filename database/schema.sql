-- ========================================================
-- Cine Palácio - Banco de Dados de Filmes Brasileiros
-- Estrutura MySQL com suporte a PDO (70 Obras Clássicas e Lado B)
-- ========================================================

CREATE DATABASE IF NOT EXISTS cinepalacio CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE cinepalacio;

-- 1. Tabela de Usuários (com senhas em Bcrypt e suporte a recuperação)
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(191) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    token_recuperacao VARCHAR(100) DEFAULT NULL,
    token_expira DATETIME DEFAULT NULL,
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Tabela de Filmes Brasileiros (Mais conhecidos e Menos conhecidos)
CREATE TABLE IF NOT EXISTS filmes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    ano INT NOT NULL,
    diretor VARCHAR(255) NOT NULL,
    generos VARCHAR(255) NOT NULL,
    classificacao_indicativa VARCHAR(10) NOT NULL,
    duracao_min INT DEFAULT 100,
    sinopse TEXT NOT NULL,
    capa_url TEXT DEFAULT NULL,
    imdb_id VARCHAR(50) DEFAULT NULL,
    imdb_url VARCHAR(255) DEFAULT NULL,
    nota_imdb DECIMAL(3,1) DEFAULT 7.5,
    popularidade VARCHAR(50) NOT NULL DEFAULT 'mais_conhecido',
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Tabela de Interações do Usuário (Assistidos, Gostou, Avaliação em Estrelas)
CREATE TABLE IF NOT EXISTS interacoes_usuario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    filme_id INT NOT NULL,
    assistido TINYINT(1) DEFAULT 0,
    gostou TINYINT(1) DEFAULT 0,
    avaliacao_estrelas INT DEFAULT NULL,
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_usuario_filme (usuario_id, filme_id),
    CONSTRAINT fk_interacao_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    CONSTRAINT fk_interacao_filme FOREIGN KEY (filme_id) REFERENCES filmes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Tabela de Históricos de Logs (Auditoria de todas as ações de usuários)
CREATE TABLE IF NOT EXISTS historico_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT DEFAULT NULL,
    acao VARCHAR(100) NOT NULL,
    detalhes TEXT DEFAULT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent TEXT DEFAULT NULL,
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_log_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================================
-- Carga Ampliada de Filmes do Cinema Brasileiro (IMDb de Referência)
-- ========================================================

INSERT INTO filmes (id, titulo, ano, diretor, generos, classificacao_indicativa, duracao_min, sinopse, capa_url, imdb_id, imdb_url, nota_imdb, popularidade) VALUES
-- MAIS CONHECIDOS (GRANDES SUCESSOS E CLÁSSICOS POPULARES)
(1, 'Cidade de Deus', 2002, 'Fernando Meirelles, Kátia Lund', 'Crime, Drama', '18', 130, 
'Nas favelas do Rio de Janeiro dos anos 1960 a 1980, dois rapazes seguem caminhos divergentes: Buscapé sonha em ser fotógrafo e Zé Pequeno se torna o chefe violento do tráfico local.', 
'https://upload.wikimedia.org/wikipedia/pt/1/10/CidadedeDeus.jpg', 'tt0317248', 'https://www.imdb.com/title/tt0317248/', 8.6, 'mais_conhecido'),

(2, 'O Agente Secreto', 2025, 'Kleber Mendonça Filho', 'Suspense, Drama, Histórico', '16', 135, 
'Em 1977, no auge da ditadura militar no Brasil, um professor universitário e especialista em tecnologia foge de São Paulo para Recife buscando refúgio, mas se vê no centro de uma perigosa conspiração de espionagem e paranóia tropical.', 
'https://upload.wikimedia.org/wikipedia/pt/6/60/O_Agente_Secreto_%28Cartaz_brasileiro%29.jpg', 'tt27814988', 'https://www.imdb.com/title/tt27814988/', 8.1, 'mais_conhecido'),

(3, 'Central do Brasil', 1998, 'Walter Salles', 'Drama', '12', 110, 
'Dora escreve cartas para analfabetos na Central do Brasil no Rio de Janeiro. Após a trágica morte da mãe de Josué, um garoto de nove anos, ela parte em uma jornada pelo sertão nordestino para ajudá-lo a encontrar o pai.', 
'https://upload.wikimedia.org/wikipedia/pt/a/a2/Central_do_Brasil_poster.jpg', 'tt0140888', 'https://www.imdb.com/title/tt0140888/', 8.0, 'mais_conhecido'),

(4, 'O Auto da Compadecida', 2000, 'Guel Arraes', 'Comédia, Aventura, Fantasia', '12', 104, 
'As aventuras hilárias dos sertanejos João Grilo e Chicó que vivem de golpes para sobreviver no sertão paraibano, enfrentando valentões, coronéis e até o julgamento celestial com a Virgem Maria e Jesus.', 
'https://upload.wikimedia.org/wikipedia/pt/b/bf/O_auto_da_compadecida.jpg', 'tt0271383', 'https://www.imdb.com/title/tt0271383/', 8.7, 'mais_conhecido'),

(5, 'Tropa de Elite', 2007, 'José Padilha', 'Ação, Crime, Drama', '16', 115, 
'Em 1997, o Capitão Nascimento do BOPE procura um substituto para seu posto enquanto lidera operações implacáveis de pacificação nas favelas do Rio de Janeiro antes da visita do Papa João Paulo II.', 
'https://upload.wikimedia.org/wikipedia/pt/2/2a/TropaDeElitePoster.jpg', 'tt0861739', 'https://www.imdb.com/title/tt0861739/', 8.0, 'mais_conhecido'),

(6, 'Tropa de Elite 2: O Inimigo Agora É Outro', 2010, 'José Padilha', 'Ação, Crime, Drama', '16', 115, 
'Dez anos depois, Nascimento enfrenta um novo inimigo ainda mais perigoso: as milícias paramilitares e o sistema político corrompido que controlam a segurança pública do Rio de Janeiro.', 
'https://upload.wikimedia.org/wikipedia/pt/1/1b/Tropa_de_elite_2_poster.jpg', 'tt1555149', 'https://www.imdb.com/title/tt1555149/', 8.0, 'mais_conhecido'),

(7, 'Bacurau', 2019, 'Kleber Mendonça Filho, Juliano Dornelles', 'Suspense, Mistério, Ficção Científica', '16', 131, 
'Pouco após a morte da matriarca Carmelita aos 94 anos, os moradores do remoto povoado de Bacurau no sertão brasileiro descobrem que a comunidade sumiu do mapa e está sob cerco de mercenários estrangeiros.', 
'https://upload.wikimedia.org/wikipedia/pt/e/ee/Bacurau_poster.png', 'tt2762506', 'https://www.imdb.com/title/tt2762506/', 7.3, 'mais_conhecido'),

(8, 'Ainda Estou Aqui', 2024, 'Walter Salles', 'Drama, Biografia, Histórico', '14', 135, 
'Adaptação do livro de Marcelo Rubens Paiva sobre a vida de Eunice Paiva e sua perseverança heróica após o desaparecimento e assassinato do marido, o ex-deputado Rubens Paiva, pela ditadura civil-militar brasileira.', 
'https://upload.wikimedia.org/wikipedia/pt/4/41/Ainda_Estou_Aqui_poster.jpg', 'tt28189870', 'https://www.imdb.com/title/tt28189870/', 8.4, 'mais_conhecido'),

(9, 'Aquarius', 2016, 'Kleber Mendonça Filho', 'Drama', '16', 146, 
'Clara, uma jornalista aposentada e escritora de 65 anos, é a última moradora remanescente do edifício Aquarius na orla de Boa Viagem, no Recife, e resiste bravamente ao assédio predatório de uma empreiteira.', 
'https://upload.wikimedia.org/wikipedia/pt/8/87/Aquarius_cartaz.jpg', 'tt4990176', 'https://www.imdb.com/title/tt4990176/', 7.4, 'mais_conhecido'),

(10, 'Que Horas Ela Volta?', 2015, 'Anna Muylaert', 'Drama, Comédia', '12', 112, 
'Val é uma pernambucana que trabalha há anos como empregada doméstica para uma família rica de São Paulo. A chegada inesperada de sua filha Jéssica para prestar vestibular questiona e subverte as velhas relações de classe.', 
'https://upload.wikimedia.org/wikipedia/pt/9/98/Que_Horas_Ela_Volta_poster.jpg', 'tt3742378', 'https://www.imdb.com/title/tt3742378/', 7.7, 'mais_conhecido'),

(11, 'Estômago', 2007, 'Marcos Jorge', 'Comédia, Drama, Crime', '16', 113, 
'Raimundo Nonato migra do interior para a cidade grande e descobre seu talento nato para a culinária. Seu dom o faz prosperar em um boteco, em um restaurante refinado e, surpreendentemente, dentro da prisão.', 
'https://upload.wikimedia.org/wikipedia/pt/3/36/Estomago_cartaz.jpg', 'tt1039960', 'https://www.imdb.com/title/tt1039960/', 7.7, 'mais_conhecido'),

(12, 'O Som ao Redor', 2012, 'Kleber Mendonça Filho', 'Drama, Suspense', '16', 131, 
'A rotina de uma rua de classe média em Boa Viagem, Recife, é alterada pela chegada de uma empresa particular de segurança armada. Tensões sociais e históricas emergem no dia a dia dos vizinhos.', 
'https://upload.wikimedia.org/wikipedia/pt/7/7b/O_Som_ao_Redor_poster.jpg', 'tt2190367', 'https://www.imdb.com/title/tt2190367/', 7.1, 'mais_conhecido'),

(13, 'Hoje Eu Quero Voltar Sozinho', 2014, 'Daniel Ribeiro', 'Drama, Romance', '12', 96, 
'Leonardo é um jovem cego do ensino médio que busca conquistar sua autonomia diante da mãe superprotetora. Sua vida ganha novas cores quando Gabriel chega à nova escola.', 
'https://upload.wikimedia.org/wikipedia/pt/9/90/The_Way_He_Looks_poster.jpg', 'tt1700802', 'https://www.imdb.com/title/tt1700802/', 7.8, 'mais_conhecido'),

(14, 'Retratos Fantasmas', 2023, 'Kleber Mendonça Filho', 'Documentário, Histórico', '12', 93, 
'Uma jornada eletrizante e afetiva pelo centro do Recife a partir dos cinemas de rua que moldaram gerações e a própria relação do diretor com a memória, o tempo e a arquitetura urbana.', 
'https://upload.wikimedia.org/wikipedia/pt/8/8f/Retratos_Fantasmas_cartaz.jpg', 'tt27772421', 'https://www.imdb.com/title/tt27772421/', 7.6, 'mais_conhecido'),

(15, 'Carandiru', 2003, 'Héctor Babenco', 'Crime, Drama', '16', 145, 
'Com base nas memórias do médico Drauzio Varella, o filme retrata as vidas, os conflitos e os laços de solidariedade dos presidiários da infame Casa de Detenção de São Paulo antes do massacre de 1992.', 
'https://upload.wikimedia.org/wikipedia/pt/a/a2/Carandiru_filme.jpg', 'tt0293007', 'https://www.imdb.com/title/tt0293007/', 7.6, 'mais_conhecido'),

(16, 'Bingo: O Rei das Manhãs', 2017, 'Daniel Rezende', 'Biografia, Drama, Comédia', '16', 113, 
'Inspirado na vida real de Arlindo Barreto, um dos atores que interpretaram o palhaço Bozo nos anos 1980 na TV brasileira, alcançando fama estrondosa enquanto enfrenta conflitos com drogas e anonimato.', 
'https://upload.wikimedia.org/wikipedia/pt/8/87/Bingo_-_O_Rei_das_Manh%C3%A3s_poster.jpg', 'tt5051912', 'https://www.imdb.com/title/tt5051912/', 7.8, 'mais_conhecido'),

(17, 'O Palhaço', 2011, 'Selton Mello', 'Comédia, Drama, Aventura', '10', 90, 
'Benjamim e Valdemar formam uma dupla de palhaços no Circo Esperança. Em crise de identidade, Benjamim questiona se sua vocação é realmente fazer os outros rirem enquanto parte em busca de seu próprio sonho.', 
'https://upload.wikimedia.org/wikipedia/pt/4/4e/O_Palhaco.jpg', 'tt1977897', 'https://www.imdb.com/title/tt1977897/', 7.4, 'mais_conhecido'),

(18, 'O Lobo Atrás da Porta', 2013, 'Fernando Coimbra', 'Crime, Drama, Mistério', '16', 101, 
'O sequestro de uma criança em uma delegacia do subúrbio carioca leva a depoimentos contraditórios entre o pai, a mãe e a amante, revelando uma trama perturbadora de ciúmes, obsessão e vingança.', 
'https://upload.wikimedia.org/wikipedia/pt/0/07/A_Wolf_at_the_Door.jpg', 'tt3173774', 'https://www.imdb.com/title/tt3173774/', 7.5, 'mais_conhecido'),

(19, 'O Menino e o Mundo', 2013, 'Alê Abreu', 'Animação, Aventura, Fantasia', 'L', 80, 
'Obra-prima indicada ao Oscar de Melhor Filme de Animação. Um garoto do campo parte em uma jornada mágica e tocante pelo mundo em busca de seu pai que foi trabalhar na grande cidade industrial.', 
'https://upload.wikimedia.org/wikipedia/pt/8/87/O_Menino_e_o_Mundo.jpg', 'tt3183630', 'https://www.imdb.com/title/tt3183630/', 7.8, 'mais_conhecido'),

(20, 'Marte Um', 2022, 'Gabriel Martins', 'Drama', '16', 115, 
'Uma família negra de classe média baixa em Contagem, Minas Gerais, apoia o sonho do filho mais novo, Deivinho, de participar de uma missão de colonização no planeta Marte em meio aos desafios do Brasil contemporâneo.', 
'https://upload.wikimedia.org/wikipedia/pt/4/47/Marte_Um_cartaz.jpg', 'tt15065400', 'https://www.imdb.com/title/tt15065400/', 7.6, 'mais_conhecido'),

(21, 'Pixote, a Lei do Mais Fraco', 1981, 'Héctor Babenco', 'Crime, Drama', '18', 128, 
'Um jovem infrator de 10 anos foge de um reformatório brutal em São Paulo e tenta sobreviver nas ruas ao lado de outros meninos marginalizados e da prostituta Sueli, num dos mais aclamados retratos da infância no cinema mundial.', 
'https://upload.wikimedia.org/wikipedia/pt/c/c8/Pixote_A_Lei_do_Mais_Fraco.jpg', 'tt0082912', 'https://www.imdb.com/title/tt0082912/', 8.0, 'mais_conhecido'),

(22, 'Bye Bye Brasil', 1979, 'Carlos Diegues', 'Comédia, Drama, Aventura', '14', 110, 
'Salomé, Lorde Cigano e Andorinha viajam pelo interior profundo do Brasil com a Caravana Rolidei, apresentando números exóticos enquanto testemunham a chegada da modernização e da televisão ao sertão e à Amazônia.', 
'https://upload.wikimedia.org/wikipedia/pt/a/ab/Byebyebrazilposter.jpg', 'tt0078917', 'https://www.imdb.com/title/tt0078917/', 7.3, 'mais_conhecido'),

(23, 'Dona Flor e Seus Dois Maridos', 1976, 'Bruno Barreto', 'Comédia, Fantasia, Romance', '16', 120, 
'Florípedes se casa com o respeitável e calmo farmacêutico Teodoro após a morte de Vadinho, seu primeiro marido boêmio e mulherengo. Contudo, o fantasma de Vadinho retorna sensual e invisível para reconquistá-la.', 
'https://upload.wikimedia.org/wikipedia/pt/7/77/Dona_Flor_e_Seus_Dois_Maridos_filme.jpg', 'tt0074424', 'https://www.imdb.com/title/tt0074424/', 7.1, 'mais_conhecido'),

(24, 'Eles Não Usam Black-Tie', 1981, 'Leon Hirszman', 'Drama', '14', 120, 
'Numa fábrica metalúrgica nos anos 80 em São Paulo, o veterano líder operário Otávio lidera uma greve enquanto seu filho Tião fura a paralisação para não perder o emprego e garantir o casamento com Maria.', 
'https://upload.wikimedia.org/wikipedia/pt/3/30/Eles_n%C3%A3o_usam_black-tie_poster.jpg', 'tt0082320', 'https://www.imdb.com/title/tt0082320/', 7.8, 'mais_conhecido'),

(25, 'O Pagador de Promessas', 1962, 'Anselmo Duarte', 'Drama', '12', 95, 
'Único filme brasileiro vencedor da Palma de Ouro no Festival de Cannes. Zé do Burro carrega uma enorme cruz de madeira por léguas até Salvador para pagar uma promessa feita a Iansã em um terreiro de candomblé.', 
'https://upload.wikimedia.org/wikipedia/pt/1/18/O_Pagador_de_Promessas_poster.jpg', 'tt0056322', 'https://www.imdb.com/title/tt0056322/', 8.2, 'mais_conhecido'),

(26, 'Vidas Secas', 1963, 'Nelson Pereira dos Santos', 'Drama', '12', 103, 
'Clássico absoluto do Cinema Novo baseado na obra de Graciliano Ramos. Acompanha a peregrinação sofrida e silenciosa da família de Fabiano, Sinhá Vitória, dois filhos e a cadela Baleia fugindo da seca impiedosa no sertão.', 
'https://upload.wikimedia.org/wikipedia/pt/1/14/Vidas_secas.jpg', 'tt0057659', 'https://www.imdb.com/title/tt0057659/', 8.0, 'mais_conhecido'),

(27, 'Bicho de Sete Cabeças', 2000, 'Laís Bodanzky', 'Drama', '14', 74, 
'Seu Wilson encontra um baseado no casaco de seu filho Neto. Em pânico e sem diálogo, interna o rapaz à força em um manicômio judiciário corrupto, desencadeando um pesadelo kafkiano de abusos institucionais.', 
'https://upload.wikimedia.org/wikipedia/pt/2/23/Bicho_de_sete_cabecas.jpg', 'tt0264415', 'https://www.imdb.com/title/tt0264415/', 7.7, 'mais_conhecido'),

(28, 'O Homem que Copiava', 2003, 'Jorge Furtado', 'Comédia, Crime, Romance', '16', 123, 
'André é um jovem tímido de Porto Alegre que opera uma máquina fotocopiadora. Apaixonado pela vizinha Sílvia, ele bola um plano surreal envolvendo notas falsas de 50 reais, assaltos e a loteria para conquistá-la.', 
'https://upload.wikimedia.org/wikipedia/pt/6/69/O_homem_que_copiava_poster.jpg', 'tt0367859', 'https://www.imdb.com/title/tt0367859/', 7.6, 'mais_conhecido'),

(29, 'Lisbela e o Prisioneiro', 2003, 'Guel Arraes', 'Comédia, Romance, Aventura', '12', 106, 
'No sertão pernambucano, o malandro conquistador Leléu se apaixona perdidamente pela sonhadora Lisbela, que é noiva do almofadinha Douglas e filha do temido delegado da cidade.', 
'https://upload.wikimedia.org/wikipedia/pt/8/8e/Lisbela_e_o_Prisioneiro.jpg', 'tt0368837', 'https://www.imdb.com/title/tt0368837/', 7.5, 'mais_conhecido'),

(30, 'Madame Satã', 2002, 'Karim Aïnouz', 'Biografia, Drama, Crime', '18', 105, 
'Retrato visceral do lendário transformista, malandro e capoeirista João Francisco dos Santos na vibrante boêmia da Lapa carioca nos anos 1930, antes de se consagrar como o mito urbano Madame Satã.', 
'https://upload.wikimedia.org/wikipedia/pt/1/14/Madame_Sata.jpg', 'tt0317887', 'https://www.imdb.com/title/tt0317887/', 7.1, 'mais_conhecido'),

(31, 'Marighella', 2019, 'Wagner Moura', 'Ação, Biografia, Drama', '16', 155, 
'Biografia do escritor e guerrilheiro comunista Carlos Marighella, que liderou a Ação Libertadora Nacional (ALN) armada contra a ditadura militar brasileira durante os anos de chumbo no final dos anos 1960.', 
'https://upload.wikimedia.org/wikipedia/pt/7/79/Marighella_%28filme%29.jpg', 'tt7972044', 'https://www.imdb.com/title/tt7972044/', 6.7, 'mais_conhecido'),

(32, 'Medida Provisória', 2020, 'Lázaro Ramos', 'Ficção Científica, Drama', '14', 120, 
'Em um futuro distópico no Brasil, um governo autoritário decreta uma medida provisória obrigando todos os cidadãos de pele negra a serem deportados compulsoriamente para países africanos.', 
'https://upload.wikimedia.org/wikipedia/pt/d/df/Medida_Provisoria_cartaz.jpg', 'tt11979924', 'https://www.imdb.com/title/tt11979924/', 6.8, 'mais_conhecido'),

(33, 'Minha Mãe É uma Peça', 2013, 'André Pellenz', 'Comédia', '12', 85, 
'Dona Hermínia é uma mãe hiperprotetora e hilária de meia-idade que descobre que seus filhos adolescentes a acham chata. Ela resolve sair de casa e se hospedar na tia para ensiná-los uma lição.', 
'https://upload.wikimedia.org/wikipedia/pt/8/87/Minha_M%C3%A3e_%C3%A9_uma_Pe%C3%A7a_-_O_Filme.jpg', 'tt2958082', 'https://www.imdb.com/title/tt2958082/', 7.0, 'mais_conhecido'),

(34, 'Cazuza - O Tempo Não Pára', 2004, 'Sandra Werneck, Walter Carvalho', 'Biografia, Drama, Musical', '16', 128, 
'A trajetória meteórica e rebelde do cantor e poeta Cazuza, da liderança do Barão Vermelho aos sucessos em carreira solo e sua corajosa luta pública contra a AIDS até seu falecimento em 1990.', 
'https://upload.wikimedia.org/wikipedia/pt/d/d4/Cazuza_o_tempo_nao_para_poster.jpg', 'tt0363514', 'https://www.imdb.com/title/tt0363514/', 7.3, 'mais_conhecido'),

(35, 'Durval Discos', 2002, 'Anna Muylaert', 'Comédia, Drama, Mistério', '14', 96, 
'Durval e sua mãe Carmita vivem nos fundos de uma loja de vinis em Pinheiros, São Paulo. A contratação de uma nova diarista misteriosa que deixa para trás uma menina transforma a rotina cômica em suspense.', 
'https://upload.wikimedia.org/wikipedia/pt/7/7f/Durval_Discos.jpg', 'tt0345167', 'https://www.imdb.com/title/tt0345167/', 7.4, 'mais_conhecido'),

-- MENOS CONHECIDOS / CULT / CINEMA MARGINAL / CINEMA NOVO / LADO B
(36, 'O Bandido da Luz Vermelha', 1968, 'Rogério Sganzerla', 'Crime, Policial, Experimental', '16', 92, 
'Marco definitivo do Cinema Marginal brasileiro, acompanha as fugas, o exibicionismo e a paranoia de Jorge, o lendário assaltante que invadia mansões em São Paulo armado com uma lanterna de luz vermelha.', 
'https://upload.wikimedia.org/wikipedia/pt/b/b3/O_Bandido_da_Luz_Vermelha_poster.jpg', 'tt0062711', 'https://www.imdb.com/title/tt0062711/', 7.4, 'menos_conhecido'),

(37, 'A Lira do Delírio', 1978, 'Walter Lima Jr.', 'Drama, Mistério, Policial', '18', 105, 
'Num cabaré de Niterói durante o frenesi carnavalesco dos anos 70, o assassinato de uma criança desencadeia uma investigação repleta de delírio sensual, desespero e jogo de sombras.', 
'https://upload.wikimedia.org/wikipedia/pt/8/8c/A_Lira_do_Del%C3%ADrio_poster.jpg', 'tt0140333', 'https://www.imdb.com/title/tt0140333/', 6.9, 'menos_conhecido'),

(38, 'Terra em Transe', 1967, 'Glauber Rocha', 'Drama, Cinema Novo, Político', '14', 106, 
'Na fictícia república de Eldorado, o poeta e jornalista Paulo Martins agoniza entre o delírio da fé e as intrigas ferozes de poder entre populistas e conservadores.', 
'https://upload.wikimedia.org/wikipedia/pt/9/91/Terra_em_Transe_poster.jpg', 'tt0062352', 'https://www.imdb.com/title/tt0062352/', 7.4, 'menos_conhecido'),

(39, 'Bang Bang', 1971, 'Andrea Tonacci', 'Experimental, Faroeste, Comédia', '16', 85, 
'Um ator vive situações absurdas e hilárias pelas ruas de Belo Horizonte enquanto grava um filme de ação caótico com perseguições automobilísticas, cegos atiradores e macacos.', 
'https://upload.wikimedia.org/wikipedia/pt/6/69/Bang_Bang_1971_poster.jpg', 'tt0139875', 'https://www.imdb.com/title/tt0139875/', 7.1, 'menos_conhecido'),

(40, 'Deus e o Diabo na Terra do Sol', 1964, 'Glauber Rocha', 'Drama, Faroeste, Cinema Novo', '14', 120, 
'Após matar o patrão latifundiário que o explorava, o vaqueiro Manuel e sua mulher Rosa fogem pelo sertão castigado, cruzando o caminho do messianismo de Sebastião e da violência do cangaceiro Corisco.', 
'https://upload.wikimedia.org/wikipedia/pt/7/7b/Deus_e_o_diabo_na_terra_do_sol.jpg', 'tt0058006', 'https://www.imdb.com/title/tt0058006/', 7.7, 'menos_conhecido'),

(41, 'Alma Corsária', 1993, 'Carlos Reichenbach', 'Drama, Comédia', '16', 116, 
'Durante o lançamento do livro de dois poetas marginais em um boteco do Bixiga, um tiroteio irrompe, disparando lembranças nostálgicas e turbulentas dos anos 1960 e 1970 em São Paulo.', 
'https://upload.wikimedia.org/wikipedia/pt/a/ad/Alma_Cors%C3%A1ria_poster.jpg', 'tt0109088', 'https://www.imdb.com/title/tt0109088/', 7.1, 'menos_conhecido'),

(42, 'A Margem', 1967, 'Ozualdo Candeias', 'Drama, Cinema Marginal', '16', 96, 
'Às margens sujas do Rio Tietê, figuras desamparadas e sonhadoras da boêmia paulistana vivem histórias de amor trágico, abandono e ternura silenciosa.', 
'https://upload.wikimedia.org/wikipedia/pt/e/eb/A_Margem_poster.jpg', 'tt0061952', 'https://www.imdb.com/title/tt0061952/', 7.2, 'menos_conhecido'),

(43, 'Sem Essa, Aranha', 1970, 'Rogério Sganzerla', 'Comédia, Anárquico, Experimental', '18', 92, 
'Aranha, um homem decadente que finge cegueira para se aproveitar das pessoas, vagueia por uma favela carioca cercado por personagens extravagantes em ritmo de samba frenético.', 
'https://upload.wikimedia.org/wikipedia/pt/6/6e/Sem_Essa_Aranha_poster.jpg', 'tt0122247', 'https://www.imdb.com/title/tt0122247/', 6.8, 'menos_conhecido'),

(44, 'Tatuagem', 2013, 'Hilton Lacerda', 'Drama, Histórico, Romance', '16', 110, 
'Em 1978, no Recife sob ditadura militar, o grupo teatral anárquico Chão de Estrelas desafia a moral conservadora. Clécio, o líder da trupe, se apaixona pelo jovem soldado Fininha.', 
'https://upload.wikimedia.org/wikipedia/pt/2/25/Tatuagem_filme_poster.jpg', 'tt3044996', 'https://www.imdb.com/title/tt3044996/', 7.2, 'menos_conhecido'),

(45, 'Arábia', 2017, 'Affonso Uchôa, João Dumans', 'Drama', '12', 97, 
'Ao encontrar o diário de um operário que sofreu um acidente em uma usina de Ouro Branco, um jovem mergulha na trajetória comovente de trabalho, andanças e poesia cotidiana pelo interior de Minas Gerais.', 
'https://upload.wikimedia.org/wikipedia/pt/3/30/Ar%C3%A1bia_poster.jpg', 'tt6391444', 'https://www.imdb.com/title/tt6391444/', 7.2, 'menos_conhecido'),

(46, 'Branco Sai, Preto Fica', 2014, 'Adirley Queirós', 'Ficção Científica, Documentário', '14', 93, 
'Tiros em um baile de black music na Ceilândia dos anos 1980 ferem dois homens para sempre. Anos depois, um viajante do tempo vem do futuro coletar provas para incriminar o Estado autoritário.', 
'https://upload.wikimedia.org/wikipedia/pt/0/05/Branco_Sai_Preto_Fica_poster.jpg', 'tt3987088', 'https://www.imdb.com/title/tt3987088/', 7.0, 'menos_conhecido'),

(47, 'A Praga', 1980, 'José Mojica Marins', 'Terror', '18', 70, 
'Obra cult redescoberta do mestre do terror Zé do Caixão. Um jovem fotógrafo em viagem pelo campo desrespeita uma senhora idosa e é amaldiçoado com uma fome canibal incontrolável.', 
'https://upload.wikimedia.org/wikipedia/pt/b/b2/A_Praga_filme.jpg', 'tt15201430', 'https://www.imdb.com/title/tt15201430/', 6.5, 'menos_conhecido'),

(48, 'À Meia-Noite Levarei Sua Alma', 1964, 'José Mojica Marins', 'Terror, Cult', '16', 84, 
'Primeiro filme de terror brasileiro. O impiedoso coveiro Zé do Caixão aterroriza os habitantes de uma cidadezinha do interior na busca obsessiva por uma mulher capaz de gerar o filho perfeito para continuar seu sangue.', 
'https://upload.wikimedia.org/wikipedia/pt/5/52/A_Meia-Noite_Levarei_Sua_Alma.jpg', 'tt0056801', 'https://www.imdb.com/title/tt0056801/', 7.0, 'menos_conhecido'),

(49, 'Esta Noite Encarnarei no Teu Cadáver', 1967, 'José Mojica Marins', 'Terror, Cult', '16', 108, 
'Zé do Caixão sobrevive aos ferimentos e prossegue em sua busca macabra pela mulher ideal para perpetuar sua linhagem, enfrentando pesadelos dantescos que o transportam às chamas do próprio inferno.', 
'https://upload.wikimedia.org/wikipedia/pt/1/14/Esta_Noite_Encarnarei_no_Teu_Cad%C3%A1ver_poster.jpg', 'tt0060379', 'https://www.imdb.com/title/tt0060379/', 7.1, 'menos_conhecido'),

(50, 'O Menino e o Vento', 1967, 'Carlos Hugo Christensen', 'Mistério, Drama', '14', 110, 
'Em uma pacata cidadezinha do interior de Minas Gerais, a amizade pura e misteriosa entre um engenheiro forasteiro e um garoto livre que conversa com os ventos causa comoção e preconceito social.', 
'https://upload.wikimedia.org/wikipedia/pt/0/01/O_Menino_e_o_Vento_poster.jpg', 'tt0141603', 'https://www.imdb.com/title/tt0141603/', 7.1, 'menos_conhecido'),

(51, 'Mato Seco em Chamas', 2022, 'Joana Pimenta, Adirley Queirós', 'Ficção Científica, Ação, Drama', '18', 153, 
'Na favela Sol Nascente em Ceilândia, um grupo de mulheres destemidas comanda uma refinaria clandestina de petróleo no coração do cerrado e organiza um motim político em motos futuristas.', 
'https://upload.wikimedia.org/wikipedia/pt/a/a7/Mato_Seco_em_Chamas_poster.jpg', 'tt18274384', 'https://www.imdb.com/title/tt18274384/', 6.8, 'menos_conhecido'),

(52, 'Copacabana Mon Amour', 1970, 'Rogério Sganzerla', 'Experimental, Comédia', '18', 85, 
'Ao som das músicas de Gilberto Gil, a jovem Sônia Silk sonha em ser cantora de rádio enquanto perambula alucinada pelas calçadas e areias escaldantes de Copacabana nos anos de chumbo.', 
'https://upload.wikimedia.org/wikipedia/pt/a/a0/Copacabana_Mon_Amour_poster.jpg', 'tt0065576', 'https://www.imdb.com/title/tt0065576/', 6.7, 'menos_conhecido'),

(53, 'Macunaíma', 1969, 'Joaquim Pedro de Andrade', 'Comédia, Fantasia, Cinema Novo', '14', 108, 
'Adaptação do romance de Mário de Andrade sobre o anti-herói preguiçoso e metamorfo Macunaíma, que nasce negro no coração da floresta, torna-se branco e viaja para a metrópole enfrentar o gigante Venceslau Pietro Pietra.', 
'https://upload.wikimedia.org/wikipedia/pt/f/fb/Macunaima_poster.jpg', 'tt0064623', 'https://www.imdb.com/title/tt0064623/', 7.4, 'menos_conhecido'),

(54, 'Rio, 40 Graus', 1955, 'Nelson Pereira dos Santos', 'Drama, Neorrealismo', '12', 100, 
'Marco inaugural do cinema moderno brasileiro. Cinco jovens vendedores de amendoim percorrem diferentes pontos turísticos do Rio de Janeiro em um domingo escaldante, revelando as contradições sociais da cidade.', 
'https://upload.wikimedia.org/wikipedia/pt/1/1a/Rio_40_graus.jpg', 'tt0048560', 'https://www.imdb.com/title/tt0048560/', 7.5, 'menos_conhecido'),

(55, 'A Idade da Terra', 1980, 'Glauber Rocha', 'Experimental, Drama, Poético', '18', 158, 
'O testamento cinematográfico e profético de Glauber Rocha. Uma sinfonia audiovisual febril e apocalíptica que retrata quatro versões de Cristo (um negro, um militar, um guerrilheiro e um burguês) no Terceiro Mundo.', 
'https://upload.wikimedia.org/wikipedia/pt/d/d4/A_Idade_da_Terra.jpg', 'tt0080914', 'https://www.imdb.com/title/tt0080914/', 6.9, 'menos_conhecido'),

(56, 'Cabra Marcado para Morrer', 1984, 'Eduardo Coutinho', 'Documentário, Histórico', '12', 119, 
'Em 1964, Coutinho filmava a história do líder camponês João Pedro Teixeira, assassinado na Paraíba. Interrompido pelo golpe militar, o diretor retorna vinte anos depois para reencontrar a viúva Elizabeth e seus filhos.', 
'https://upload.wikimedia.org/wikipedia/pt/e/ee/Cabra_Marcado_para_Morrer.jpg', 'tt0087018', 'https://www.imdb.com/title/tt0087018/', 8.5, 'menos_conhecido'),

(57, 'Edifício Master', 2002, 'Eduardo Coutinho', 'Documentário, Drama', '12', 110, 
'Durante sete dias, o mestre do documentário Eduardo Coutinho e sua equipe entrevistam os moradores de um gigantesco prédio residencial de 276 quitinetes a uma quadra da praia de Copacabana, revelando vidas comoventes.', 
'https://upload.wikimedia.org/wikipedia/pt/4/4b/Edificio_master.jpg', 'tt0337617', 'https://www.imdb.com/title/tt0337617/', 8.1, 'menos_conhecido'),

(58, 'Santiago', 2007, 'João Moreira Salles', 'Documentário, Memória', 'L', 80, 
'Trezentos metros de película filmados em 1992 sobre o idoso e culto mordomo argentino Santiago, arquivados por quinze anos, são revisitados pelo diretor numa reflexão poética sobre memória, perda e cinema.', 
'https://upload.wikimedia.org/wikipedia/pt/7/75/Santiago_filme.jpg', 'tt0973842', 'https://www.imdb.com/title/tt0973842/', 7.8, 'menos_conhecido'),

(59, 'Ilha das Flores', 1989, 'Jorge Furtado', 'Documentário, Curta, Comédia Ácida', 'L', 13, 
'Um dos curtas-metragens mais premiados da história do cinema mundial. Acompanha a trajetória de um tomate, desde a colheita até o aterro sanitário da Ilha das Flores em Porto Alegre, traçando uma sátira ácida sobre o consumo.', 
'https://upload.wikimedia.org/wikipedia/pt/0/05/Ilha_das_Flores_cartaz.jpg', 'tt0097566', 'https://www.imdb.com/title/tt0097566/', 8.8, 'menos_conhecido'),

(60, 'As Boas Maneiras', 2017, 'Juliana Rojas, Marco Dutra', 'Terror, Fantasia, Drama', '14', 135, 
'Clara, uma enfermeira solitária da periferia de São Paulo, é contratada pela misteriosa e rica Ana como babá de seu filho ainda não nascido. Conforme a gravidez avança sob as noites de lua cheia, forças sobrenaturais emergem.', 
'https://upload.wikimedia.org/wikipedia/pt/b/b2/As_Boas_Maneiras_poster.jpg', 'tt5481940', 'https://www.imdb.com/title/tt5481940/', 6.8, 'menos_conhecido'),

(61, 'Trabalhar Cansa', 2011, 'Juliana Rojas, Marco Dutra', 'Drama, Suspense, Terror Social', '14', 99, 
'Helena realiza o sonho de abrir um pequeno mercadinho em São Paulo enquanto seu marido Otávio perde o emprego de classe média. Aos poucos, estranhas ocorrências e rachaduras misteriosas infectam o ambiente de trabalho.', 
'https://upload.wikimedia.org/wikipedia/pt/b/b0/Trabalhar_Cansa.jpg', 'tt1876527', 'https://www.imdb.com/title/tt1876527/', 6.8, 'menos_conhecido'),

(62, 'A Febre', 2019, 'Maya Da-Rin', 'Drama, Mistério', '10', 98, 
'Justino, um indígena Desana de 45 anos que trabalha como vigia de contêineres no porto de Manaus, é acometido por uma febre misteriosa quando sua filha se prepara para estudar medicina em Brasília.', 
'https://upload.wikimedia.org/wikipedia/pt/9/9f/A_Febre_poster.jpg', 'tt9709292', 'https://www.imdb.com/title/tt9709292/', 7.1, 'menos_conhecido'),

(63, 'Saneamento Básico, O Filme', 2007, 'Jorge Furtado', 'Comédia', '12', 112, 
'Os moradores de uma pequena comunidade de descendentes de italianos na serra gaúcha descobrem que há verba do governo apenas para a produção de um vídeo de ficção, e decidem gravar um filme de monstros para construir uma fossa.', 
'https://upload.wikimedia.org/wikipedia/pt/b/be/Saneamento_Basico_cartaz.jpg', 'tt0889218', 'https://www.imdb.com/title/tt0889218/', 7.6, 'menos_conhecido'),

(64, 'Amarelo Manga', 2002, 'Cláudio Assis', 'Drama, Comédia Ácida', '18', 103, 
'No calor e na podridão sedutora do centro do Recife, as vidas entrelaçadas de um açougueiro infiel, sua esposa evangélica, um homossexual dono de hotel e um necrófilo colidem numa atmosfera amarela pulsante.', 
'https://upload.wikimedia.org/wikipedia/pt/2/25/Amarelo_Manga.jpg', 'tt0312356', 'https://www.imdb.com/title/tt0312356/', 7.1, 'menos_conhecido'),

(65, 'Cinema, Aspirinas e Urubus', 2005, 'Marcelo Gomes', 'Drama, Aventura', '14', 99, 
'Em 1942, Johann, um alemão fugindo da Segunda Guerra Mundial, viaja pelo árido sertão nordestino vendendo aspirinas e exibindo anúncios cinematográficos para sertanejos ao lado do caroneiro Ranulpho.', 
'https://upload.wikimedia.org/wikipedia/pt/d/dc/Cinema%2C_Aspirinas_e_Urubus.jpg', 'tt0457849', 'https://www.imdb.com/title/tt0457849/', 7.2, 'menos_conhecido'),

(66, 'Meteorango Kid: Herói Intergalático', 1969, 'André Luiz Oliveira', 'Experimental, Anárquico, Comédia', '18', 85, 
'Obra cult máxima do cinema underground baiano dos anos 60. Um estudante universitário alienado e anárquico vaga pelas ruas de Salvador no dia do seu aniversário, enfrentando delírios psicodélicos sob ditadura.', 
'https://upload.wikimedia.org/wikipedia/pt/9/90/Meteorango_Kid.jpg', 'tt0141604', 'https://www.imdb.com/title/tt0141604/', 6.8, 'menos_conhecido'),

(67, 'O Dragão da Maldade contra o Santo Guerreiro', 1969, 'Glauber Rocha', 'Faroeste, Drama, Épico', '14', 95, 
'Conhecido internacionalmente como Antônio das Mortes. O caçador de cangaceiros Antônio das Mortes é contratado por coronéis para eliminar o último remanescente do cangaço, mas passa a questionar sua própria missão.', 
'https://upload.wikimedia.org/wikipedia/pt/1/1e/O_dragao_da_maldade_contra_o_santo_guerreiro.jpg', 'tt0064264', 'https://www.imdb.com/title/tt0064264/', 7.2, 'menos_conhecido'),

(68, 'No Coração do Mundo', 2019, 'Gabriel Martins, Maurílio Martins', 'Crime, Drama', '16', 120, 
'Na periferia de Contagem, moradores com diferentes anseios e dores buscam uma saída para suas vidas difíceis, culminando num arriscado plano de assalto a uma casa num condomínio fechado.', 
'https://upload.wikimedia.org/wikipedia/pt/5/5e/No_Coracao_do_Mundo_cartaz.jpg', 'tt8242488', 'https://www.imdb.com/title/tt8242488/', 7.1, 'menos_conhecido'),

(69, 'Rio Zona Norte', 1957, 'Nelson Pereira dos Santos', 'Drama, Musical', '12', 104, 
'Após cair de um vagão de trem, o humilde e talentoso compositor de samba Espírito da Luz recorda suas desilusões, os amores do morro e a exploração inescrupulosa de sua arte pelos magnatas da indústria fonográfica.', 
'https://upload.wikimedia.org/wikipedia/pt/4/4e/Rio_zona_norte.jpg', 'tt0050906', 'https://www.imdb.com/title/tt0050906/', 7.6, 'menos_conhecido'),

(70, 'O Ébrio', 1946, 'Gilda de Abreu', 'Drama, Musical, Melodrama', 'L', 125, 
'Um dos maiores fenômenos de bilheteria e comoção popular da história do cinema brasileiro. A trajetória comovente do médico e cantor que cai na ruína e no alcoolismo após traições da esposa e parentes gananciosos.', 
'https://upload.wikimedia.org/wikipedia/pt/a/a2/O_ebrio.jpg', 'tt0178997', 'https://www.imdb.com/title/tt0178997/', 7.5, 'menos_conhecido');
