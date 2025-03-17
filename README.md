# VISTA PHP Database Module
## Introduction
Vista PHP Database module is a part of Vista PHP Framework, but can also be used as a stand alone ORM (Object Relational Mapper) for PHP.
It is designed to simplify database manipulation and provide a secure way to interact with databases.

## Getting Started
### Installation
```bash
composer require vista-php/database
```

### Configuration
The database configuration should be stored in the `config/database.php` file.
The file should return an array with the following keys:
`DB_TYPE`, `DB_HOST`, `DB_NAME`, `DB_USER` and `DB_PASS`

#### For SQLite:
```php
return [
    'DB_TYPE' => 'sqlite',
    'DB_NAME' => __DIR__ . '../database/database.sqlite',
];
```

#### For MySQL:
```php
return [
    'DB_TYPE' => 'mysql',
    'DB_NAME' => 'database',
    'DB_HOST' => 'localhost',
    'DB_USER' => 'root',
    'DB_PASS' => 'root'
];
```
### Usage
All you need to do is extend the `Vista\Model\Model` class in one of your model class 
and define the table name, primary key, columns and relationship methods.

#### Example:
```php
use Vista\Model\Model;

class User extends Model
{
    protected string $table = 'users';
    protected string $primaryKey = 'id';
    protected array $columns = ['id', 'name', 'username', 'password'];
    protected array $relationships = ['posts']

    public function posts()
    {
        return Post::where('user_id', $this->id);
    }
}
```
