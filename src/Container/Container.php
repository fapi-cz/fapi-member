<?php declare(strict_types=1);

namespace FapiMember\Container;

use Exception;

class Container
{
	static array $instances = [];

	public static function set(string $name, mixed $instance): void
	{
		self::$instances[$name] = $instance;
	}

    public static function get(string $name): mixed
    {
        if (!isset(self::$instances[$name])) {
//			echo $name . '<br><br>';
			if (class_exists($name)) {
        		self::$instances[$name] = new $name();
			} else {
				throw new Exception("Service not found: " . $name);
			}
        }

        return self::$instances[$name];
    }
}
