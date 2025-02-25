<?php

class Router
{
    /**
     * @param string $route
     * @param Closure|string $path_to_include
     * @return void
     */
    public static function GET($route, $path_to_include): void
    {
        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            Router::route($route, $path_to_include);
        }
    }

    public static function POST($route, $path_to_include): void
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            Router::route($route, $path_to_include);
        }
    }

    /**
     * @param mixed $route
     * @param mixed $path_to_include
     * @param mixed $method
     * @return void
     * @deprecated No longer supported
     */
    public static function CUSTOM($route, $path_to_include, $method): void
    {
        if ($_SERVER['REQUEST_METHOD'] == $method) {
            Router::route($route, $path_to_include);
        }
    }

    public static function PUT($route, $path_to_include): void
    {
        if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
            Router::route($route, $path_to_include);
        }
    }

    public static function PATCH($route, $path_to_include): void
    {
        if ($_SERVER['REQUEST_METHOD'] == 'PATCH') {
            Router::route($route, $path_to_include);
        }
    }

    public static function DELETE($route, $path_to_include): void
    {
        if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
            Router::route($route, $path_to_include);
        }
    }

    public static function ANY($route, $path_to_include): void
    {
        Router::route($route, $path_to_include);
    }

    public static function extractUrlParts($route): array
    {
        $request_url = filter_var($_SERVER['REQUEST_URI'], FILTER_SANITIZE_URL);
        $route_parts = explode('/', $route);
        $request_url_parts = explode('/', $request_url);

        array_shift($route_parts);
        array_shift($request_url_parts);

        $parameters = [];
        for ($i = 0; $i < count($route_parts); $i++) {
            $route_part = $route_parts[$i];
            if (preg_match("/^[$]/", $route_part)) {
                $route_part = ltrim($route_part, '$');
                $parameters[] = $request_url_parts[$i];
            } else if ($route_parts[$i] != $request_url_parts[$i]) {
                return []; // Return an empty array if the route does not match
            }
        }
        return $parameters;
    }

    private static function route($route, $path_to_include): void
    {
        $callback = $path_to_include;
        if ($route == "/404") {
            if (!is_callable($callback)) {
                include_once("$path_to_include");
            } else {
                call_user_func_array($callback, []);
            }
            exit();
        }

        $request_url = filter_var($_SERVER['REQUEST_URI'], FILTER_SANITIZE_URL);
        $request_url = rtrim($request_url, '/');
        $request_url = strtok($request_url, '?');
        $route_parts = explode('/', $route);
        $request_url_parts = explode('/', $request_url);
        array_shift($route_parts);
        array_shift($request_url_parts);
        if ($route_parts[0] == '' && count($request_url_parts) == 0) {
            if (is_callable($callback)) {
                call_user_func_array($callback, []);
                exit();
            }
            include_once "$path_to_include";
            exit();
        }
        if (count($route_parts) != count($request_url_parts)) {
            return;
        }
        $parameters = [];
        for ($i = 0; $i < count($route_parts); $i++) {
            $route_part = $route_parts[$i];
            if (preg_match("/^[$]/", $route_part)) {
                $route_part = ltrim($route_part, '$');
                array_push($parameters, $request_url_parts[$i]);
                $$route_part = $request_url_parts[$i];
            } else if ($route_parts[$i] != $request_url_parts[$i]) {
                return;
            }
        }
        // Callback function
        if (is_callable($callback)) {
            call_user_func_array($callback, [$parameters]);
            exit();
        }
        include_once "$path_to_include";
        exit();
    }

    public static function toJSON($statusCode, $data = []): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
    }

    /**
     * Retrieves and decodes JSON data from a POST request.
     *
     * This function checks if the request method is POST and if the content type
     * is 'application/json'. If these conditions are met, it reads the raw POST data
     * from the input stream, trims any whitespace, and decodes the JSON data into
     * an associative array. If the conditions are not met, it returns an empty array.
     *
     * @return array An associative array of decoded JSON data or an empty array if the conditions are not met.
     */
    public static function getJSON(): array
    {
        if (trim($_SERVER['REQUEST_METHOD']) !== 'POST' || strcasecmp(!empty($_SERVER['CONTENT_TYPE']) ? trim($_SERVER['CONTENT_TYPE']) : '', 'application/json') !== 0) {
            return [];
        }

        $content = trim(file_get_contents('php://input'));
        $decoded = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return [];
        }

        return $decoded;
    }

    /**
     * Retrieves and sanitizes the body of a POST request.
     *
     * This function checks if the request method is POST. If it is not,
     * it returns an empty string. If the request method is POST, it iterates
     * through the \$_POST superglobal array, sanitizes each value to remove
     * special characters, and returns the sanitized data as an associative array.
     *
     * @return array|string An associative array of sanitized POST data or an empty string if the request method is not POST.
     */
    public static function getBody(): array|string
    {
        if (trim($_SERVER['REQUEST_METHOD']) !== 'POST') {
            return '';
        }

        $body = [];

        foreach ($_POST as $key => $value) {
            $body[$key] = filter_input(INPUT_POST, $key, FILTER_SANITIZE_SPECIAL_CHARS);
        }

        return $body;
    }

    /**
     * Automatically sets a csrf token
     * @throws \Random\RandomException
     */
    public static function set_csrf(): void
    {
        session_start();
        if (!isset($_SESSION["csrf"])) {
            $_SESSION["csrf"] = bin2hex(random_bytes(50));
        }
        echo '<input type="hidden" name="csrf" value="' . $_SESSION["csrf"] . '">';
    }


    /**
     * Check if a valid csrf token was sent with the request
     * @return bool
     */
    public static function is_csrf_valid(): bool
    {
        session_start();
        if (!isset($_SESSION['csrf']) || !isset($_POST['csrf'])) {
            return false;
        }

        return $_SESSION['csrf'] == $_POST['csrf'];
    }
}
