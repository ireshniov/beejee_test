# beejee_test

```
docker-compose up -d
```
```
docker-compose exec php composer install
```
```
cp .env.dist .env; fill variables in .env file;
```

```
docker-compose exec php ./bin/console.php migrations:migrate 
```
```
Test task is available on http://127.0.0.1:8080;

http://127.0.0.1:8080/tasks index endpoint;
```
