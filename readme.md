## Requirements

- PHP >= 5.5.9
- OpenSSL PHP Extension
- PDO PHP Extension
- Mbstring PHP Extension
- Tokenizer PHP Extension

## Install

```bash
xu@calypso:~$ git clone git@space.git
xu@calypso:~$ cd space/
xu@calypso:~$ composer install              # install project dependencies
xu@calypso:~$ chmod a+rw storage -R         # folder for logs, cache, etc
xu@calypso:~$ chmod a+rw bootstrap/cache -R # folder for laravel internal cache

# create database (you should change credentials)
mysql> CREATE USER 'space'@'localhost' IDENTIFIED BY 'space';
mysql> CREATE DATABASE space;
mysql> GRANT ALL PRIVILEGES ON space . * TO 'space'@'localhost';
mysql> FLUSH PRIVILEGES;

xu@calypso:~$ cp .env.example .env          # create enviroment config file
xu@calypso:~$ vim .env                      # edit configuration (mail smtp options, db credentials you choose on db creation, debug mode). also you can edit mail config at config/mail.php file
xu@calypso:~$ php artisan key:generate      # generate unique application key
xu@calypso:~$ php artisan migrate           # run database migrations

// run development server. choose unused port
xu@calypso:~$ php artisan serve --port 8444 # now site can be accessed at http://localhost:8444
```

Then create Manager user

open tinker repl (to quit type `\q`)
```bash
xu@calypso:~$ php artisan tinker
```

in tinker type
```php
>>> $user = new \App\User;
>>> $user->email = 'admin@example.ru';
>>> $user->password = Hash::make('somePassword'); # replace somePassword with strong manager password
>>> $user->name = 'Administrator Name';
>>> $user->save();
>>> \q # quit tinker
```

## Development

- controllers: `app/Http/Controllers/`
- routes: `app/Http/routes.php`
- main config `config/app.php`,
- site config `config/space.php`


To see all defined routes and corresponding controller methods use `php artisan route:list` console command