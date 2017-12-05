# QuickBrownFox

[![Build Status](https://travis-ci.org/LapazPhp/QuickBrownFox.svg?branch=master)](https://travis-ci.org/LapazPhp/QuickBrownFox)

ORM independent RDB fixture data generator.

## Usage

In PHPUnit `bootstrap.php`, define some common rules:

```php
<?php
$fixtureManager = new \Lapaz\QuickBrownFox\FixtureManager();

$fixtureManager->table('author', function ($td) {
    $td->fixture('GoF')->define([
        [ 'name' => "Erich Gamma" ],
        [ 'name' => "Richard Helm" ],
        [ 'name' => "Ralph Johnson" ],
        [ 'name' => "John Vlissides" ],
    ]);
});

$fixtureManager->table('book', function ($td) {
    $td->generator('DesignPattern-N')->define(function($i) {
        return [
            'title' => 'Design Pattern ' . ($i + 1),
            'code' => sprintf('000-000-%03d', $i),
            // not needed: 'author_id' => ?
        ];
    });
});
```

Prepare new test session:

```php
<?php
use PHPUnit\Framework\TestCase;

class BookRepositoryTest extends TestCase
{
    protected $session;
    protected $connection;
    
    protected function setUp()
    {
        global $fixtureManager;
        // Create some singleton like accessor if you hate **global** statement.
        
        $this->connection = new PDO('...');
        $this->session = $fixtureManager->newSession($this->connection);
    }
}
```

Add tests using predefined fixtures and generators:

```php
    public function testFindBook()
    {
        $this->session->into('autors')->load('GoF');
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
        $this->session->into('autors')->load([
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
        $this->session->into('autors')->with([
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
        $this->session->into('autors')->with([
            'specialist' => false,
            'rating' => function() { return mt_rand(30, 50); },
        ])->generate(6));

        $this->session->into('autors')->with([
            'specialist' => false,
            'rating' => function() { return mt_rand(30, 50); },
        ])->generate(6));
```

Attributes are overridden as left one over right one. Overridden function are not evaluated, so unnecessary slow calculations can be passed.

```php
        $this->session->into('books')->with('DesignPattern-N')->with([
            'title' => function($i) {
                return 'Design Pattern -2nd Edition- ' . ($i + 1);
            },
        ])->generate(10);
```
