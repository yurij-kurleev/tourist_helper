<?php
// Connect to MySQL
$link = mysqli_connect('5.187.0.69', 'root', 'k11o04s19t95', 'Excursions');
if (!$link) {
    echo 'Could not connect: ' . mysqli_error($link);
}
else echo 'Connected';

// Make my_db the current database
$sql = "CREATE TABLE IF NOT EXISTS category
(id_c SMALLINT NOT NULL AUTO_INCREMENT,
title VARCHAR(50) NOT NULL,
PRIMARY KEY (id_c))";
if (mysqli_query($link, $sql) === TRUE) {
printf("<br>Таблица category успешно создана.\n");
}
else  printf("<br>Таблица category не была создана.\n");

$sql = "CREATE TABLE IF NOT EXISTS coord_point
(id_cp MEDIUMINT NOT NULL AUTO_INCREMENT,
longitude DOUBLE NOT NULL,
latitude DOUBLE NOT NULL,
PRIMARY KEY (id_cp))";
if (mysqli_query($link, $sql) === TRUE) {
printf("<br>Таблица coord_point успешно создана.\n");
}
else  printf("<br>Таблица coord_point не была создана.\n");

$sql = "CREATE TABLE IF NOT EXISTS excursion
(id_e MEDIUMINT NOT NULL AUTO_INCREMENT,
title VARCHAR(50) NOT NULL,
language VARCHAR(30) NOT NULL,
description TEXT,
id_c SMALLINT NOT NULL,
current_price NUMERIC(8, 2) NOT NULL,
PRIMARY KEY (id_e),
FOREIGN KEY(id_c) REFERENCES category(id_c)
ON DELETE CASCADE
ON UPDATE CASCADE)";
if (mysqli_query($link, $sql) === TRUE) {
printf("<br>Таблица excursion успешно создана.\n");
}
else  printf("<br>Таблица excursion не была создана.\n");

$sql = "CREATE TABLE IF NOT EXISTS stop
(ordinal_number SMALLINT NOT NULL,
id_e MEDIUMINT NOT NULL,
id_cp MEDIUMINT NOT NULL,
PRIMARY KEY (id_e, id_cp),
FOREIGN KEY(id_e) REFERENCES excursion(id_e)
ON DELETE CASCADE
ON UPDATE CASCADE,
FOREIGN KEY(id_cp) REFERENCES coord_point(id_cp)
ON DELETE CASCADE
ON UPDATE CASCADE)";
if (mysqli_query($link, $sql) === TRUE) {
printf("<br>Таблица stop успешно создана.\n");
}
else  printf("<br>Таблица stop не была создана.\n");

$sql = "CREATE TABLE IF NOT EXISTS factual_excursion
(id_fe MEDIUMINT NOT NULL AUTO_INCREMENT,
date DATE NOT NULL,
factual_price NUMERIC(8,2) NOT NULL,
id_e MEDIUMINT NOT NULL,
PRIMARY KEY (id_fe),
FOREIGN KEY(id_e) REFERENCES excursion(id_e)
ON DELETE CASCADE
ON UPDATE CASCADE)";
if (mysqli_query($link, $sql) === TRUE) {
printf("<br>Таблица factual_excursion успешно создана.\n");
}
else  printf("<br>Таблица factual_excursion не была создана.\n");

$sql = "CREATE TABLE IF NOT EXISTS user
(id_u MEDIUMINT NOT NULL AUTO_INCREMENT,
full_name VARCHAR(80) NOT NULL,
img_ref VARCHAR(150),
login VARCHAR(20) NOT NULL,
password VARCHAR(20) NOT NULL,
PRIMARY KEY (id_u))";
if (mysqli_query($link, $sql) === TRUE) {
printf("<br>Таблица user успешно создана.\n");
}
else  printf("<br>Таблица user не была создана.\n");

$sql = "CREATE TABLE IF NOT EXISTS role
(id_r TINYINT NOT NULL AUTO_INCREMENT,
title VARCHAR(20) NOT NULL,
PRIMARY KEY (id_r))";
if (mysqli_query($link, $sql) === TRUE) {
printf("<br>Таблица role успешно создана.\n");
}
else  printf("<br>Таблица role не была создана.\n");

$sql = "CREATE TABLE IF NOT EXISTS visit
(id_u MEDIUMINT NOT NULL,
id_fe MEDIUMINT NOT NULL,
rate NUMERIC(3,1),
comment TEXT,
id_r TINYINT NOT NULL,
PRIMARY KEY (id_u, id_fe),
FOREIGN KEY(id_u) REFERENCES user(id_u)
ON DELETE CASCADE
ON UPDATE CASCADE,
FOREIGN KEY(id_fe) REFERENCES factual_excursion(id_fe)
ON DELETE CASCADE
ON UPDATE CASCADE,
FOREIGN KEY(id_r) REFERENCES role(id_r)
ON DELETE CASCADE
ON UPDATE CASCADE)";
if (mysqli_query($link, $sql) === TRUE) {
printf("<br>Таблица visit успешно создана.\n");
}
else  printf("<br>Таблица visit не была создана.\n");
?>