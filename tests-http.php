<?php
/*
// URL de la requête
$urlRequete = $_SERVER['REQUEST_URI'];
echo '<hr>';
echo "URI de la requête : $urlRequete";

// La méthode de la requête 
$methodeRequete = $_SERVER['REQUEST_METHOD'];
echo '<hr>';
echo "Méthode de la requête $methodeRequete";

// Le chemin de la requête (la partie de l'URL suivant le nom du fichier de script)
$chemin = parse_url($urlRequete, PHP_URL_PATH);
echo '<hr>';
echo "Chemin de la requête $chemin";

// Les paramètres de l'URL (querystring)
$params = parse_url($urlRequete, PHP_URL_QUERY);
echo '<hr>';
echo "Paramètres de la requête (QueryString) : $params";
*/
$options = [PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ];
$pdo = new PDO("mysql:hote=localhost; dbname=leila; charset=utf8", 'root', '1234', $options);

function tout($pdo) {
    
    $reqParamPDO = $pdo->prepare("SELECT cat_nom, plat.* FROM plat JOIN categorie ON pla_cat_id_ce=cat_id");
    $reqParamPDO->execute();
    $menu = $reqParamPDO->fetchAll(PDO::FETCH_GROUP);
    return json_encode($menu);
}

/*
    $platJson contient : 
    {
        "nom": "Nom du plat",
        "detail": "Bla bla bla",
        "prix": 13.95,
        "portion": 1,
        "categorie": 2
    }
*/
function ajouter($pdo, $platJson) {
    $plat = json_decode($platJson);
    $reqParamPDO = $pdo->prepare(
        "
            INSERT INTO plat VALUES 
            (NULL, '{$plat->nom}', '{$plat->detail}', {$plat->portion}, {$plat->prix}, {$plat->categorie} )
        ");
    $reqParamPDO->execute();
    return json_encode(["id" => $pdo->lastInsertId()]);
}


/*
    GET /plats --------------> echo tout($pdo)
    POST /plats -------------> echo ajouter($pdo, $lePlatAAjouter)
*/

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        echo tout($pdo);
        break;
    case 'POST':
        // Récupérer le corps du message HTTP
        $postBody = file_get_contents('php://input');
        echo ajouter($pdo, $postBody);
        break;
    case 'PUT':
        # code...
        break;
    case 'DELETE':
        # code...
        break;
    default:
        # code...
        break;
}