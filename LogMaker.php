<?php

class LogMaker {
    private static ?LogMaker $object = null;
    private const ERROR_LOG_FILE = './errorLog.log';
    private const LOG_DIR = './logs/';


    private function __construct() {
        ini_set("log_errors", TRUE);
    }

    public static function getLogMakerInstance(): LogMaker {
        if (!self::$object instanceof self) self::$object = new self();
        return self::$object;
    }

    public function writeErrorLog(string $error, ?string $errorDetail): bool {
        if (empty($error)) return false;
        $date = date('d/m/Y H:i:s.u');
        $string = empty($errorDetail) ? '[' . $date . '] ' . $error . "\n"
                                      : '[' . $date . '] ' . $error . ":\n\t" . $errorDetail . "\n";
        return error_log($string, 3, self::ERROR_LOG_FILE);
    }

    public function writeLog(string $logName, string $log, ?string $logDetail): bool {
        $error = 'Error writing a log';
        if (empty($logName) || empty($log)) {
            if (empty($logName)) $errorDetail = 'Log destination is empty or null.';
            else $errorDetail = 'Log title is empty or null.';
            $this->writeErrorLog($error, $errorDetail);
            return false;
        }
        $file = fopen(self::LOG_DIR . $logName, 'w');
        if (empty($file)) {
            $errorDetail = 'Failed to open destination file.';
            $this->writeErrorLog($error, $errorDetail);
            return false;
        }
        $date = date('d/m/Y H:i:s.u');
        $string = empty($logDetail) ? '[' . $date . '] ' . $log . "\n"
                                    : '[' . $date . '] ' . $log . ":\n\t" . $logDetail . "\n";
        $result = fwrite($file, $string);
        fclose($file);
        if (!$result) {
            $errorDetail = 'Failed to write in the destination file.';
            $this->writeErrorLog($error, $errorDetail);
            return false;
        }
        return true;
    }
}