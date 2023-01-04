# Standards pour le style du code PHP

Freshmile standardise strictement via [PHP CS Fixer](https://cs.symfony.com) le style du code PHP.

Le style est dérivé de [celui de Symfony](https://symfony.com/doc/current/contributing/code/standards.html), donc largement de la [PSR-12](https://www.php-fig.org/psr/psr-12/).

Il est inutile de faire la liste des règles suivies, car elles sont trop nombreuses, sont très naturelles si déjà habitué à du code PSR-12-like, et que tout l’intérêt est justement de ne pas y penser.

## Automatisation

En plus d'appliquer ces standards naturellement lorsque vous codez, il est nécessaire d'automatiser leur vérification et application.

Ils sont également vérifiés dans la pipeline de CI de Gitlab.

Composer expose une commande pour directement modifier le code : `composer cs-fix` (ou `composer run cs-fix` qui reviens au même).

## Style étendu

Malheureusement, il n'est pas possible pour plein de raisons d'appliquer automatiquement toutes les règles que l'on souhaite pourtant observer.

Ainsi, en plus de toutes les règles appliquées automatiquement par PHP CS Fixer, il convient d'observer des règles additionnelles qui concernent surtout notre interaction avec le système de typage de PHP ainsi qu'avec Laravel.

Ces règles, bien que n'étant pas appliquées automatiquement, **ne sont pas optionnelles**.  
Mais il peut toujours y avoir des exceptions, si justifiées.

### 1) Divers

#### 1.1)

Les assignements à l'intérieur de structures de contrôle (comme les `if()`) sont interdits.  
La seule exception étant l'exemple classique de lecture d'un fichier, par exemple avec `fgets()` dans une boucle while: `while (($line = fgets($handle)) !== false) {`.

#### 1.2)

- Déterminer si une variable est nulle se fait exclusivement avec l'opérateur `===` / `!==`, et non avec la méthode `is_null()`, ou encore moins sans opérateur `if ($maVarEstPasNulle)`.
- Déterminer si une variable est un string vide se fait exclusivement en la comparant avec un string vide `=== ''` / `!== ''`, et non en utilisant `empty()` ou `strlen()` ou sans opérateur
- Déterminer si une variable est un array vide se fait exclusivement en la comparant avec un array vide `=== []` / `!== []`, et non en utilisant `empty()` ou `count()`

#### 1.3)

Pour vérifier si une clé existe dans un array, toujours utiliser `isset()` plutôt que `array_key_exists()` à moins d'en avoir vraiment besoin (si l'une des valeur peut être nulle et que l'on veut que ça retourne `true` même dans ce cas).

#### 1.4)

Lorsque l'on passe une closure aux méthodes des query builder, qui va donc recevoir l'instance du query builder en argument, l'argument doit être nommé `$qb`, et typehinté contre `\Illuminate\Database\Eloquent\Builder` ou son plus lointain enfant pertinent. Le typehint de retour doit être `void`. Exemple: `->where(function (Builder $qb): void {`.

#### 1.5)

L'utilisation d'une référence (avec l'opérateur `&`) est à proscrire le plus possible.  
Si son usage ne peut être évité doit faire l'objet d'un commentaire alertant sur la présence de la référence. Exemple: `// /!\ $foobar is a REFERENCE /!\`.  
L'esperluette doit être suivie d'un espace.  
Lorsque la référence est la valeur courante dans une boucle `foreach` (ce qui permet de modifier les valeurs d'un array lorsque l'on boucle dessus par exemple), la variable **doit** être détruite avec `unset()` immédiatement après la boucle ([comme le précise la documentation](https://www.php.net/manual/en/control-structures.foreach.php)).

#### 1.6)

Les conditions avec les booléens ne doivent pas comparer explicitement avec `true` ou `false`, mais ne pas utiliser d'opérateur du tout ou uniquement l'opérateur `!`.  
Bien: `if ($amIABoolean) {`, `if (! $amIABoolean) {`.  
Pas bien: `if ($amIABoolean === true) {`, `if ($amIABoolean !== false) {`, etc...

A l'exception des cas où l'on veut faire la différence entre `true`/`false` et une autre valeur "vraie" ou "fausse".  
Exemple typique avec `strpos` : `if (strpos('needle', $haystack) === false) {`. Ici `if (! strpos('needle', $haystack)) {` retournerai vrai lorsque needle n'existe pas ou bien lorsqu'il existe en début de chaine (à l'index 0).

#### 1.7)

Les méthodes cibles des routes dans les controllers doivent avoir un PHPDoc qui mentionne la méthode HTTP et l'URI.

Exemple :
```php
/**
 * Route: POST /api/enduser/user/tokens/{token_uid}/rename
 */
public function rename(Token $token): JsonResponse
{
    //
}
```

Cette règle s'applique aussi pour les méthodes de test, qui teste la route, cf règle 3.5.


#### 1.8)

Les règles de validation doivent toujours être définies dans un array, et non en tant que string séparé par des pipes.

Exemple :
```php
$this->request->validate([
    // bien
    'email_preferences' => ['nullable', 'array'],

    // pas bien
    'email_preferences' => 'nullable|array',
]);
```

#### 1.9)

Ne pas utiliser de *fonctions globales*, autres que celles de PHP.  
Toujours utiliser à la place leur équivalent en facade ou directement sur le service.

Seules exceptions :
- `env()` (dans les fichiers de config)
- les fonctions pour le debug (`dd()`, `dump()`, ...)

#### 1.10)

Les commentaires doivent commencer par `//` (suivit obligatoirement d'un espace), `/*` ou `/**`, mais jamais par `#`.

#### 1.11)

La méthode `where()` du query builder doit toujours être utilisée avec trois arguments, l'opérateur n'est jamais optionnel, même lorsqu'il est `'='`.

```php
// bien
User::query()->where('is_anonymous', '=', false)->...

// pas bien
User::query()->where('is_anonymous', false)->...
```

#### 1.12)

L'utilisation de l'assignation en masse des attributs des models, via le constructeur ou la méthode `fill()` est *découragée*.  
Il est préféré à la place de set les attributs expressément un par un sur l'instance, car cela permet d'avoir l'analyse statique sur les types et l'existence des attributs.

Exemple :
```php
// bien 
$user = new User();
$user->email = $email;
$user->firstname = $firstname;
$user->time_zone = 'Europe/Paris';

// pas bien
$user = new User([
    'email' => $email,
    'firstname' => $firstname,
    'time_zone' => 'Europe/Paris',
]);
```

#### 1.13)

PHP8.0 a introduit [les propriétés readonly](https://www.php.net/manual/en/language.oop5.properties.php#language.oop5.properties.readonly-properties), qui permet d'empêcher toutes modifications hors du constructeur et après qu'elles aient été définies pour la première fois.

Lorsque possible, toute propriété devrait être marquée readonly.

#### 1.14)

PHP8.0 a introduit [les propriétés promues](https://stitcher.io/blog/constructor-promotion-in-php-8), qui permet de déclarer des propriétés directement à la place des argument du constructeur.
Ainsi en une seule ligne on déclare une propriété (bien sur typée et si possible readonly), on déclare un argument au constructeur et la valeur passée à l'argument s'assigne toute seule à la propriété.

Lorsque possible, il convient de toujours déclarer une propriété promue.

Lorsqu'un constructeur dispose d'une propriété promue, tous les arguments **doivent** être sur leur propre ligne, même si il n'y a qu'une seule propriété.

Exemple:
```php
final class CustomerDTO
{
    public function __construct(
        public readonly string $name, 
    ) {
    }

    /*
    Notez aussi le stye des accolade lorsque le corps du constructeur est vide.

    C'est
    ) {
    }

    et non
    ) 
    {
    }

    ou 
    ) {
        
    }
    */
}
```

### 2) Typage / Analyse statique

Bien qu'étant en PHP, nous visons d'être le plus strictement typé possible.

PHP lui-même et Laravel en particuliers rendent cela parfois un brin compliqué, mais ça n'est pas une raison pour ne pas essayer.

La possibilité de connaitre le type et de rendre l'analyse statique du projet possible une **prime-directive** de tous développeurs backend de Freshmile.

Un code un peu plus long et "compliqué" à lire, mais qui est statiquement analysable est préférable à du code court mais qui ne le serait pas.

**Concrètement, une règle générale est que tout ce qui n'est pas statiquement analysable est interdit.**


#### 2.1)

Les arguments, type de retour de méthodes et type de propriétés **doivent** avoir des typehint lorsque possible.  
Les designs ne permettant pas d'utiliser de typehint parce que plusieurs types sont acceptés/retournés sont donc *découragés*.

Mieux vaut mettre un typehint "large" comme `object`, voir `mixed` que pas de typehint du tout.

Les union types introduits en PHP8.0 sont autorisés mais globalement découragés.

Le type `mixed` est autorisé, mais découragé. Les union types sont préférés au type `mixed`.

Lorsque un typehint fait référence à une classe ou interface, ne pas utiliser le FQCN, mais le nom de base, ou un alias. Exemple : `function test(MaClass $class)` au lieu de `function test(\Mon\Namespace\MaClass $class)`.  
La même règle s'applique aux PHPDocs.

Les propriétés des classes doivent également être typées.

#### 2.2)

Lors de la définition d'une suite de types possibles pour un argument ou une valeur de retour, que ce soit dans un typehint ou un PHPDoc, `null` doit toujours apparaitre en premier, et le reste doit globalement être en ordre alphabétique et/ou de longueur de type. Exemple `null|int|string|\Foo\Bar`.

Typiquement donc null vient en premier, puis les typehints fournis par PHP par ordre alphabétique, puis les classes.  
*Cette règle n'est pas un encouragement à mixer ainsi autant de type différents.*

#### 2.3)

La première règle (2.1) s'applique également aux fonctions anonymes, standards ou courtes, avec l'unique exception suivante : le type de retour des closures courtes peut être omis dans les situations où il n'est pas significatif, c'est à dire qu'il est ignoré par le code qui utilise la closure.

Voir exemple au point 2.4 ci-dessous.


#### 2.4)

Appeler le moins possible de méthodes *via* des méthodes magiques :

Sur les models, appeler la méthode statique `query()` qui retourne le query builder au lieu d'appeler les méthodes du query builder statiquement sur le model.
Ecrire : `User::query()->where(...)` plutôt que `User::where(...)`.

Sur les relations, si besoin d'utiliser l'une des méthodes de nos query builder custom (`App\Database\Builder` ou l'un de ses enfants) ne pas appeler ces méthodes directement sur l'instance de la relation, et ne pas non plus appeler la méthode `getQuery()`, qui retourne le query builder.  
Cela n'est pas statiquement analysable par défaut (l'instance de la relation ne sais pas quelle instance de query builder elle décore :( ) et la magie nécessaire avec des PHPDocs pour rendre ça analysable est interdite.

A la place, passer une closure à la méthode `tap()`, qui recevra alors l'instance du query builder, mais retournera l'instance de la relation. L'idée étant d'appeler les méthodes `first()`/`get()` sur l'instance de la relation (lorsqu'elles existent) au lieu d'appeler celle du query builder, ce qui peut provoquer des comportements non souhaités (voir pulp#384) pour les relations qui override ces méthodes.  
Les méthodes présentes sur le query builder d'Eloquent ou celui de base peuvent être appelées directement sur l'instance de la relation.

Exemple :
```php
$user->sessions()
	->tap(fn (SessionQueryBuilder $qb) => $qb->whereActive()) // notez que ici le type de retour, bien que étant SessionQueryBuilder, peut être omis puisqu'il est ignoré par la méthode tap()
	->get();

$user->reservations()
	->tap(function (Builder $qb): void {
		$qb
			->whereActive()
			->mostRecentFirst('end_at');
	})
	->with('someOtherRelation')
	->first();
```

#### 2.5)

Ne pas créer/ajouter de macros. Ne pas utiliser de macros qui ne sont pas analysables statiquement par manque de PHPDoc.

#### 2.6)

Lorsqu'un service doit être mis en queue, il doit être expressément instancié, puis seulement passé à la méthode dispatch du Bus (`\Illuminate\Contracts\Bus\Dispatcher` ou sa facade).  
La méthode statique `dispatch()` accessible sur les classes ayant le trait `Dispatchable` ne doit pas être utilisée.

Exemple:
```php
$service = new MyJob($someModel);

$bus->dispatch($service);
// ou éventuellement via la facade: Bus::dispatch($service);

// au lieu de 
MyJob::dispatch($someModel);
```

#### 2.7)

Toujours faire des comparaisons strictes, avec les opérateurs `===`/`!==`. Les exceptions doivent être justifiées d'un commentaire.  
Cette règle s’étend aux tests où il est préférable d'utiliser `assertSame()` plutôt que `assertEquals()`, sauf parfois en cas de comparaison d'array.

#### 2.8)

Tous **nouveaux** classes ou traits doivent avoir la ligne `declare(strict_types=1);` tout en haut entre le tag PHP et le namespace, séparé par un saut de ligne.

Pour plus d'information sur son effet, cf : https://www.php.net/manual/en/functions.arguments.php#functions.arguments.type-declaration.strict.

A l'occasion d'une modification, toutes classes **de tests** doivent se voir ajouter cette ligne.

Mais attention, **ne PAS rajouter cette ligne dans les fichiers existants en dehors des tests**, à moins que son code soit très bien testé, car il y a alors trop de risque d'erreur au runtime.

#### 2.9)

Les getters/setters des attributs de model doivent avoir la visibilité `protected` (et non `private` comme ils sont accédés depuis une classe parent).

#### 2.10)

Les nouvelles classes doivent avoir la visibilité `final` par défaut, a moins d'être `abstract` ou conçues à dessin pour être étendue.

Les nouvelles classes qui ont des enfants devraient être marquées abstraites.

Lorsqu'une classe a des enfants mais ne peut être marquée abstraite car elle-même instanciée, elle doit être expressément marquée comme "étendue" afin qu'on ne se pose pas la question de savoir si on a juste oublié le mot-clé ou pas.  
Comme il n'y a pas de mot clés pour cela dans PHP, on rajoute devant le nom de la class, entouré de commentaires le mot clé `extended`.

Exemple:
```php
abstract class A
{

}

/* extended */ class B extends A
{

}

final class C extends B
{

}
```

Les méthodes, propriétés et constantes de ces classes doivent être `private` par défaut a moins d'avoir une bonne raison d'être `protected` ou `public`.

#### 2.11)

Lorsque la variable/propriété/argument est typé contre un array, il est obligatoire d'indiquer via un PHPDoc le contenu de l'array, au minimum des clés et valeurs de premier niveau.

La notation utilisant les chevrons et mentionnant toujours `array` est à utiliser à la place de celle utilisant les crochets (`array<string>` au lieu de `string[]`).

Exemples:
```php
/**
 * @param array<string> $arrayOfString
 * @param array<string, int> $assocArray
 */
```

#### 2.12)

L'utilisation des "[higher order messages](https://laravel.com/docs/8.x/collections#higher-order-messages)" (collection proxy) est interdite.


#### 2.13)

PHPStan n'aime pas le type `mixed`, car comme il peut être n'importe quoi, on ne sais pas quoi en faire, donc on ne peut rien en faire...

Autant que possible, ne pas interagir avec des variables `mixed`, ne pas créer d'arguments, de types de retours mixed.  
Utiliser autant que possible des méthodes notamment qui retourne un seul type.

### Tests

#### 3.1)

Le nom des méthodes de test doivent être en camelCase, commencer par `test`, ne pas utiliser le phpdoc `@test`, avoir le typehint `: void`.

#### 3.2)

Les méthodes d'assertion de PHPUnit sont statiques. Même si elles peuvent être appelées comme des méthodes d'instance, elles ne doivent pas l'être.  
Exemple :
```php
public function test_that_something_is_working(): void
{
	self::asertTrue(true);
	// au lieu de
	$this->assertTrue(true);
}
```
**Astuce** : Si l'autocompletion de votre IDE (par exemple PHPStorm) vous affiche/utilise plutôt la version non-statique, lui faire ignorer le fichier `vendor/phpunit/phpunit/src/Framework/Assert/Functions.php`.

#### 3.3)

L'argument de la méthode `seed()` dans les tests doit être le nom de classe du seeder, et non son nom en string. Exemple: `$this->seed(\App\Database\Seeder\ModelsTestsSeeder::class);` (ou plutôt `$this->seed(ModelsTestsSeeder::class);`, cf règles 2.1 et 4.3) au lieu de `$this->seed('ModelsTestsSeeder');`.

#### 3.4)

Chaque méthode de test devrait être clairement séparé en trois sections `Arrange`, `Act` et `Assert` identifiées par un commentaire les précédents, particulièrement lorsque les sections sont relativement longues.

Exemple :
```php
// \Tests\Controllers\Supervision\CallEmergencyControllerTest::test_resolve
public function test_resolve(): void
{
    // arrange
    $call = CallFactory::new()->create([
        'emsp_id' => Operator::getFreshmileEmsp()->id,
        'supervisor_id' => $this->hotliner->id,
        'solution' => 'en attente de résolution',
        'is_resolved' => false,
    ]);
    CallEmergencyFactory::new()->create([
        'call_id' => $call->id,
        'status' => EmergencyStatus::OPEN,
        'closed_at' => null,
        'other_contacts' => [
            ['email' => 'test@example.fr'],
        ],
    ]);

    Notification::fake();

    // act
    $response = $this->actingAs($this->hotliner)
        ->putJson("/calls/$call->id/resolved?cancelled=0", [
            'solution' => 'la solution',
        ]);

    // assert
    $response->assertOk();
    $response->assertJson(['success' => true]);

    Notification::assertNothingSent(); // because no notification when resolving

    $call->refresh();
    self::assertTrue($call->is_resolved);
    self::assertSame('la solution', $call->solution);

    self::assertSame(EmergencyStatus::CLOSED, $call->emergency->status);
    self::assertNotNull($call->emergency->closed_at);
}
```

#### 3.5)

La règle 1.7) s'applique aussi sur les méthodes de test, qui testent les routes des controller.

```php
/**
 * Route POST /api/enduser/user/book-location
 */
public function test_enduser_can_book_a_location(): void
{
    //
}
```

Si le test n'est pas à un endroit logique, comme dans une classe de test nommée `SomethingControllerTest`, le PHPDoc `@see` devrait être pointé vers la méthode du controller effectivement testée.  
L'idée est de pouvoir facilement retrouver où se trouve le test à partir de la méthode.

```php
/**
 * Route: POST /api/enduser/user/book-location
 *
 * @see \Pulp\Http\Enduser\Controllers\UserController::bookLocation
 */
public function test_enduser_can_book_a_location(): void
{
    //
}
```

### PHPDocs

#### 4.1)

Ne pas avoir de PHPDoc qui n'ajoutent pas d'information par rapport aux typehints ou aux noms d'argument/variable/propriété ou de méthodes.

#### 4.2)

Ne pas avoir de tag `@param` ou `@return` sans type ou sans nom de variable (pour $param)
Les tags `@param` doivent exister avant le tag `@return` qui doit exister avant `@throws`, et il doit y avoir un saut de ligne entre chaque type (entre param et return, entre return et throws, mais pas entre chaque params).

#### 4.3)

Lorsq'un PHPDoc fait référence à une classe ou interface, ne pas utiliser le FQCN, mais le nom de base, ou un alias. Exemple: `Session` au lieu de `\App\Models\Session`.  
La même règle s'applique aux typehints.

#### 4.4)

Les PHPDocs des propriétés ou des variables doivent être sur une seule ligne, sauf si il y a beaucoup à raconter par exemple. Tous les autres, notamment ceux des fonctions/méthodes sur plusieurs lignes.

Exemple:
```php
final class Foo
{
	/** @var \Some\Class Blablabli */
	private SomeClass $someProperty;

	/**
	 * Une description, mais c'est bien parce que le nom de la méthode est pourrie.
	 */
	public function someMethod(int $someArgument, int|string $someOtherArgument): void
	{
		// ...
	}
}
```
