# Génération d'un formulaire liste d'acquisition pour une bibliothèque

Test de formulaire PHP pour générer une liste d'acquisition pour une bibliothèque
à partir d'un export CSV d'une collection Zotero.

Basé sur un processus de veille acquisition mensuelle.

Idée originale **Elodie Schwob**  
Réalisation du script **Alexandre Racine**

## Auteur

* **Alexandre Racine**  
<https://alex-racine.ch>  
<https://libracine.ch>

## Contributeur

* **Elodie Schwob**  
<https://ch.linkedin.com/in/elodieschwob>

## Licence

(CC BY-SA 3.0) <https://creativecommons.org/licenses/by-sa/3.0/deed.fr>

## Fonctionnement

### index.php 

  prend en entrée le fichier CSV **collection_exemple.csv** présent dans le dossier **files** et exporté depuis Zotero.
  Il prend les données de la ligne de référence et génère ensuite un formulaire avec check box à cocher pour 
  les références souhaitées.

  Les lignes **error_reporting(E_ALL);** et **ini_set('display_errors', 1);** servent à afficher les erreurs PHP
  dans le navigateur pour débugger. A enlever absolument une fois le script terminé et en production pour des
  raisons de sécurité.

### Dossier "assets" 

  Comprend tous les éléments CSS, JS, Mail et images nécessaires

#### phpmailer

Module nécessaire pour l'envoi du mail. Normalement déjà préinstallé dans le cadre de ce projet. En cas de problème, si une réinstallation est nécessaire, se référer à <https://github.com/PHPMailer/PHPMailer/blob/master/README.md> 

#### CSS

Fichiers CSS du framework foundation <https://foundation.zurb.com/> + CSS local **acquisition.css**

#### JS

Comprend le code **Javascript** du framework foundation.

#### images

Jolie image vers laquelle sont redirigés les spams bots s'ils remplissent le *[honeypot](https://www.smartfile.com/blog/captchas-dont-work-how-to-trick-spam-bots-with-a-smarter-honey-pot/)*
  

### Dossier "files"

Comprend les fichiers du formulaire

#### collection_exemple.csv

Collection test exportée depuis Zotero en format CSV. Contient toutes les références ligne par ligne.  

Cet export est fait grâce à un export CSV custom (**fichier CSV_custom.js**) à placer dans le dossier **translators** du répertoire utilisateur de [Zotero](https://www.zotero.org/).  

Pour plus d'information à ce sujet voir <https://forums.zotero.org/discussion/41927/export-collection-in-csv-format-how-to-get-rid-of-superfluous-columns>  

Le fichier CSV ne contient ainsi plus que 5 colonnes :  

* Auteur
* Titre
* Éditeur
* ISBN
* URL de l'éditeur

#### sendemail.php

Le script PHP qui se charge de traiter les données et d'envoyer le mail.

#### test.php

Le script PHP qui permet de tester le formulaire sans envoyer le mail.

#### bot.php

Le script PHP qui redirige les spam bots s'ils ont rempli le *[honeypot](https://www.smartfile.com/blog/captchas-dont-work-how-to-trick-spam-bots-with-a-smarter-honey-pot/)*

### Prérequis

En local ou en production, nécessite un environnement web prenant en charge une version récente de PHP. Pour mettre en place un environnement de développement web simplement et rapidement voir [XAMPP](https://www.apachefriends.org/index.html).

### Visualisation des résultats

* Mettre les fichiers à la racine du serveur
* Pointer sur l'adresse url par exemple **http://localhost/index.php**