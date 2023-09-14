<?php

namespace Architekt\Http;

class Call
{
    const TYPE = 'CURL';

    static public function get($url, $options = array()): ?string
    {
        $options = array_merge([
            'timeout' => 10,
            'cache' => true,
            'cache_timeout' => 24 * 3600,
            'method' => 'GET'
        ], $options
        );

        if (true === $options['cache']) {
            if (!self::cacheExpired($url, $options['cache_timeout'])) {
                return self::getCacheContent($url);
            }
        }
        if ('CURL' === self::TYPE) {
            $ch = curl_init();
            $header = array(
                "Accept: text/xml,application/xml,application/xhtml+xml,text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5",
                "Cache-Control: max-age=0",
                "Connection: keep-alive",
                "Keep-Alive: 300",
                "Accept-Charset: utf-8,ISO-8859-1;q=0.7,*;q=0.7",
                "Accept-Language: fr-fr,fr;q=0.5",
                "Pragma: "
            );

            $cfgCall = array(
                CURLOPT_URL => $url,
                CURLOPT_FAILONERROR => 1,
                CURLOPT_FOLLOWLOCATION => 1,
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_HTTPHEADER => $header,
                CURLOPT_USERAGENT => "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:21.0) Gecko/20100101 Firefox/20.0",
                CURLOPT_ENCODING => "gzip, deflate",
                CURLOPT_TIMEOUT => $options['timeout'],
                CURLOPT_PORT => 443,
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_SSL_VERIFYPEER => 0,
            );
            if (str_starts_with($url, 'http:')) {
                $cfg_call[CURLOPT_PORT] = 80;
            }

            curl_setopt_array($ch, $cfgCall);
            $response = curl_exec($ch);
        } else {
            $response = file_get_contents($url);
        }

        if ($response === false) {
            return null;
        } else {
            if (true === $options['cache']) {
                if (!self::setFileContent($url, $response)) {
                    die('error putting cache');
                }
            }
            return $response;
        }
    }

    static private function cacheExpired(string $url, int $timeout): bool
    {
        $timestamp = self::getCacheTimestamp($url);
        return null === $timestamp || $timestamp + $timeout < time();
    }

    static private function getCacheTimestamp(string $url): ?int
    {
        return self::cacheExists($url) ? filemtime(self::getCacheFile($url)) : null;
    }

    static private function cacheExists(string $url): bool
    {
        return file_exists(self::getCacheFile($url));
    }

    static private function getCacheFile(string $url): string
    {
        return PATH_FILER . 'Import/' . md5($url);
    }

    static private function getCacheContent(string $url): string
    {
        return file_get_contents(self::getCacheFile($url));
    }

    static private function setFileContent(string $url, string $content): bool
    {
        return false !== file_put_contents(self::getCacheFile($url), $content);
    }
}