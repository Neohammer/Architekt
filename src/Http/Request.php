<?php

namespace Architekt\Http;

use Architekt\Http\Exceptions\InvalidServerConfigurationException;
use Architekt\Transaction;

class Request
{
    const URI_PARAMETERS = 'uri';

    static public function getUri(): ?string
    {
        return self::get(self::URI_PARAMETERS);
    }

    static public function get(string $key ,$default = null): ?string
    {
        return self::getAll()[$key] ?? $default;
    }

    static public function getAll(): ?array
    {
        return $_GET ?? null;
    }

    static public function getFilters(): ?array
    {
        $filters = self::get('q');
        if(null === $filters) return null;

        return self::parseFilters($filters);
    }

    static private function parseFilters(string $query) :array
    {
        $filters = explode(',', $query);
        foreach ($filters as $k => $v) {
            $params = explode(':', $v);
            unset($filters[$k]);
            $filters[$params[0]] = $params[1] ?? null;
        }
        return $filters;
    }

    static public function route(string $route): string
    {
        if (Request::hasFilters()) {
            $route .= sprintf('?q=%s', Request::get('q'));
        }
        return $route;
    }

    static public function hasFilters(): ?bool
    {
        return null !== self::get('q');
    }

    static public function isXhrRequest(): bool
    {
        if (array_key_exists('HTTP_X_REQUESTED_WITH' , $_SERVER)) {
           return 'XMLHttpRequest' === $_SERVER['HTTP_X_REQUESTED_WITH'];
        }
        return false;
    }

    static public function isModalRequest(): bool
    {
        return in_array('modal' , [
            Request::get('returnType'),
            Request::post('returnType')
        ],true);
    }

    static public function postAll(): ?array
    {
        return $_POST ?? null;
    }

    static public function post(string $key ,$default=null): ?string
    {
        return self::postAll()[$key] ?? $default;
    }

    static public function postSet(string $key ,mixed $value): void
    {
        $_POST[$key] = $value;
    }

    static public function postArray(string $key ,$default=null): ?array
    {
        $value = self::postAll()[$key] ?? $default;

        return (is_array($value)?$value:$default);
    }

    static public function postTags(string $key, $default=null): ?array
    {
        if(isset($_POST) && array_key_exists($key, $_POST)  ){
            $tags = explode(',',$_POST[$key]);
            foreach ($tags as $k=>$v) {
                $tags[$k] = trim(ucfirst($v));
            }
            return $tags;
        }
        return $default;
    }

    static public function file(string $key, $default=null): ?array
    {
        if(isset($_FILES) && array_key_exists($key, $_FILES) ){
            return $_FILES[$key];
        }
        return $default;
    }

    static public function sessionAll(): ?array
    {
        if(isset($_SESSION)) {
            return $_SESSION;
        }

        throw new InvalidServerConfigurationException("Session is not started");
    }

    static public function session(string $key, $default = null): string|bool|null
    {
        return self::sessionAll()[$key] ?? $default;
    }

    static public function sessionFlush(string $key, $default = null): ?string
    {
        $value = self::session($key,$default);
        self::sessionUnset($key);
        return $value;
    }

    /**
     * @param mixed $value
     */
    static public function sessionSet(string $key, $value): void
    {
        $_SESSION[$key] = $value;
    }


    static public function sessionUnset(string $key): void
    {
        if (array_key_exists($key,self::sessionAll())) {
           unset($_SESSION[$key]);
        }
    }

    static public function sessionArray(string $key, $default = null): ?array
    {
        $value = self::sessionAll()[$key] ?? null;
        return (is_array($value)?$value:$default);
    }

    static public function hasHimselfReferer(): bool
    {

        if(!array_key_exists('HTTP_REFERER',$_SERVER)) {
            return false;
        }
        preg_match('|^http?://([^/]+)|',$_SERVER['HTTP_REFERER'],$matches);

        return $matches[1] === $_SERVER['SERVER_NAME'];

    }


    static public function to301(string $route): void
    {
        http_response_code(301);
        self::redirect($route);
    }

    static public function to403(?string $redirectRoute = '/Redirect/error/403'): void
    {
        http_response_code(403);
        if (!Request::isXhrRequest()) {
            self::redirect($redirectRoute);
        }
    }

    static public function to404(): void
    {
        http_response_code(404);
        if (!Request::isXhrRequest()) {
            self::redirect('/Redirect/error/404');
        }
        exit();
    }

    static public function to500(): void
    {
        http_response_code(500);
        if (!Request::isXhrRequest()) {
            self::redirect('/Redirect/error/500');
        }
        exit();
    }

    static public function redirect($route): void
    {
        if (Request::isXhrRequest()) {
            echo $route;
        }
        else{
            header('Location: ' . $route);
        }
        exit();
    }

    static public function method(): string
    {
        return $_SERVER['REQUEST_METHOD'] ?? 'GET';
    }
}