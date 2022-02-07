<?php

const PROGRAM_MAIN_NAME = 'TestProject/main.php';
const CONF_FILE_NAME = './configuration/services.json';

function startMonitorService(): void {

    function checkIfIsAlreadyRunning(): bool {
        $pids = array_map('intval', array_filter(
            explode("\n", shell_exec('pgrep -f \'' . PROGRAM_MAIN_NAME . ' monitor\''))));
        return count($pids) > 1;
    }

    function monitorServices(): void {
        $logMaker = LogMaker::getLogMakerInstance();

        if (!file_exists(CONF_FILE_NAME)) throw new Exception('There are no services to monitor.');
        $stringJson = file_get_contents(CONF_FILE_NAME);
        if (empty($stringJson)) throw new Exception('There are no services to monitor.');
        $arrayJson = json_decode($stringJson);
        if (empty($arrayJson)) throw new Exception('There are no services to monitor.');

        foreach ($arrayJson as $serviceName => $serviceValues) {
            $serviceValuesArray = get_object_vars($serviceValues);
            if ($serviceValuesArray['type'] === 'systemd') {
                $output = shell_exec('systemctl is-active ' . $serviceName);
                $logDetail = "The status of the service is: $output.";
                if (!str_contains($output, 'active')) {
                    $serverName = $_SERVER['HOSTNAME'];
                    $to = $serviceValuesArray['email'];
                    $subject = "The $serviceName service is down on the $serverName server";
                    $message = 'Down service';
                    $headers = 'From: webmaster@example.com' . "\r\n" .
                               'Reply-To: webmaster@test.com' . "\r\n" .
                               'X-Mailer: PHP/' . phpversion();
                    mail($to, $subject, $message, $headers);
                    $logDetail .= " An email has been sent to $to";
                }

                if (!$logMaker->writeLog("$serviceName.log", "$serviceName log", $logDetail))
                    throw new Exception("An error occurred writing a log for the $serviceName service.");
            }
        }
    }

    if (checkIfIsAlreadyRunning())
        throw new Exception("The monitor service is already running, if you want to stop execute the " .
            "command 'sudo systemctl stop testProject.service'.");

    while (file_exists(CONF_FILE_NAME)) {
        monitorServices();
        sleep(60);
    }

}

function getMonitorFileName(): string {
    return __FILe__;
}

