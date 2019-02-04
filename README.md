# Pokématos App
Collaborative map for Pokemon Go players. Raid reporting, Discord notifications, powerfull REST api. Project under development

### Prerequisites
1. Install NPM & nodeJs
2. Install Composer
3. Install Laravel
3. Install Database server (like Wamp)

### Installation
1. Unzip all files in your project folder
2. with your favorite CLI, run ```npm install``` and ```composer install``` to add all needed dependencies
3. rename ```.env.example``` in ```.env``` and add your database connexion information (host, port, database name, user & pwd)
4. run ```php artisan key:generate```
5. run ```php artisan migrate:fresh``` and next ```php artisan db:seed``` to add database structure & dummy data
6. run ```php artisan serve```. Pokematos is now available at ```http://127.0.0.1:8000```
