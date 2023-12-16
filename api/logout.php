<?php

//get the current session
session_start();
echo "Logging out";

//logout the user
session_destroy();
// header('Location: index.php');