Test
====

Installazione
---------------------------

Per configuare l'applicazione è necessario installare i seguenti programmi:

1 Composer (https://getcomposer.org/doc/00-intro.md)
2 Docker (https://docs.docker.com/engine/installation/)
3 Docker compose (https://docs.docker.com/compose/install/)

Dopo aver installato le precedenti applicazioni può iniziare la procedura di build dell'applicazione.

    composer install --ignore-platform-reqs

Dopo aver scaricato le dipendenze del progetto bisogna creare il database sull'immagine docker. Dalla root del progetto far partire i container:

    docker-compose up

Entrare nel container docker contenente l'immagine mysql lanciando il comando da linea di comando:

    docker exec -it phptest_mysql_1 bash

Entrati nel container bisogna creare il db e le tabelle:

    mysql --user=root --password=test 
    
    CREATE DATABASE testprontopro;


    CREATE TABLE product (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(30) NOT NULL,
        description VARCHAR(30) NOT NULL,
        image VARCHAR(200)
    )   

    CREATE TABLE tag (
       id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
       name VARCHAR(30) NOT NULL,
       product_id  INT(6) UNSIGNED NOT NULL REFERENCES product(id)
    )

Ultimate queste operazioni potrete accedere all'applicazione dall'indirizzo 

    http://127.0.0.80/product/list

