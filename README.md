Vous voyez ici mon projet de fin d'études. Les consignes sont les suivantes :
- Utiliser les 5 langages suivants : PHP, JS, MySQL, HTML, CSS
- Ne pas utiliser de framework/librairie
- Projet autonome (pas d'équipe)
- Sujet libre
- Aucun accompagnement (il était disponible, je n'en ai pas ressenti le besoin)
- Build from scratch, 100% du contenu, du code, et de l'architecture sont de moi.
- Délai maximum de 2 mois.
 
Je souhaite préciser les choses suivantes :
- A l'origine, je ne pensais pas mettre ce projet sur un repo public. Seule la version beta, et les commits qui la suivent apparaissent ici.
- Le code n'est que très peu commenté pour la même raison.
- Le dossier tests est désespérément absent car il n'était pas au programme de la formation, il est entendu que j'ai exploré cette nécessité depuis.
- Ayant manqué de temps sur la fin, je ne suis que moyennement content de l'optimisation algorithmique et architecturale. Ceci dit, web.dev me donne un score de 99.5 !
- La "statistification" (sans doute le plus interessant) est ici : https://github.com/Mickael-Bidinger/Optimal-Deplete/blob/master/application/services/UpdatingService.php
- Coté front voir https://github.com/Mickael-Bidinger/Optimal-Deplete/blob/master/assets/js/class/Options/Filters/Specialization.js

En un mot :
- Back-end: importation de données (150M+ lignes), déduction de statistiques (500k+ possibilités), rendering SVG
- Front-end: AJAX vers API interne, mise à jour du contenu de la page, diverses mises à jour d'affichage

En détails :
- L'objectif général de cette application est de découvrir l'effet des différents facteurs impactant les donjons mythiques plus de World of Warcraft.
Il fourni donc des statistiques prenant en compte les filtres choisis par l'utilisateur : classes présentes, affixes, donjons, niveau de difficulté ...
Cela permet, par exemple, de voir quelles sont les meilleures classes pour un contenu donné, ou quel donjon est le plus simple avec les affixes de la semaine.

Pour ce projet, outre la programmation, j'ai réalisé les choses suivantes : 
- étude de faisabilité : l'API blizzard fourni effectivement les données nécessaires
- étude de marché : définition, avec l'aide de la communauté, de la qualité de l'idée de base et des détails des fonctionnalités.
- design : soumis à la communauté
- architecture et création de la base de données
- hébergement (choix et déploiement)
- marketing et référencement

Il est également à noter que j'ai créé mon propre framework back, les framework extérieurs étant déconseillés.

Ce qui m'a posé le plus de problème : 
- Le calcul des statistiques. Non pas d'un point de vue algorithmique, mais du point de vue de la réactivité du site. 
En effet, j'ai tout d'abord tenté de calculer les statistiques à la demande (sur 150M de lignes pour rappel...). Ainsi ma toute première version "quick and dirty" mettait plus de 20 secs à répondre. J'ai donc passé un certain temps à optimiser algorithme, architecture, et technologie (mongoDB) de ma base de données, avant de trouver la solution : calculer les statistiques au moment de l'import, avant de les stocker. L'évidence même me direz-vous !

Ce dont je suis le plus fier :
- Découverte et approfondissement de mongoDB, même si je ne l'ai finalement pas utilisé !
- La popularité du site (2000 pages vues par mois en moyenne)
- La réaction de mes professeurs en voyant le projet, je cite : "Je te pique ta mixin fontface", "Je vais me taper un coup de fil des correcteurs, les élèves ne font normalement pas un travail de cette qualité".

Le mot de la fin :
- Leonard de Vinci a dit... "L'art n'est jamais terminé, seulement abandonné."
Je vois le développement de la même manière ! J'aurai souhaité avoir un délai plus long pour optimiser d'avantage.
