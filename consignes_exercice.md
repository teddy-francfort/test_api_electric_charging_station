# Exercice Dev Backend, Appli Laravel, 09/2022

## Description

L'exercice a pour but la création du coté serveur d'une API qui peut recevoir et traiter des CDRs à enregistrer en base de donnée.

## Terminologie

- Un **opérateur** est une société qui possède des points de charges (`evses`) et/ou des utilisateurs (les conducteurs).
- Un **EVSE** (Electric Vehicle Supply Equipment) est un point de charge, c'est-à-dire un emplacement où une voiture peut se recharger. Typiquement une borne a deux points de charges (deux cotés).
- Un **CDR** (Charge Details Record) est un reçu indiquant les infos d'une session de charge une fois terminée, dont sa consommation électrique et son coût. 

Dans les cas d'**interopérabilité**, c'est-à-dire lorsque la société qui fait l'application par laquelle la recharge est lancée (Freshmile, ou Chargemap par exemple), est différente de la société qui possède la borne, alors à la fin de charge, cette dernière va envoyer un CDR à l'autre société, qui elle va facturer l'utilisateur ce qu'elle veut.

Par exemple si un utilisateur Chargemap lance une charge sur une borne Freshmile, Freshmile va alors envoyer un CDR vers Chargemap.

A l'inverse si un utilisateur Freshmile lance une charge sur borne qui est possédée par Izivia, alors c'est Izivia qui va envoyer un CDR vers Freshmile. L'exercice traite (une version ultra simplifiée) de ce cas-là. 

## Description des objets

Pour chacun de ces trois objets, un model est à créer.

Voici la structure de leur table en BDD :

- table `operators`
	- `id unsigned int primary key`
	- `name varchar(100) not null`
	- `access_token varchar(64) not null`

- table `evses`
	- `id unsigned int primary key`
	- `ref varchar(36) not null`
	- `address varchar(45) not null`
	- `operator_id unsigned int not null` avec contrainte de clé étrangère vers `operators.id`

- table `cdrs`
	- `id unsigned int primary key`
	- `ref varchar(36) not null`
	- `start_datetime timestamp not null`
	- `end_datetime timestamp not null`
	- `total_energy int`
	- `total_cost unsigned int`
	- `evse_id unsigned int not null` avec contrainte de clé étrangère vers `evses.id`

Chaque table a les champs classiques `created_at timestamp default current_timestamp`, ainsi que `updated_at`.

Il faudra aussi s'assurer d'avoir les index sur les champs pertinents, à vous de les déterminer en fonctions des requêtes qui sont faites.

Les models doivent comporter :
- les méthodes de relation entre les objets
- tous les champs de timestamps doivent être casté en datetime
- Un PHPDoc pour chacun des attributs (y compris pour les relations), en tenant compte des casts éventuels

Exemple :
```php
/**
 * @property int $id
 * @property Carbon $created_at 
 * @property Operator $operator
 */
final class Evse {
```

## Flow d'une requête et tâches à faire par l'appli

Seules deux routes **d'API** sont accessibles (les routes "web" ne sont pas du tout utilisées) :
- `PUT /ocpi/cdrs`
- `GET /ocpi/cdrs/{ref du cdr}`

Toute autre routes préfixées par `/ocpi` doit retourner 404 avec un body vide.

### Autorisation

Les requêtes doivent avoir un header `Authorization` avec un bearer token, qui doit correspondre à l'un de ceux dans la base de donnée (table opérateur).

Si pas de header ou token inconnu, retourner 401 avec body vide.

Si token connu, le model de l'opérateur devrait facilement être accessible via l'objet de la requête (sera utile dans le controller).

L'autorisation doit se faire via un middleware.

### Logs

Définir un loggeur `ocpi` qui écrit simplement dans un fichier `ocpi.log`.

Pour les requêtes, il doit être loggé un objet JSON contenant
- l'URL
- tous les headers
- tout le body (en string)

Pour les réponses, il doit être loggé un objet JSON contenant :
- le status HTTP
- tous les headers
- tout le body (en string)

Le log doit se faire via un middleware.

## Format des Cdrs

Les cdrs reçus (sur la route PUT, ou envoyés depuis la route GET) sont en JSON et ressemblent à ça :
```json
{
	"id": "{la ref du cdr}",
	"evse_uid": "{la ref de l'evse}",

	"start_datetime": "2020-09-16T00:00:00Z",
	"end_datetime": "2020-09-16T00:00:00Z",

	"total_energy": 12.345,
	"total_cost": "12.34"
}
```

L'énergie (la consommation en électricité de la voiture) est exprimée en float et en Kilo Watt heure (kWh). Pour donner une idée, une Renault Zoe a une batterie de 40 kWh.  
Le coût est exprimé en unité d'€ avec deux chiffres après la virgule (les centimes), sous forme de string.  

Attention en base de donnée, c'est différent les deux sont stocké en nombre entier :
- l'énergie est stockée en Watt heure (Wh)
- le coût est stocké en centimes

La conversion entre les deux formats doit être transparente, c'est-à-dire que les deux attributs sur le model Cdr doivent avoir un "custom cast" qui s'occupe de ça.  

Les dates sont au format `ISO8601 Zulu`.

### Route PUT

Lorsque reçu, le format du body de la requête doit être dûment validé par un form request.

Ensuite, il faut que le controller trouve une correspondance entre la ref de l'evse et un evse en BDD.
Si aucune correspondance n'est trouvée, ou que l'evse n'appartient pas à l'opérateur répondre 404 avec un body vide.

Faire ce qu'il faut pour enregistrer le Cdr (qui peut déjà exister) et répondre 200.

### Route GET

La ref du Cdr est dans l'URL.

Les cdrs doivent être filtrés, c'est-à-dire qu'ils doivent exister et appartenir à l'opérateur qui fait la requête.

Cela doit se faire via une méthode `whereOperatorIs(Operator $operator): Builder` qui accepte un model opérateur en argument et filtre la requête SQL, via les evses.  
Elle est à créer sur un query builder custom, et non en tant que "model scope".

Exemple (pseudo code) :
```php
Cdr::query()
    ->whereOperatorIs($operator)
    ->where('ref', '=', $data['id'])
    ->first();
```

Si le cdr est inconnu ou n'appartient pas à l'opérateur de la requête, retourner 404, sans body.

Sinon, transformer le Cdr à l'aide d'une "API Resource" de Laravel, et faire retourner l'API resource de la méthode du controller.

## Tests

Pas besoin de tester unitairement quoi que ce soit.

Les deux routes, ainsi que les divers cas d'autorisation, doivent par contre être testés (avec PHPUnit, pas Pest).

Cela requiert donc la création d'au moins une classe de test, qui seed des données dans la base de donnée via un seeder et des factories, qui sont à créer. Les models ne doivent **pas** avoir le trait HasFactory.

## Projet

Installer une application Laravel 9 toute fraiche, fonctionnant sur PHP8.1.  
Supprimer des dépendances composer `Laravel Pint` qui ne sert pas.

La BDD peut être MySQ8.0 ou bien SQLite afin de simplifier le setup.    
Il n'y a pas non plus besoin de PHP-FPM, puisque les deux routes sont testées, il suffit que l'appli fonctionne en CLI via les tests.

L'application de l'exercice ne fait que ça avec ces deux routes.  
De nombreux fichiers présents par défaut dans un projet Laravel de base sont alors complètement ou partiellement inutiles.  
Modifiez-les à bon escient *ou supprimez-les*.

Pas besoin toutefois de nettoyer chaque fichier de configurations des clés inutiles.


## Qualité de code

Installer PHPStan dans un dossier `devtools/phpstan`.

Faire en sorte de pouvoir le faire tourner depuis le dossier racine de l'application via la commande `composer phpstan`.

Le configurer pour le faire tourner au niveau le plus haut possible.

Note : dans ma solution, j'ai pu le faire tourner au niveau 9 sans Larastan, et en ignorant seulement les quelques lignes des accès à l'opérateur sur l'objet requête dans le controller.  
Si vous n'arrivez pas à le faire tourner à un niveau aussi élevé, ce n'est pas grave.

Larastan peut être installé, mais si le style du code est respecté, et avec un si petit projet, il est normalement inutile.


## Style

Installer PHP CS Fixer dans un dossier `devtools/php-cs-fixer`.

Le configurer pour utiliser les présets de règle `Symfony` et `Symfony:risky`, sur les dossiers `app`, `config`, `database`, `routes`, et `tests`.

Faire en sorte de pouvoir fixer le code de l'application depuis son dossier racine via la commande `composer php-cs-fixer`.

En plus de ces styles fixés automatiquement, il convient que le code respecte les standards de code mentionnés dans le fichier `standards-de-code.md`. 

Note : si jamais il y a un conflit entre les règles automatiques et celles du fichier, ce n'est pas grave PHP-CS-Fixer a la priorité. 


## Notes

Pour ce que j'appelle les "query builder custom", voir les liens suivants comme la doc officielle de Laravel n'en parle même pas :
- https://codecourse.com/courses/custom-laravel-query-builders
- https://laravelexample.com/custom-eloquent-query-builder-class-local-scopes-class#local-scopes-class


Pour PHPStan et l'analyse statique, voir ça plus comme un bonus un fois que le reste est fait.
Aussi à moins d'être expérimenté avec, résolvez les erreurs qu'il donne niveau par niveau, en commençant au niveau 1.
Atteindre le niveau 9 requiert de s'y connaitre déjà un minimum tant dans PHPStan et Laravel.

Pour les commits, faites en autant que vous voulez, pas besoin de les squash à la fin.  
Faites comme si vous développiez normalement. 
