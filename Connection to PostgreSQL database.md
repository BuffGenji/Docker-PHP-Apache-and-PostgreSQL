
In this paper we will make a docker container with a database  we can connect to from our local pgAdmin application. The objective is to be able to see that no new connections are made when multiple actions are made (simulated with curl requests).

This composition of **compose.yaml** and **Dockerfile** will allow us to get a simple PostgreSQL database with a volume up and running.


# Dockerfile

```dockerfile
# Use an official PHP Apache runtime
FROM php:8.2-apache
# Enable Apache modules
RUN a2enmod rewrite
# Install PostgreSQL client and its PHP extensions

RUN apt-get update \
&& apt-get install -y libpq-dev \
&& docker-php-ext-install pdo pdo_pgsql

# Set the working directory to /var/www/html
WORKDIR /var/www/html
# Copy the PHP code file in app/ into the container at /var/www/html
COPY ../app/ .
```

There is a lot of content here, we can divide it into 2 parts.

## PHP part

What PHP itself needs on the container - which is where the PHP will be running - are some extensions, most notably to establish the connection to a PostgreSQL database it will need the `pdo` extension for the PDO API. This along with the `pdo_pgsql` extension that allows us to connect to a PostgreSQL database.

```dockerfile
docker-php-ext-install pdo pdo_pgsql
```

Now before this part we have what the container will need some installations that normally come with the full installation of PostgreSQL which contain some libraries it will need to do some processing. 

```dockerfile
RUN apt-get update \
&& apt-get install -y libpq-dev
```


## Apache part

Now we have some things, options, configuration sin general that we need to do to egt the. PHP part connected to the Apache Web server. 

```dockerfile
# Use an official PHP Apache runtime
FROM php:8.2-apache
# Enable Apache modules
RUN a2enmod rewrite
```

First off, we download the image from the Hub. 

The `a2enmod rewrite` will allow Apache to rewrite URL and redirect traffic to other URLs or ports. This might not be necessary later on if our framework - currently the Fat Free Framework.


Finally we have the `WORKDIR` and the `COPY` commands that are to properly configure Apache in a very high-level way - we will most likely still need to configure more specific things later on, like HTTPS and adding some middle-ware on the server level (not allowing people to access certain paths and site locations ex: /api ).

```dockerfile
# Set the working directory to /var/www/html
WORKDIR /var/www/html
# Copy the PHP code file in /app into the container at /var/www/html
COPY ../app/ .
```
The `/var/www/html` is the default directory in Apache, from which the Applications public files are served. And with the command `COPY` we can place a copy of out files in our app directory in the html directory, supplanting it. 

Meaning that the 'new root' is actually `var/www/app`, because all of the content in app is now in html. It is still in the system as `var/www/html` though. Not to be confused.


# compose.yaml

```dockercompose.yaml

services:
# Apache webserver service
	webserver:
		container_name: PHP-webServer
		build:
		# Dockerfile path
		context: .
		dockerfile: Dockerfile
		# Mount the local ./app directory to /var/www/html in the container
		volumes:
			- ./app:/var/www/html
		# Map port 8000 on the host to port 80 on the container
		ports:
			- 8000:80
		depends_on:
			- postgres

	postgres:
		image: postgres:16.0
		container_name: postgres-database
		environment:
			POSTGRES_DB: test_database		
			POSTGRES_USER: db_user
			POSTGRES_PASSWORD: db_password
		ports:
			- "5433:5432"
		volumes:
			- postgres-volume:/var/lib/postgresql/data
	
volumes:
	postgres-volume:
		driver: local
```

I have already gone over what a general `compose.yaml` looks like, so here we will only go over the important differences that make this problem work how it does.

The services in this project are the **webserver** and the **postgres** which are the Apache (with PHP) and the Database.

In the `build` option, being the entry point for the `docker compose up` command, the  `context` option specifies where the Dockerfile is located, and the accompanying `dockerfile` is just to tell the `docker-compose`what the name of the Dockerfile is, which in this case is redundant because the default value fo the dockerfile is 'Dockerfile'.


Next we have a `volumes` option which will create for us a volume that will map - just like the ports, with the exact same syntax `internal:external` - the directories to each other, which means that the changes made in one will reflect in the other. 

### Important

There are 4 important things here : 

- **port-mapping** : this refers both to the mapping with the webserver container to our host, which would be the `8000:80` which connects a normal HTTP request coming from the host - using the browser - and then there is the connection to the database ( also from the host ). In this case we have a `5433:5432`, which could be `5432:5432` but I just have another container doing the same thing - on the same port - and for that reason I changed it.

- **environment** : this is all of the variables needed to connect to your database, which you can connect to from you local pgAdmin **on port 5433** in this case.

- **volumes** : we have in the `postgres` service an option `volumes` which specifies a **named volume** - which is just a volume that stays once the container stack stops - that we later specify as a different `composer object`, which is why it is at the same indentation as the unique `service` keyword.  The `driver` is where your data will go, so `local`just means that the files will be kept on the `docker host`  - which is terminology meaning my machine and local filesystem.


Now we have a dockerized application with a database, we can now add functionality and we will have a normal application. This is the link to a Github project that has everything. 

Link :  [Docker-PHP-Apache-and-PostgreSQL](https://github.com/BuffGenji/Docker-PHP-Apache-and-PostgreSQL)