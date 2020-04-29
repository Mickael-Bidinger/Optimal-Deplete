<?php


namespace MB;


class Queue
{
    private static $queue = [];

    public static function add($instance, string $method, array $parameters = [])
    {
        self::$queue[] = [
            'instance' => $instance,
            'method' => $method,
            'parameters' => $parameters,
        ];
    }

    public function flush()
    {
        \header('Content-Length: ' . \ob_get_length());
        \ob_end_flush();
        \flush();

        foreach (self::$queue as $item) {
            if (\is_null($item['instance'])) {
                \call_user_func_array($item['method'], ($item['parameters']));
                continue;
            }
            \call_user_func_array([($item['instance']), $item['method']], ($item['parameters']));
        }
    }

    public function prepare()
    {
        \ob_end_clean();
        \header('Connection: close' . PHP_EOL);
        \ignore_user_abort(true);
        \ob_start();
        \register_shutdown_function([$this, 'flush']);
    }

}