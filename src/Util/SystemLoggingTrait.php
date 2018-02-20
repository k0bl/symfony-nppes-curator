<?php

namespace App\Util;

use Psr\Log\LoggerInterface;

trait SystemLoggingTrait {
    protected $logger;
    protected $counts = array();

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
        return $this;
    }

    public function getLogger()
    {
        return $this->logger;
    }

    protected function logCall($functionName)
    {
        $this->debugLog("$functionName() called", true);
    }

    protected function logCallCount($functionName)
    {
        $count = array_key_exists($functionName, $this->counts)
               ? $this->counts[$functionName]
               : 0;
        $count++;
        $this->counts[$functionName] = $count;
        $this->debugLog("$functionName() call #$count", true);
    }

    protected function doLog($level, $message, $context = null)
    {
        if ($this->logger && $this->logger instanceof LoggerInterface) {
            if ($level === 'log' || !method_exists($this->logger, $level)) {
                throw new \RuntimeException("Invalid logging level: $level");
            }
            if ($context) {
                $this->logger->$level($message, $context);
            } else {
                $this->logger->$level($message);
            }
        }
    }

    protected function emergencyLog($message, $decorate = false)
    {
        if ($decorate) {
            $message = $this->decorateLog($message);
        }
        $this->doLog('emergency', $message);
    }

    protected function alertLog($message, $decorate = false)
    {
        if ($decorate) {
            $message = $this->decorateLog($message);
        }
        $this->doLog('alert', $message);
    }

    protected function criticalLog($message, $decorate = false)
    {
        if ($decorate) {
            $message = $this->decorateLog($message);
        }
        $this->doLog('critical', $message);
    }

    protected function errorLog($message, $decorate = false)
    {
        if ($decorate) {
            $message = $this->decorateLog($message);
        }
        $this->doLog('error', $message);
    }

    protected function warningLog($message, $decorate = false)
    {
        if ($decorate) {
            $message = $this->decorateLog($message);
        }
        $this->doLog('warning', $message);
    }

    protected function noticeLog($message, $decorate = false)
    {
        if ($decorate) {
            $message = $this->decorateLog($message);
        }
        $this->doLog('notice', $message);
    }

    protected function infoLog($message, $decorate = false)
    {
        if ($decorate) {
            $message = $this->decorateLog($message);
        }
        $this->doLog('info', $message);
    }

    protected function debugLog($message, $decorate = false)
    {
        if ($decorate) {
            $message = $this->decorateLog($message);
        }
        $this->doLog('debug', $message);
    }

    protected function decorateLog($message)
    {
        return substr(get_called_class(), strrpos(get_called_class(), '\\') + 1) . " $message";
    }

}
