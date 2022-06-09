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

function retirer($pdo, $id) {
    $reqParamPDO = $pdo->prepare("DELETE FROM plat WHERE pla_id=:id");
    $reqParamPDO->execute(['id'=>$id]);
    return json_encode(["nombreEnregistrementsAffectes" => $reqParamPDO->rowCount()]);
}

function remplacer($pdo, $id, $platMod) {
    $plat = json_decode($platMod);
    $reqParamPDO = $pdo->prepare("UPDATE plat SET 
        pla_nom='{$plat->nom}', 
        pla_detail='{$plat->detail}', 
        pla_prix={$plat->prix}, 
        pla_portion={$plat->portion}, 
        pla_cat_id_ce={$plat->categorie}
        WHERE pla_id=:id");
    $reqParamPDO->execute(['id'=>$id]);
    return json_encode(["nombreEnregistrementsAffectes" => $reqParamPDO->rowCount()]);
}

/*
    $changement devrait ressembler à : 
    // Payload du corps du message HTTP
    {
        "pla_prix": 50,
        "pla_detail": "Nouveau détail pour ce plat"
    }
*/
function changer($pdo, $id, $changement) {
    $champsAModifier = json_decode($changement, true);
    $fragmentSql = "";
    // Assainir les noms des colonnes !!!! Sinon, injection de code possible...
    foreach ($champsAModifier as $colonne => $nouvelleValeur) {
        $fragmentSql .= "$colonne=:$colonne,";
    }
    $fragmentSql = rtrim($fragmentSql, ',');
    $reqParamPDO = $pdo->prepare("UPDATE plat SET {$fragmentSql} WHERE pla_id=:id");
    echo "UPDATE plat SET {$fragmentSql} WHERE pla_id=:id";
    // Remarquez l'utilisation du "spread operator"
    $reqParamPDO->execute(['id'=>$id, ...$champsAModifier]);
    return json_encode(["nombreEnregistrementsAffectes" => $reqParamPDO->rowCount()]);
}

/*
    GET /plats --------------> echo tout($pdo)
    POST /plats -------------> echo ajouter($pdo, $lePlatAAjouter)
*/

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        // Exemple URL de la requête : /plats
        echo tout($pdo);
        break;
    case 'POST':
        // Exemple URL de la requête : /plats
        // Récupérer le corps du message HTTP
        $postBody = file_get_contents('php://input');
        echo ajouter($pdo, $postBody);
        break;
    case 'PUT':
        // Exemple URL de la requête : /plats/{idPlat}
        $postBody = file_get_contents('php://input');
        // Remplacer toutes les propriétés de l'entité sauf l'identifiant
        echo remplacer($pdo, 4, $postBody);
        break;
    case 'DELETE':
        // Exemple URL de la requête : /plats/{idPlat}
        echo retirer($pdo, 11);
        break;
    case 'PATCH':
        // Exemple URL de la requête : /plats/{idPlat}
        $postBody = file_get_contents('php://input');
        // Remplacer toutes les propriétés de l'entité sauf l'identifiant
        echo changer($pdo, 1, $postBody);
        break;
    
    default:
        # code...
        break;
}