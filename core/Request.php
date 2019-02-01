<?php

namespace Core;

class Request
{
    public function input($key, $filter = FILTER_DEFAULT, $options = []) 
    {
        if (isset($_GET[$key])) {
            return filter_input(INPUT_GET, $key, $filter, $options);
        }

        if (isset($_POST[$key])) {
            return filter_input(INPUT_POST, $key, $filter, $options);
        }

        return null;
    }
}