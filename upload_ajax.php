<?php

$action = isset($_GET['action']) ? $_GET['action'] : 'default';

sleep(4);

switch ($action)
{
    default:
        echo '<form>';
        echo '<input type="text" name="masfasfa"><br>';
        echo '<input type="text" name="masfasfa2"><br>';
        echo '<input type="text" name="masfasfa3"><br>';
        echo '<form>';
}



?>