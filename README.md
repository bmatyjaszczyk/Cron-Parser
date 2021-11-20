# Deliveroo Cron Parser

### How to install
1. Add permissions to php file
   - ````chmod +x find.php````
2. Install PHP 
   - ````apt install php php-cli````  or ```` brew install php````  if you are on Mac)
3. Install composer by running following commands
   - ````curl -sS https://getcomposer.org/installer -o /tmp/composer-setup.php````
   - ````sudo php /tmp/composer-setup.php --install-dir=/usr/local/bin --filename=composer````
4. Install dependencies:
    - ````composer install````
5. When finished, it's ready to run script and tests


Installation help links if needed:
- Installing on ubuntu: https://www.digitalocean.com/community/tutorials/how-to-install-and-use-composer-on-ubuntu-20-04



### To run:
````php find.php  "*/15 0 1,15 * 1-5 /usr/bin/find"````

### To run tests:
````./vendor/bin/phpunit tests````




### Notes:
   - Combinations are **not** checked. 
     - Example:
       - If we provide following string ````* * 1 * */3```` script won't check if day of the week is day of the month 1
   - I'm using 1-7 for days of week