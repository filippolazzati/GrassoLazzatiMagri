# GrassoLazzatiMagri
DREAM Software Engeneering 2 project at Politecnico di Milano
# Installation instructions
In order to install **DREAM**, you need to have running on your pc both **Python** and **PHP** and also a database instance like _postgresql_ or _mysql_.
## Database installation
You can download _postgresql_ at [postgres downloads](https://www.postgresql.org/download/) or, if you prefer _mysql_, you can download it at [mysql downloads](https://www.mysql.com/downloads/) or install it with a package manager of your choice.
## Python installation
First of all, you can install **python** for instance from [python downloads](https://www.python.org/downloads/) and then you need to install the modules [Numpy](https://numpy.org/), [Pandas](https://pandas.pydata.org/) and [JAX](https://github.com/google/jax), which can be installed through the following commands:
```shell
python3 -m pip install numpy
python3 -m pip install pandas
python3 -m pip install jax
```
or, simply, through:
```shell
pip install numpy
pip install pandas
pip install jax
```
if you have just one version of python installed (at least python 3 is required).
## PHP, composer and Symfony installation
You need to install:
1. **PHP 8.0.2** or higher, which can be downloaded at [PHP downloads](https://www.php.net/downloads.php), and these **PHP** extensions (which are installed and enabled by default in most **PHP 8** installations): [Ctype](https://www.php.net/book.ctype), [iconv](https://www.php.net/book.iconv), [PCRE](https://www.php.net/book.pcre), [Session](https://www.php.net/book.session), [SimpleXML](https://www.php.net/book.simplexml), and [Tokenizer](https://www.php.net/book.tokenizer);
2. [Composer](https://getcomposer.org/download/), which is used to install PHP packages;
3. you have also to install [Symfony CLI](https://symfony.com/download); this creates a binary called symfony that provides all the tools you need to develop and run your Symfony application locally.

You are suggested to add both **PHP** and _Symfony CLI_ to your `path` environment variable.
## Start DREAM
Finally, you need to:
1. clone the [repository](https://github.com/Chiara-Magri/GrassoLazzatiMagri) in a directory of your choice;
2. Enter the implementation directory `DREAM/`. All the following commands and actions must be performed inside this directory.
3. create a file **.env.local** which will be used for environment setup;
4. For email configuration, enter the following line inside the **.env.local** file (these are the credentials for the sample account we used for testing)
```yaml
MAILER_DSN="gmail+smtp://dream.sw.eng.2.project@gmail.com:dream-password@default"
```
followed by the following line in case you have _POSTGRESQL_ running on your pc (`db_user` and `db_password` are respectively the username and password of your database and `13` represents the version installed):
```yaml
DATABASE_URL="postgresql://db_user:db_password@127.0.0.1:5432/dream?serverVersion=13"
```
or, in case you are using _MySQL_ (same meaning for `db_user` and `db_password`):
```yaml
DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/dream?serverVersion=5.7"
```
You also need to setup the path the **Python 3** executable on your system:
```yaml
PYTHON_PATH=python3 # adapt for your system
```
5. You may also need to enable the appropriate database driver in the **PHP** configuration. To do so, find the file `php.ini` in the **PHP** installation folder, and remove the comment from the line corresponding to your database:
```yaml
;extension=pdo_mysql
```
that is, remove the semicolon ';' from it:
```yaml
extension=pdo_mysql
```
(for **Postgres**, uncomment the line `extension=pdo_pgsql`).

6. make _Composer_ install the project's dependencies into `vendor/`:
```shell
cd DREAM/
composer install
```
7. initialize the database instance through the commands:
```shell
php bin/console d:d:c % create database
php bin/console d:s:u -f % synchronize schema
```
8. start the server:
```shell
symfony server:start
```
9. call the commands `app:setup:populate-database` and `app:setup:populate-areas` to populate the database with sample data (in particular Telangana areas, needed to create a farmer account):
```shell
cd DREAM/
php bin/console app:setup:populate-database
php bin/console app:setup:populate-areas
```
10. you can now open a browser and go to [127.0.0.1:8000/](127.0.0.1:8000/) to connect to **DREAM**.
## Run tests
In order to be able to run the tests, you also need to follow the next steps (in the project directory `DREAM/`):
1. add the line that you previously added to your `.env.local` file:
```yaml
DATABASE_URL="..."
```
to the file `.env.test.local` (that you need to create);

2. run the commands to initialize the test database (it will be called `dream_test`):
```shell
php bin/console --env=test doctrine:database:create
php bin/console --env=test doctrine:schema:create
```
3. you are now ready to run tests through the command:
```shell
php ./vendor/bin/phpunit
```
You can specify a test name after the command to run that specific test, e.g. `php ./vendor/bin/phpunit tests/Forum/ForumTest.php`.
