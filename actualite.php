<?php
// Modèles

class Article {
    public $id;
    public $titre;
    public $contenu;
    public $dateCreation;
    public $categorie;

    public function __construct($id, $titre, $contenu, $dateCreation, $categorie) {
        $this->id = $id;
        $this->titre = $titre;
        $this->contenu = $contenu;
        $this->dateCreation = $dateCreation;
        $this->categorie = $categorie;
    }
}

class Categorie {
    public $id;
    public $libelle;

    public function __construct($id, $libelle) {
        $this->id = $id;
        $this->libelle = $libelle;
    }
}

class ArticleRepository {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getAllArticles() {
        $sql = "SELECT Article.id, Article.titre, Article.contenu, Article.dateCreation, Categorie.libelle
                FROM Article
                INNER JOIN Categorie ON Article.categorie = Categorie.id";
        
        $result = $this->conn->query($sql);
        $articles = [];

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $article = new Article($row["id"], $row["titre"], $row["contenu"], $row["dateCreation"], $row["libelle"]);
                $articles[] = $article;
            }
        }

        return $articles;
    }

    public function getArticlesByCategory($categorieId) {
        $sql = "SELECT Article.id, Article.titre, Article.contenu, Article.dateCreation, Categorie.libelle
                FROM Article
                INNER JOIN Categorie ON Article.categorie = Categorie.id
                WHERE Article.categorie = " . intval($categorieId);
        
        $result = $this->conn->query($sql);
        $articles = [];

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $article = new Article($row["id"], $row["titre"], $row["contenu"], $row["dateCreation"], $row["libelle"]);
                $articles[] = $article;
            }
        }

        return $articles;
    }
}

// Contrôleur

class ArticleController {
    private $articleRepository;

    public function __construct($articleRepository) {
        $this->articleRepository = $articleRepository;
    }

    public function index($categorie = 'all') {
        if ($categorie === 'all') {
            $articles = $this->articleRepository->getAllArticles();
        } else {
            $articles = $this->articleRepository->getArticlesByCategory($categorie);
        }

        return $articles;
    }
}

// Connexion à la base de données MySQL
$servername = "localhost";
$username = "mglsi_user";
$password = "passer";
$dbname = "mglsi_news";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Instanciation du contrôleur
$articleRepository = new ArticleRepository($conn);
$articleController = new ArticleController($articleRepository);

// Récupération de la catégorie depuis l'URL
$categorie = isset($_GET['categorie']) ? $_GET['categorie'] : 'all';

// Appel de la méthode index du contrôleur pour récupérer les articles
$articles = $articleController->index($categorie);

// Fermeture de la connexion à la base de données
$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Site d'Actualités ESP</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4; /* Couleur de fond */
        }
        header {
            background-color: #87CEEB; /* Bleu clair */
            color: #fff; /* Blanc */
            padding: 20px 0;
            text-align: center;
        }
        header h1 {
            margin: 0;
        }
        nav {
            background-color: #00BFFF; /* Bleu clair */
            overflow: hidden;
        }
        nav a {
            float: left;
            display: block;
            color: #fff; /* Blanc */
            text-align: center;
            padding: 14px 16px;
            text-decoration: none;
        }
        nav a:hover {
            background-color: #e0f7ff; /* Bleu très clair */
            color: black;
        }
        .container {
            width: 80%;
            margin: auto;
            overflow: hidden;
            background-color: rgba(255, 255, 255, 0.8); /* Couleur de fond semi-transparente */
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .article {
            background: #fff; /* Blanc */
            border: 1px solid #ddd;
            margin: 20px 0;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        .article h2 {
            margin-top: 0;
        }
        .article p {
            line-height: 1.6;
        }
        .article .date {
            color: #999;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <header>
        <h1>ACTUALITES POLYTECHNICIENNES</h1>
    </header>
    <nav>
        <a href="?categorie=all">Accueil</a>
        <a href="?categorie=1">Sport</a>
        <a href="?categorie=2">Santé</a>
        <a href="?categorie=3">Éducation</a>
        <a href="?categorie=4">Politique</a>
    </nav>
    <div class="container">
        <?php foreach ($articles as $article): ?>
            <div class="article categorie-<?php echo htmlspecialchars($article->categorie); ?>">
                <h2><?php echo htmlspecialchars($article->titre); ?></h2>
                <p><?php echo htmlspecialchars($article->contenu); ?></p>
                <p class="date">Publié le <?php echo date('d/m/Y', strtotime($article->dateCreation)); ?> dans la catégorie <?php echo htmlspecialchars($article->categorie); ?></p>
            </div>
        <?php endforeach; ?>
        <?php if (empty($articles)): ?>
            <p>Aucun article trouvé.</p>
        <?php endif; ?>
    </div>

    <script>
        function filtrerArticles(categorie) {
            var articles = document.querySelectorAll('.article');
            articles.forEach(function(article) {
                if (categorie === 'all' || article.classList.contains('categorie-' + categorie)) {
                    article.style.display = 'block';
                } else {
                    article.style.display = 'none';
                }
            });
        }
    </script>
</body>
</html>
