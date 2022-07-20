# QuickBrownFox

[![PHP Composer](https://github.com/LapazPhp/QuickBrownFox/actions/workflows/php.yml/badge.svg?branch=master)](https://github.com/LapazPhp/QuickBrownFox/actions/workflows/php.yml)

ORM independent RDB fixture data generator.

## Usage

Define some common rules while building bootstrap environment for your tests:

```php
// $container points some application scope service locator
$container->set(\Lapaz\QuickBrownFox\Database\SessionManager::class, function() use ($container) {
    $fixtures = new \Lapaz\QuickBrownFox\FixtureManager();
    
    $fixtures->table('authors', function ($td) {
        $td->fixture('GoF')->define([
            [ 'name' => "Erich Gamma" ],
            [ 'name' => "Richard Helm" ],
            [ 'name' => "Ralph Johnson" ],
            [ 'name' => "John Vlissides" ],
        ]);
    });
    
    $fixtures->table('books', function ($td) {
        $td->generator('DesignPattern-N')->define(function($i) {
            return [
                'title' => 'Design Pattern ' . ($i + 1),
                'code' => sprintf('000-000-%03d', $i),
                // not needed: 'author_id' => ?
            ];
        });
    });
    
    // 'database' must be DBAL connection or PDO
    return $fixtures->createSessionManager($container->get('database'));
});
```

Prepare new test session:

```php
use PHPUnit\Framework\TestCase;

class BookRepositoryTest extends TestCase
{
    protected $session;
    
    protected function setUp()
    {
        /** @var ContainerInterface $container */
        $this->session = $container->get(\Lapaz\QuickBrownFox\Database\SessionManager::class)->newSession();
    }
}
```

Add tests using predefined fixtures and generators:

```php
    public function testFindBook()
    {
        $this->session->into('authors')->load('GoF');
        $this->session->into('books')->with('DesignPattern-N')->generate(10);
        
        $books = (new BookRepository($this->connection))->findAll();
        $this->assertCount(10, $books);
        $this->assertEquals("Design Pattern 1", $book[0]->getTitle());
        
        $this->assertNotNull($book[0]->getAuthor());
    }
```

NOTE: Non-null foreign key constraints are randomly resolved if not present.


You can use inline fixture instead of predefined one:

```php
        $this->session->into('authors')->load([
            [ 'name' => "Erich Gamma" ],
            [ 'name' => "Richard Helm" ],
            [ 'name' => "Ralph Johnson" ],
            [ 'name' => "John Vlissides" ],
        ]);
```

And inline generator:

```php
        $this->session->into('books')->with(function($i) {
            return [
                'title' => 'Design Pattern ' . ($i + 1),
                'code' => sprintf('000-000-%03d', $i),
            ];
        })->generate(10);
```

Common attributes can be set for array form fixture data.

```php
        $this->session->into('authors')->with([
            'specialist' => true,
            'rating' => function($i) { return 80 + $i * 5; },
        ])->load([
            [ 'name' => "Erich Gamma" ],
            [ 'name' => "Richard Helm" ],
            [ 'name' => "Ralph Johnson" ],
            [ 'name' => "John Vlissides" ],
        ]);
```

You can append extra data repeatedly within a session.
These are deleted automatically when the next session would use the same table.

```php
        $this->session->into('authors')->with([
            'specialist' => false,
            'rating' => function() { return mt_rand(30, 50); },
        ])->generate(6));

        $this->session->into('authors')->with([
            'specialist' => false,
            'rating' => function() { return mt_rand(30, 50); },
        ])->generate(6));
```

Attributes are overridden by right one over left one. Overridden function are not evaluated, so unnecessary slow calculations are not called.

```php
        $this->session->into('books')->with('DesignPattern-N')->with([
            'title' => function($i) {
                return 'Design Pattern -2nd Edition- ' . ($i + 1);
            },
        ])->generate(10);
```

If you don't care each attributes, you can do simply:

```php
        $this->session->into('books')->generate(100);
```

But when there might be some property constraint, you can define table level generator rule:

```php
    $fixtures->table('books', function ($td) {
        $td->defaults([
            'type' => function() {
                return mt_rand(1, 3); // books.type must be 1, 2 or 3
            }
        ]);
    });
```
