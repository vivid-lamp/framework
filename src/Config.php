<?php

declare(strict_types=1);

namespace SleepyLamp\Framework;

class Config
{
    protected $configPath;

    protected $config;

    public function __construct($configPath)
    {
        $this->configPath = $configPath;
    }

    public function load($file)
    {
        $this->config[$file] = include $this->configPath . $file . '.php';
    }

    public function get(string $name, $default = null)
    {
        $name = explode('.', $name);
        $file = $name[0];
        if (!isset($this->config[$file])) {
            $this->load($file);
        }
        $config = $this->config;
        foreach ($name as $item) {
            if (isset($config[$item])) {
                $config = $config[$item];
            } else {
                return $default;
            }
        }
        return $config;
    }
}
