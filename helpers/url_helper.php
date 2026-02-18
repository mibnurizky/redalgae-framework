<?php
function route(string $name, array $params = []): string
{
    global $router;
    return $router->route($name, $params);
}