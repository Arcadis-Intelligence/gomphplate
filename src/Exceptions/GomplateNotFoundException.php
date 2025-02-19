<?php

namespace ArcadisIntelligence\Gomphplate\Exceptions;

class GomplateNotFoundException extends \Exception
{
    public function __construct()
    {
        parent::__construct('Unable to locate gomplate binary', 0, null);
    }
}
