<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// petite màj git

/**
 * Contrôleur de la page d'accueil
 */
function genHome()
{
    $articleModel = new ArticleModel();
    //dump($articleModel);

    $lastArticles = $articleModel->getAllArticles(5);

    $flashmessage = getFlashMessage();

    // affiche : inclusion du fichier de template
    $template = 'home';
    include TEMPLATE_DIR . '/base.phtml';
}

/**
 * Contrôleur de la page article
 */
function genArticle()
{
        $articleModel = new ArticleModel();

        $idArticle = (int)$_GET['idArticle'];

        if(!array_key_exists('idArticle',$_GET)||!$_GET['idArticle']||!ctype_digit($_GET['idArticle'])){
                echo '<p>ERREUR : Id article manquant ou incorrect</p>';
                exit;
        }

        $article = $articleModel->getOneArticle($idArticle);

        // test pour savoir si l'article existe
        if(!$article){
                echo 'ERREUR : aucun article ne possède l\'ID '.$idArticle;
                exit;
        }


        // Traitement des données de formulaire d'ajout de commentaires
        if (!empty($_POST)) {
        //Récupération
        $content = trim($_POST['content']);
        
        // A FAIRE validation
        $erreurs = [];

        // on vérifie si le champs content est vide => message d'erreur
        if (!$content) {
            $erreurs['content'] = "Le champs commentaire est obligatoire";
        }

    // Si pas d'erreurs
    if (empty($erreurs)) {

        $commentModel = new CommentModel();

        //récupération des données
        $content = trim($_POST['content']);
        $rate=$_POST['rate'];

        $comment = $commentModel->insertComment($content, $idArticle, $rate, $_SESSION['user']['idUser']);
/*         $sql = 'INSERT INTO comment (content, articleId, rate)
                VALUES (?, ?, ?)';

        prepareAndExecute($sql, [$content, $idArticle, $rate]); */

        // Redirection
        header('Location: index.php?action=article&idArticle=' . $idArticle);
        exit;
    }

}
        $commentModel = new CommentModel();
        $commentsArticle = $commentModel->getCommentsByArticleId($idArticle);

        $idNextArticle = $articleModel->toNextPage($idArticle);
        $idPrevArticle = $articleModel->toPrevPage($idArticle);

       // affichage : inclusion du fichier template
       $template = 'article';
       include TEMPLATE_DIR . '/base.phtml';
}




/**
 * Contrôleur de la page contact
 */
function genContact()
{
        // affichage : inclusion du fichier template
        $template = 'contact';
        include TEMPLATE_DIR . '/base.phtml';
}

/**
 * Contrôleur de la page mentions légales
 */
function genMentions()
{
        // affichage : inclusion du fichier template
        $template = 'mentions';
        include TEMPLATE_DIR . '/base.phtml';
}

/**
 * Contrôleur de la page de création de compte
 */
function genSignup()
{
    $userModel = new UserModel();

    // $ pour être connectée directement après m'etre inscrite 
    $userSession = new UserSession();

    $firstname = '';
    $lastname = '';
    $email = '';
    $password = '';
    $password_confirm = '';

    // Si le formulaire est soumis... 
    if (!empty($_POST)) {

        // On récupère les données du formulaire
        $firstname = trim($_POST['firstname']);
        $lastname = trim($_POST['lastname']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $password_confirm = $_POST['password-confirm'];

        // Validation du formulaire (à faire en dernier quand ça fonctionne sans erreur)
        $errors = validateSignupForm($firstname, $lastname, $email, $password, $password_confirm);

        // S'il n'y a pas d'erreur, si tout est OK
        if (empty($errors)) {

            // Hashage du mot de passe
            $hash = password_hash($password, PASSWORD_DEFAULT);

            // On fait appel au modèle ( la fonction insertUser() ) pour insérer les données dans la table user
            $userModel->insertUser($firstname, $lastname, $email, $hash);

            // avec ça en plus je suis connectée directement après m'etre inscrite 
            $user = $userModel->checkCredentials($email, $password);
            $userSession->userRegister($user['idUser'], $user['lastname'], $user['firstname'], $user['email'], $user['role']);
            
            // Ajout d'un message flash en session
            addFlashMessage('Votre compte a bien été créé');

            // On redirige l'internaute pour l'instant vers la page d'accueil
            header('Location: index.php');
            exit;
            
        }
    }

    // Affichage : inclusion du fichier de template
    $template = 'signup';
    include TEMPLATE_DIR . '/base.phtml';
}

function genLogin(){

    $userModel = new UserModel();
    $userSession = new UserSession();

    if(!empty($_POST)){
        $email = trim($_POST['email']);
        $password = $_POST['password'];

        $user = $userModel->checkCredentials($email, $password);

    if ($user){
        $userSession->userRegister($user['idUser'], $user['lastname'], $user['firstname'], $user['email'], $user['role']);

        // Ajout d'un message flash en session
        addFlashMessage('Bonjour '.$user['firstname'].' ! Vous êtes connecté.e');

        // On redirige l'internaute pour l'instant vers la page d'accueil
        header('Location: index.php');
        exit;
    }
        $errors['message'] = 'identifiants incorrects';
    } 

    
    // Affichage : inclusion du fichier de template
    $template = 'login';
    include TEMPLATE_DIR . '/base.phtml';
}