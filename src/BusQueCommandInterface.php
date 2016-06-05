<?php

namespace MGDigital\BusQue;

interface BusQueCommandInterface
{

    /**
     * @return mixed
     */
    public function getCommand();

    /**
     * @return string|null
     */
    public function getId();
}
