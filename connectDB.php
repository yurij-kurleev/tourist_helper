<?php
$link = mysqli_connect('localhost', 'root', '', 'Excursions');
if (!$link) {
    echo 'Could not connect: ' . mysqli_error($link);
}
else echo 'Connected';