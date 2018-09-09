# Symfony Namek Bank Pro

###### Requirements :

Docker<br>
Docker-compose<br>

###### How to start :

```
cp .env.dist .env

docker-compose up -d
docker-compose exec web composer install
```

Open a web-browser and type 'localhost'. (Make sure there is nothing listening on port *80 to avoid conflits)


### More :

Create an ADMIN Master by php bin/console with :
````
commande            params
CreateAdminMaster   (email) (firstname) (lastname)
````

Display number of existing creditcards by php bin/console with :
````
commande           
DisplayNbCreditcards   
````

### etc

No PHP Units, no Travis... :'( too late

