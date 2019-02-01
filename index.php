<?php session_start() ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acquisition</title>
    <link rel="stylesheet" href="assets/css/foundation.css">
    <link rel="stylesheet" href="assets/css/app.css">
    <link rel="stylesheet" href="assets/css/acquisition.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
 </head>
<body>
<?php

/*Désactiver ces deux lignes en production pour des raisons de sécurité
affiche les erreurs PHP indépendemment des réglages du serveur

error_reporting(E_ALL);
ini_set('display_errors', 1);*/

//On ouvre le fichier CSV en lecture et on compte les lignes. On retire les en-têtes de colonnes du compte.

$file = fopen('files/collection_exemple.csv', 'r');
$count = count(file('files/collection_exemple.csv'));
$count = $count -1;

/*On initialise le formulaire avec l'envoi des résultats par mail
  Redirige vers la page sendemail.php dans le même dossier*/

echo '<form action="files/test.php" method="post">';

echo '<p>Merci de rentrer vos coordonnées</p>';
echo '<p>Nom * : <input type="text" name="nom" value="" placeholder="Nom (obligatoire)" required></p>';
echo '<p>Prénom * : <input type="text" name="prenom" value="" placeholder="Prénom (obligatoire)" required></p>';
echo '<p>Email * : <input type="text" name="email" value="" placeholder="Email (obligatoire) Ex: marcel@proust.ch" required></p>';

//On initialise un "honeypot" pour les spammers

echo '<p class="bot" hidden><input type="text" name="email2">';

/*Si l'adresse e-mail n'est pas valide on affiche
  un message d'erreur et on désactive la session
  pour que le message ne reste pas affiché à la 
  prochaine session*/

if(isset($_SESSION['errors'])){
	echo '<div class="errors">*'.$_SESSION['errors'].'</div>';
	unset ($_SESSION['errors']);
}
echo '<hr />';

/*Si aucune référence n'est sélectionnée on affiche
  un message d'erreur et on désactive la session
  pour que le message ne reste pas affiché à la 
  prochaine session*/

if(isset($_SESSION['ref_error_message'])){
	echo '<div class="errors">*'.$_SESSION['ref_error_message'].'</div>';
	unset ($_SESSION['ref_error_message']);
}

//On affiche le compte des références affichées.

echo '<p>Il y a '.$count.' références disponibles. Merci de sélectionner celles que vous souhaitez commander :</p>';

/*On ouvre une balise <fieldset> qui servira à encapsuler la checkbox "checkall" et les autres checkboxes.
  Permet d'activer un bouton "Tout sélectionner"*/

echo '<fieldset>';
echo '<ul>';
echo '<li>';
echo '<label class="button"><input type="checkbox" class="checkall"> Tout sélectionner / déselectionner</label>';
echo '</li>';
echo '<li>';
echo '<div class="gobottom">';
echo '<div class="button"><a href="#bottom">Aller à la fin du formulaire</a></div>';
echo '</li>';
echo '</ul>';
echo '</div>';

/*On initialise un marqueur qui permettra de taguer les commentaires éventuels
  pour les assigner par la suite à la bonne référence*/

$refnumber = 0;

/*$flag = true; et if($flag) { $flag = false; continue; } pour ne pas prendre
en compte les en-têtes de colonnes*/

$flag = true;
while (($line = fgetcsv($file)) !== FALSE) {
  
  //$line est un "array" des éléments de la ligne
  //print_r($line);
  //si nécessaire on visualise l'array pour obtenir les indexes correctes des données à traiter
	
	/*On prend en compte les données du fichier CSV qui pourraient
	  être manquantes*/

	if($flag) { $flag = false; continue; }

	if (empty($line[0])) {
		$line[0] = "Pas d'auteur";
	}
	if (empty($line[1])) {
		$line[1] = "Pas de titre";
	}
	if (empty($line[2])) {
		$line[2] = "Pas d'éditeur";
	}
	if (empty($line[3])) {
		$line[3] = "Pas d'ISBN";
	}
	if (empty($line[4])) {
		$line[4] = "Pas d'URL";
	}

	/*On incrémente le marqueur pour en assigner un unique à chaque
	  commentaire*/

	++$refnumber;
	$KeyRefNumber = 'reference'.$refnumber;
	
	/*On affiche les champs du formulaire pré-remplis avec les valeurs
	  pour chaque référence.*/

	echo '<div class="ref">';
	echo '<p>Titre : <input type="text" name="titre" value="'.$line[1].'" readonly></p>';
	echo '<p>Auteur : <input type="text" name="auteur" value="'.$line[0].'" readonly></p>';
	echo '<p>Éditeur : <input type="text" name="editeur" value="'.$line[2].'" readonly></p>';
	echo '<p hidden>ISBN : <input type="text" name="isbn" value="'.$line[3].'" readonly></p>';
	echo '<p hidden>URL : <input type="url" name="url" value="'.$line[4].'" readonly></p>';
	
	//Si l'URL n'est pas présente on ne l'affiche pas sous forme de lien

	if ($line[4] == "Pas d'URL") {
		echo '<p>'.$line[4].'</p>';
	}
	else {
		echo '<p><a href="'.$line[4].'" target="_blank">'.$line[4].'</a></p>';
	}

	/*On ajoute un champ de commentaire libre et on le tague avec
	  le marqueur prédéfini. On le nomme commentaires[] (avec crochets carrés)
	  pour indiquer que le commentaire se trouvera dans un array. Permet de
	  traiter chaque commentaire individuellement dans le fichier sendemail.php*/

	echo '<p>Commentaires : <input type="text" name="commentaires['.$KeyRefNumber.']" value=""></p>';

	/*On ajoute une checkbox qui permettra de sélectionner l'ensemble
	  des valeurs désirées. On la nomme reference[] (avec crochets carrés)
	  pour indiquer que la référence se trouvera dans un array. Permet de
	  traiter chaque référence individuellement dans le fichier sendemail.php
	  On tague la référence avec le marqueur défini (correspond au commentaire)*/

	echo '<label class="button"><input type="checkbox" name="reference[]" value="'.$line[1].'_'.$line[0].'_'.$line[2].'_'.$line[3].'_'.$line[4].'_'.$KeyRefNumber.'" /> Sélectionner</label>';
	echo '</div>';
	echo '<hr>';
}

/*On initialise une <div> qui va contenir le bouton pour envoyer le formulaire
  et un deuxième bouton "Sélectionner tout" pour sélectionner ou déselectionner 
  toutes les références au fond du formulaire. Le bouton "Envoyer" transfert les
  données dans le fichier sendemail.php*/

echo '<div id="bottom" class="bottom">';
echo '<br />';
echo '<ul>';
echo '<li>';
echo '<div class="SelectAllBottom"><label class="button"><input type="checkbox" class="checkall"> Tout sélectionner / déselectionner</label></div>';
echo '</li>';
echo '<li>';
echo '<input class="button" type="submit">';
echo '</li>';
echo '</ul>';
echo '</div>';
echo '</fieldset>';
echo '</form>';

/*On oublie pas de fermer le fichier CSV.
On utilise le code Javascript adéquat pour 
sélectionner ou déselectionner toutes les références
en une fois*/

fclose($file);

?>

<script>
$(document).ready(function(){
        $('.checkall').click(function () {
            $(this).parents('fieldset:eq(0)').find(':checkbox').attr('checked', this.checked);
        });
});
</script>

</body>
</html>
