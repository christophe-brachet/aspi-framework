# aspi-framework(In Progress - Not Ready to production !)
PHP Framework used in ASPI-CMF (Content Management Framework)
# Installation

Step 1 - Installer PHP 7.2 on your computer
On MacOS :
a) Install homebrew (package manager)
```sh
iMac-de-christophe:~ christophebrachet$ ruby -e "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/master/install)
```
b) Check for issues and conflicts that may occur during installation
```sh
iMac-de-christophe:~ christophebrachet$ brew doctor
```
c) Update Homebrew
```sh
iMac-de-christophe:~ christophebrachet$  brew update && brew upgrade
```
Step 2- Install MariaDB database (Best performance than Mysql)
On MacOS
a) Install MariaDB with Homebrew
```sh
iMac-de-christophe:~ christophebrachet$ brew install mariadb
```
b) Run the database installer
```sh
iMac-de-christophe:~ christophebrachet$ sudo mysql_install_db
```
c) Start MariaDB Server
```sh
iMac-de-christophe:~ christophebrachet$ mysql.server start
```
d) Securise MariaDB Server
```sh
iMac-de-christophe:~ christophebrachet$ mysql_secure_installation
```
```
NOTE: RUNNING ALL PARTS OF THIS SCRIPT IS RECOMMENDED FOR ALL MariaDB
      SERVERS IN PRODUCTION USE!  PLEASE READ EACH STEP CAREFULLY!

In order to log into MariaDB to secure it, we'll need the current
password for the root user.  If you've just installed MariaDB, and
you haven't set the root password yet, the password will be blank,
so you should just press enter here.

Enter current password for root (enter for none):
OK, successfully used password, moving on...

Setting the root password ensures that nobody can log into the MariaDB
root user without the proper authorisation.

Set root password? [Y/n]
New password:
Re-enter new password:
Password updated successfully!
Reloading privilege tables..
 ... Success!


By default, a MariaDB installation has an anonymous user, allowing anyone
to log into MariaDB without having to have a user account created for
them.  This is intended only for testing, and to make the installation
go a bit smoother.  You should remove them before moving into a
production environment.

Remove anonymous users? [Y/n]
 ... Success!

Normally, root should only be allowed to connect from 'localhost'.  This
ensures that someone cannot guess at the root password from the network.

Disallow root login remotely? [Y/n]
 ... Success!

By default, MariaDB comes with a database named 'test' that anyone can
access.  This is also intended only for testing, and should be removed
before moving into a production environment.

Remove test database and access to it? [Y/n]
 - Dropping test database...
 ... Success!
 - Removing privileges on test database...
 ... Success!

Reloading the privilege tables will ensure that all changes made so far
will take effect immediately.

Reload privilege tables now? [Y/n]
 ... Success!

Cleaning up...

All done!  If you've completed all of the above steps, your MariaDB
installation should now be secure.

Thanks for using MariaDB!
```
Step 3 -Install PHP 7.2
On MacOS :
a) Install PHP
```sh
iMac-de-christophe:~ christophebrachet$ brew tap homebrew/dupes
iMac-de-christophe:~ christophebrachet$ brew tap homebrew/php
iMac-de-christophe:~ christophebrachet$ brew install --without-apache --with-fpm --with-mysql php72
iMac-de-christophe:~ christophebrachet$ sudo rm /usr/bin/php 
iMac-de-christophe:~ christophebrachet$ sudo ln -s /usr/local/Cellar/php/7.2.8/bin/php /usr/bin/php 
```
b) install swoole with pecl
```sh
iMac-de-christophe:~ christophebrachet$ pecl install swoole
```
Step 4 - Install composer (dependency manager)
```sh
iMac-de-christophe:~ christophebrachet$ php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
iMac-de-christophe:~ christophebrachet$ php -r "if (hash_file('SHA384', 'composer-setup.php') === '544e09ee996cdf60ece3804abc52599c22b1f40f4323403c44d44fdfdd586475ca9813a858088ffbc1f233e9b180f061') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
iMac-de-christophe:~ christophebrachet$ php composer-setup.php
iMac-de-christophe:~ christophebrachet$ php -r "unlink('composer-setup.php');"
iMac-de-christophe:~ christophebrachet$ mv composer.phar /usr/local/bin/composer

```
Step 5 - Install imap 
On MacOS
a) Download PHP 7.2.8 on Github(https://github.com/php/php-src/tree/PHP-7.2.8) and uncompressed it on the your desktop.
```sh
iMac-de-christophe:~ christophebrachet$ brew install imap-uw
iMac-de-christophe:~ christophebrachet$ brew install openssl
iMac-de-christophe:~ brew link --force openssl
iMac-de-christophe:~ ln -s /usr/local/opt/openssl/include/openssl /usr/local/include
iMac-de-christophe:~ sudo mv /usr/bin/phpize /usr/bin/phpize-old
iMac-de-christophe:~ sudo mv /usr/bin/php-config /usr/bin/php-config
iMac-de-christophe:~ cd /Users/christophebrachet/Desktop/php-src-PHP-7.2.8/ext/imap/
iMac-de-christophe:imap christophebrachet$ phpize –clean
iMac-de-christophe:imap christophebrachet$ ./configure --with-kerberos --with-imap-ssl
iMac-de-christophe:imap christophebrachet$ make
iMac-de-christophe:imap christophebrachet$ cd modules
iMac-de-christophe:imap christophebrachet$ cp imap.so /usr/local/lib/php/pecl/20170718/
iMac-de-christophe:imap christophebrachet$ cp imap.la /usr/local/lib/php/pecl/20170718/
```
b) Modify your php.ini:
```sh
iMac-de-christophe:imap christophebrachet$ sudo nano  /usr/local/etc/php/7.2/php.ini
```
c) and add this line to load imap-php module:
```sh
extension="imap.so"
```
Step 6 - Install aspi-framework project from composer
```sh
Brachets-Mac-mini:Desktop cbrachet$ composer create-project aspi-components/framework:dev-master aspi-app
```
Step 7 - Go to application directy
```sh
Brachets-Mac-mini:Desktop cbrachet$ cd aspi-app
```
Step 8 - Init web application
```sh
Brachets-Mac-mini:aspi-app cbrachet$ php bin/console aspi:init
```
Step 9 - Start website
```sh
Brachets-Mac-mini:aspi-app cbrachet$ php bin/console aspi:webserver
```


