<?php

namespace App\Exception;

class ProviderImportException extends \RuntimeException
{
    protected $indicator;

    public function __construct($message, $indicator)
    {
        $this->indicator = $indicator;
        parent::__construct($message);
    }

    final public function getIndicator()
    {
        return $this->indicator;
    }
}
