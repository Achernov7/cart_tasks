<?php

class View
{
    private static string $path;
    private static ?array $data;

    public static function render(string $str, array $data = []): void
    {
        print(self::view($str, $data));
        die();
    }

    private static function view(string $str, array $data = []): string
    {
        self::$data = $data;
        $path = str_replace('public', 'resources/views/', $_SERVER['DOCUMENT_ROOT']);
        self::$path = $path . str_replace('.', '/', $str) . '.php';
        return self::getContent();
    }

    private static function getContent(): string
    {

        extract(self::$data);
        ob_start();
        include self::$path;
        $html = ob_get_contents();
        ob_end_clean();

        return $html;
    }
}