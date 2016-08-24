<?php

namespace MGDigital\BusQue\Redis\Predis;

use Predis\Command\ScriptCommand;

class LuaFileCommand extends ScriptCommand
{

    private $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function getScript()
    {
        return file_get_contents($this->path);
    }
}
