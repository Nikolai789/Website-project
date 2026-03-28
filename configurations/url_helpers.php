<?php

if (!function_exists('app_url')) {
    function app_url(string $path = ''): string
    {
        static $basePath = null;

        if ($basePath === null) {
            $documentRoot = isset($_SERVER['DOCUMENT_ROOT']) ? realpath($_SERVER['DOCUMENT_ROOT']) : false;
            $projectRoot = realpath(dirname(__DIR__));

            if ($documentRoot && $projectRoot) {
                $normalizedDocumentRoot = str_replace('\\', '/', $documentRoot);
                $normalizedProjectRoot = str_replace('\\', '/', $projectRoot);

                if (strpos($normalizedProjectRoot, $normalizedDocumentRoot) === 0) {
                    $basePath = substr($normalizedProjectRoot, strlen($normalizedDocumentRoot));
                }
            }

            if ($basePath === null || $basePath === false) {
                $basePath = '';
            }

            $basePath = rtrim((string) $basePath, '/');
        }

        return $basePath . '/' . ltrim($path, '/');
    }
}
