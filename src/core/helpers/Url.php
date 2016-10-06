<?php

function redirect($uri)
{
    header('Location: '.$uri);
    exit;
}