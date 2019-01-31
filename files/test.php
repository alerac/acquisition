<?php session_start() ?>
<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

function GetHeadBody() {
    echo '<!DOCTYPE html>';
    echo '<html>';
    echo '<head>';
    echo '<meta charset="utf-8">';
    echo '<meta http-equiv="x-ua-compatible" content="ie=edge">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
    echo '<title>Acquisition - test</title>';
    echo '<link rel="stylesheet" href="../assets/css/foundation.css">';
    echo '<link rel="stylesheet" href="../assets/css/app.css">';
    echo '<link rel="stylesheet" href="../assets/css/acquisition.css">';
    echo '</head>';
    echo '<body>';
}
function GetBodyFooter() {
    echo '</body></html>';
}

if($_POST['email2']) { 
    header("Location:bot.php");
}

else {

    if(!isset($_POST['reference'])) {
        GetHeadBody();
        $ref_error_message = 'Aucune référence sélectionnée. Veuillez sélectionner au moins une référence';
            $_SESSION['ref_error_message'] = $ref_error_message;
            header("Location:../index.php");
        GetBodyFooter();
    }
    else {
        /*Si au moins une référence est sélectionnée, on vérifie la validité de l'email fourni.
          Si l'email est valide on initialise les données à paramétrer dans le mail.*/

        if (filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)){
                           
                /*On teste et affecte les différents paramètres pour vérifier que le
                  mail pourra partir sans problèmes. S'il y a un problème on affiche
                  un message d'erreur*/
            //print_r($_POST['reference']);
            //print_r($_POST['commentaires']);
            
            GetHeadBody();
            echo '<div class="recap">';
            echo '<h3>Votre commande a bien été envoyée</h3>';
            $count = count($_POST['reference']);
            if ($count == 1) {
               echo '<h4>Récapitulatif de votre commande : il y a '.$count.' référence commandée</h4>'; 
            }
            else {
                echo '<h4>Récapitulatif de votre commande : il y a '.$count.' références commandées</h4>';
            }

            foreach ($_POST['reference'] as $SelectedOption) {
                $ArrayRef = explode('_', $SelectedOption);
                //print_r($ArrayRef);
                foreach ($_POST['commentaires'] as $KeyRef => $comment) {
                    $ArrayComment = [$KeyRef => $comment];
                    //print_r($ArrayComment);
                    $flipped = array_flip($ArrayComment);
                    //print_r($flipped);
                    foreach ($flipped as $FlippedComment => $FlippedKey) {
                        if (in_array($FlippedKey, $ArrayRef)) {
                            array_push($ArrayRef, $FlippedComment);
                            //print_r($ArrayRef);                        
                        }
                    }
                }
                echo $ArrayRef[0].'<br>';
                echo $ArrayRef[1].'<br>';
                echo $ArrayRef[2].'<br>';
                echo $ArrayRef[3].'<br>';
                if ($ArrayRef[4] == "Pas d'URL") {
                    echo $ArrayRef[4].'<br>';
                }
                else {
                    echo '<a href="'.$ArrayRef[4].'">'.$ArrayRef[4].'</a><br>';
                }
                if (!empty($ArrayRef[6])) {
                    echo '<strong>Commentaire:</strong> '.htmlspecialchars($ArrayRef[6]).'<br>';
                    echo '<hr>';
                }
                else {
                    echo '<hr>';
                }
            }
        }
        else {
            GetHeadBody();
            $error_message = htmlspecialchars($_POST['email']).' '.'n\'est pas une adresse email valide';
            $_SESSION['errors'] = $error_message;
            header("Location:../index.php");
            GetBodyFooter();
        }
    }
}

?>
</body>
</html>
