<?php

namespace ArcadisIntelligence\Gomphplate\Exceptions;

class InvalidDataException extends \Exception
{
    public function __construct()
    {
        parent::__construct('Invalid data provided');
    }
}
