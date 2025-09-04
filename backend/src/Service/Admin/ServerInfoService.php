<?php

namespace Fileknight\Service\Admin;

use Doctrine\DBAL\Connection;

class ServerInfoService
{
    public function getServerInfo(): array
    {
        $timezone = date_default_timezone_get();
        $hostname = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? gethostname();
        $ip = trim(file_get_contents('http://checkip.amazonaws.com/'));
        $containerIp = gethostbyname(gethostname());
        $phpVersion = phpversion();
        $webServer = $_SERVER['SERVER_SOFTWARE'] ?? 'PHP-FPM';

        return [
            'timezone' => $timezone,
            'hostname' => $hostname,
            'ip' => $ip,
            'containerIp' => $containerIp,
            'phpVersion' => $phpVersion,
            'webServer' => $webServer,
        ];
    }

    public function getDiskUsage(): array
    {
        $output = shell_exec("df -h / | awk 'NR==2 {print $2, $3, $4, $5}'");
        if (!$output) {
            return ['total' => null, 'used' => null, 'free' => null, 'percent' => null,];
        }

        [$total, $used, $free, $percent] = preg_split('/\s+/', trim($output));

        return ['total' => $total, 'used' => $used, 'free' => $free, 'percent' => $percent,];
    }

    public function getMemoryUsage(): array
    {
        $memoryTotal = 'N/A';
        $memoryUsed = 'N/A';
        if (file_exists('/proc/meminfo')) {
            $memInfo = file_get_contents('/proc/meminfo');
            preg_match('/MemTotal:\s+(\d+) kB/', $memInfo, $matchesTotal);
            preg_match('/MemAvailable:\s+(\d+) kB/', $memInfo, $matchesAvail);
            if ($matchesTotal && $matchesAvail) {
                $memoryTotal = round($matchesTotal[1] / 1024 / 1024, 2) . ' GB';
                $memoryUsed = round(($matchesTotal[1] - $matchesAvail[1]) / 1024 / 1024, 2) . ' GB';
            }
        }

        return ['total' => $memoryTotal, 'used' => $memoryUsed];
    }

    public function getCPUCores(): int
    {
        $cpuCores = 1; // default
        if (function_exists('shell_exec')) {
            $output = trim(shell_exec('nproc 2>/dev/null'));
            if ($output) {
                $cpuCores = intval($output);
            }
        }

        return $cpuCores;
    }

    public function getUptime(): string
    {
        $uptime = 'N/A';
        if (file_exists('/proc/uptime')) {
            $uptimeSeconds = floatval(file_get_contents('/proc/uptime'));
            $days = floor($uptimeSeconds / 86400);
            $hours = floor(($uptimeSeconds % 86400) / 3600);
            $minutes = floor(($uptimeSeconds % 3600) / 60);
            $uptime = "{$days}d {$hours}h {$minutes}m";
        }

        return $uptime;
    }

    public function getDatabaseInfo(Connection $connection): array
    {
        try {
            return ['name' => $connection->getDatabasePlatform()->getName(), 'version' => $connection->fetchOne('SELECT VERSION()')];
        } catch (\Exception $e) {
            return ['name' => 'N/A', 'version' => ''];
        }
    }
}
