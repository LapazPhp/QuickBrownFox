# QuickBrownFox

[![PHP Composer](https://github.com/LapazPhp/QuickBrownFox/actions/workflows/php.yml/badge.svg?branch=master)](https://github.com/LapazPhp/QuickBrownFox/actions/workflows/php.yml)

ORM independent RDB fixture data generator.

## Basic Usage

At first, define a session manager in your DI container or service locator:

```php
use Lapaz\QuickBrownFox\SessionManagerInterface;
use Lapaz\QuickBrownFox\EasySessionManager;

// $container points some application scope service locator
$container->set(SessionManagerInterface::class, function() use ($container) {
    return new EasySessionManager($container->get('dbal.connection'));
    // or return new EasySessionManager([/*DBAL connection parameters*/]);
});
```

Prepare new test session in your test case:

```php
use PHPUnit\Framework\TestCase;
use Lapaz\QuickBrownFox\SessionManagerInterface;

class BookRepositoryTest extends TestCase
{
    protected $session;
    
    protected function setUp()
    {
        /** @var ContainerInterface $container */
        $this->session = $container->get(SessionManagerInterface::class)->newSession();
    }
}
```

Session indicates single testing context. Database tables are automatically cleaned up in each sessions.
`tearDown` method is not necessary. So, you can see the last state of database after test.

### generate

Test your repository using randomly generated data by `generate` method:

```php
    public function testFindBook()
    {
        $this->session->into('books')->generate(10);
        
        $books = (new BookRepository($this->connection))->findAll();
        $this->assertCount(10, $books);
        $this->assertNotNull($books[0]->getAuthor());
    }
```

In above example, `generate` method generates 10 random books. You don't need to care column definition.
QuickBrownFox automatically fills non-null columns with random values.

Also you don't need to care relative tables because foreign key constraints are resolved automatically.
If depending table is not filled yet, QuickBrownFox generates data for it.

### load

You can test with detailed data using `load` method:

```php
    public function testGetBook()
    {
        $this->session->into('books')->load([
            ['id' => 1, 'title' => 'Design Pattern'],
            ['id' => 2, 'title' => 'Refactoring'],
        ]);
        
        $repository = new BookRepository($this->connection);

        $this->assertEquals('Design Pattern', $repository->get(1)->getTitle());
        $this->assertEquals('Refactoring', $repository->get(2)->getTitle());
        $this->assertNull($repository->get(3));
    }
```

In this case, `load` method loads specific data. If you don't specify some columns or relationships,
QuickBrownFox fills them with random values.

### with

You can specify automatically filled value patterns using `with` method:

```php
    public function testFindBook()
    {
        $this->session->into('books')->with(function($i) {
            return [
                'title' => 'Design Pattern ' . ($i + 1),
                'code' => sprintf('000-000-%03d', $i),
            ];
        })->generate(10);
        
        // test code here
    }

    public function testGetBook()
    {
        $this->session->into('books')->with([
            'preferred' => true,
            'rating' => function($i) { return 80 + $i * 5; },
        ])->load([
            ['id' => 1, 'title' => 'Design Pattern'],
            ['id' => 2, 'title' => 'Refactoring'],
        ]);
        
        // test code here
    }
```

`with` method sets common attributes for each data by callable which returns array.
You can also use an array which has callable or constant values.


## Advanced Usage

### Predefined Fixtures and Generators

QuickBrownFox supports predefined fixtures and generators.

```php
use Lapaz\QuickBrownFox\SessionManagerInterface;
use Lapaz\QuickBrownFox\FixtureManager;

// $container points some application scope service locator
$container->set(SessionManagerInterface::class, function() use ($container) {
    $fixtures = new FixtureManager();
    
    $fixtures->table('authors', function ($td) {
        $td->fixture('GoF')->define([
            ['id' => 1, 'name' => "Erich Gamma"],
            ['id' => 2, 'name' => "Richard Helm"],
            ['id' => 3, 'name' => "Ralph Johnson"],
            ['id' => 4, 'name' => "John Vlissides"],
        ]);
    });
    
    $fixtures->table('books', function ($td) {
        $td->generator('DesignPattern-N')->define(function($i) {
            return [
                'title' => 'Design Pattern ' . ($i + 1),
                'code' => sprintf('000-000-%03d', $i),
                'author_id' => 1,
            ];
        });
    });
    
    return $fixtures->createSessionManager($container->get('dbal.connection'));
    // or return $fixtures->createSessionManager([/*DBAL connection parameters*/]);
});
```

You can use named fixtures and generators in your test:

```php
    public function testFindBook()
    {
        // Loads 4 gangs.
        $this->session->into('authors')->load('GoF');

        // Bind 8 books to 4 gangs as authors.
        $this->session->into('books')->with('DesignPattern-N')->with([
            'author_id' => function($i) { return $i % 4 + 1; },
        ])->generate(8);
        
        $books = (new BookRepository($this->connection))->findAll();
        $this->assertEquals("Design Pattern 1", $book[0]->getTitle());
        $this->assertEquals("Erich Gamma", $book[0]->getAuthor()->getName());

        $this->assertEquals("Design Pattern 8", $book[7]->getTitle());
        $this->assertEquals("John Vlissides", $book[7]->getAuthor()->getName());
    }
```

- `load` method can load predefined fixture by name.
- `with` method can set predefined generator by name.

It's useful when you have some common data structure in your application.

You can use `with` method multiple times. They are merged.

### Default Values

FixtureManager provides `defaults` method to customize random value generation.
It's useful when table has some semantic constraints as its schema definition.

```php
    $fixtures->table('books', function ($td) {
        $td->defaults([
            'type' => function() {
                return mt_rand(1, 3); // books.type must be 1, 2 or 3
            }
        ]);
    });
```

In above example, `type` column is filled with random value between 1 and 3 even if not specified by `with`.
