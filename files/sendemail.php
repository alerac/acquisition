<?php session_start() ?>
<?php

/*Désactiver ces deux lignes en production pour des raisons de sécurité
affiche les erreurs PHP indépendemment des réglages du serveur

error_reporting(E_ALL);
ini_set('display_errors', 1);*/

//On importe le module PHPmailer pour l'envoi du mail.
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
 
require_once '../assets/phpmailer/vendor/autoload.php';
 
/*On initialise la variable $mail pour y affecter les paramètres plus tard
  et préparer l'envoi*/

$mail = new PHPMailer(true);

/*On déclare les fonctions qui permettront d'appeler les en-têtes <html></html>
  et <body></body> dans les différents cas de figure.*/

function GetHeadBody() {
    echo '<!DOCTYPE html>';
    echo '<html>';
    echo '<head>';
    echo '<meta charset="utf-8">';
    echo '<meta http-equiv="x-ua-compatible" content="ie=edge">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
    echo '<title>Acquisition - email</title>';
    echo '<link rel="stylesheet" href="../assets/css/foundation.css">';
    echo '<link rel="stylesheet" href="../assets/css/app.css">';
    echo '<link rel="stylesheet" href="../assets/css/acquisition.css">';
    echo '</head>';
    echo '<body>';
}
function GetBodyFooter() {
    echo '</body></html>';
}

/*Si un spambot a rempli le "honeypot" on n'envoie pas le mail
  et on le renvoie à la page "bot.php"*/

if($_POST['email2']) { 
    header("Location:bot.php");
}

else {
    
    /*Si aucune référence n'est sélectionnée on initialise le message d'erreur
      qui s'affichera sur la page principale du formulaire*/

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

            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.exemple.com';  //serveur mail SMTP (à adapter)
                $mail->SMTPAuth = true;
                $mail->Username = 'Compte utilisateur mail';   //utilisateur (à adapter)
                $mail->Password = 'Mot de passe utilisateur mail';   //Mot de passe (à adapter)
                $mail->SMTPSecure = 'ssl'; //On force l'envoi en ssl
                $mail->Port = 465;                    //port d'envoi (à adapter)
                $mail->CharSet = "text/html; charset=UTF-8;"; //encodage du mail en UTF-8
             
                $mail->setFrom('noreply@êxemple.com', 'Votre nom');
                $mail->addAddress('destinataire@exemple.com', 'Nom du destinataire'); //adresse mail à laquelle envoyer le mail
             
                //Décommenter et adapter le chemin si on souhaite ajouter une pièce jointe au mail

                #$mail->addAttachment(__DIR__ . '/attachment1.png');
                #$mail->addAttachment(__DIR__ . '/attachment2.jpg');
             
                //On déclare le mail comme étant du html

                $mail->isHTML(true);

                /*On prépare le corps du message à envoyer.
                  On déclare le sujet du mail. Ici directement encodé en UTF-8 pour régler
                  des problèmes d'encodage lors des tests effectués. Pas forcément valide dans
                  tous les cas, à adapter si nécessaire. Message : "Nouvelle commande reçue de
                  la part de ...". On affiche les variables entrées par l'utilisateur avec le
                  htmlspecialchars pour éviter les injections de code*/

                $mail->Subject = '=?UTF-8?B?Tm91dmVsbGUgY29tbWFuZGUgcmXDp3VlIGRlIGxhIHBhcnQgZGU=?= '.htmlspecialchars($_POST['nom']).' '.htmlspecialchars($_POST['prenom']);

                $message = '<p>Voici la liste des références sélectionnées par :</p>';
                $message .= '<p>'.htmlspecialchars($_POST['nom']).' '.htmlspecialchars($_POST['prenom']).'<br />'.htmlspecialchars($_POST['email']).'<br></p>';

                /*Pour chaque référence sélectionnée, on sépare les valeurs grâce au "_"
                  paramétré dans le fichier principal du formulaire.*/

                foreach ($_POST['reference'] as $SelectedOption) {

                    $ArrayRef = explode('_', $SelectedOption);

                    /*Pour chaque commentaire ajouté à la sélection, on inverse
                      la paire "clé-valeur" du commentaire en question 
                      (cf. marqueur paramétré dans le fichier principal du formulaire).
                      Si la valeur (marqueur) se retrouve dans les valeurs de la référence
                      on pousse le commentaire dans les valeurs de la référence*/

                    foreach ($_POST['commentaires'] as $KeyRef => $comment) {
                        $ArrayComment = [$KeyRef => $comment];
                        $flipped = array_flip($ArrayComment);
                        foreach ($flipped as $FlippedComment => $FlippedKey) {
                            if (in_array($FlippedKey, $ArrayRef)) {
                                array_push($ArrayRef, $FlippedComment);
                            }
                        }
                    }

                    /*On insère ensuite les différentes valeurs souhaitées de chaque référence
                      sélectionnée dans le corps du message*/

                    $message .= $ArrayRef[0].'<br>';
                    $message .= $ArrayRef[1].'<br>';
                    $message .= $ArrayRef[2].'<br>';
                    $message .= $ArrayRef[3].'<br>';

                    //Si l'URL n'est pas présente on ne l'affiche pas sous forme de lien

                    if ($ArrayRef[4] == "Pas d'URL") {
                        $message.= $ArrayRef[4].'<br>';
                    }
                    else {
                        $message.= '<a href="'.$ArrayRef[4].'">'.$ArrayRef[4].'</a><br>';
                    }
                    
                    //Si le commentaire a été rempli on l'affiche.

                    if (!empty($ArrayRef[6])) {
                        $message.= '<strong>Commentaire:</strong> '.htmlspecialchars($ArrayRef[6]).'<br>';
                        $message.= '<hr>';
                    }
                    else {
                        $message.= '<hr>';
                    }

                }

                //On déclare le coprs du message

                $mail->Body = $message;
             
                /*Si le mail n'a pas pu être envoyé on affiche un message d'erreur.
                  Sinon on affiche un récapitulatif de la commande.*/

                if (!$mail->send()) {
                    GetHeadBody();
                    echo 'Le message n\'a pas pu être envoyé.';
                    echo 'Mailer Error: ' . $mail->ErrorInfo;
                    GetBodyFooter();
                } else {
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

                    /*On reprend le code de l'affichage avec commentaire pour
                      le récapitulatif*/

                    foreach ($_POST['reference'] as $SelectedOption) {
                        $ArrayRef = explode('_', $SelectedOption);
                        foreach ($_POST['commentaires'] as $KeyRef => $comment) {
                            $ArrayComment = [$KeyRef => $comment];
                            $flipped = array_flip($ArrayComment);
                            foreach ($flipped as $FlippedComment => $FlippedKey) {
                                if (in_array($FlippedKey, $ArrayRef)) {
                                    array_push($ArrayRef, $FlippedComment);
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
                    echo '</div>';
                    GetBodyFooter();
                }

                /*On affiche un message si des erreurs ont été rencontrées
                  lors du test des paramètres du mail*/

            } catch (Exception $e) {
                GetHeadBody();
                echo 'Le message n\'a pas pu être envoyé.';
                echo 'Mailer Error: ' . $mail->ErrorInfo;
                GetBodyFooter();
            }
        }

        /*Si le mail n'est pas valide on renvoie l'utilisateur à la page
          principale du formulaire pour afficher le message d'erreur*/

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
