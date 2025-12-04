<?php

if (!function_exists('to_object')) {
    function to_object(array $array)
    {
        return json_decode(json_encode($array), false);
    }
}

if (!function_exists('path_to_url')) {
    function path_to_url(?string $path): string|null
    {
        if (!$path) return null;
        if (str_starts_with($path, 'http')) return $path;
        return url('storage/' . $path);
    }
}