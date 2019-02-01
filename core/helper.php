<?php

/**
 *
 */
function dd()
{
    $args = func_get_args();

    foreach($args as $arg) {
        print "<pre>";
        var_dump($arg);
        print "</pre>";
        print "<br>";
    }

    die();
}

/**
 * @param $path
 * @param array $data
 */
function view($path, $data = [])
{
    ob_start();

    extract($data);

    $path = implode(DIRECTORY_SEPARATOR, explode('.', $path));

    /** @noinspection PhpIncludeInspection */
    require_once join(DIRECTORY_SEPARATOR, [ROOT, 'resources', 'views', $path . '.php']);

    $contents = ob_get_contents();
    ob_end_clean();

    print $contents;
}

function layout($layout)
{
    $layout = 'layout.'.$layout;

    view($layout);
}


function config($path)
{
    $path = ROOT . '/' . str_replace('.', '/', $path) . '.php';

    /** @noinspection PhpIncludeInspection */
    return (object) require $path;
}

function url($path)
{
    print 'http://' . $_SERVER['HTTP_HOST'] . $path;
}

function redirect($path)
{
    header('Location: ' . $path);
    exit;
}

/**
 * @param $value
 * @param $option
 */
function selected($value, $option)
{
    if ($value === $option) {
        print 'selected';
    }
}

function value($value)
{
    if($value) {
        print $value;
        return $value;
    }

    print '';
    return $value;
}