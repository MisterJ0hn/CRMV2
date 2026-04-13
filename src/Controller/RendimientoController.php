<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/rendimiento")
 */
class RendimientoController extends AbstractController
{
    /**
     * @Route("/", name="rendimiento_index", methods={"GET"})
     */
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('view','rendimiento');

        $cpu = $this->getCpuStatus();
        $memory = $this->getMemoryStatus();
        $disk = $this->getDiskStatus();
        $uptime = $this->getUptimeStatus();

        return $this->render('rendimiento/index.html.twig', [
            'pagina' => 'Rendimiento del servidor',
            'cpu' => $cpu,
            'memory' => $memory,
            'disk' => $disk,
            'uptime' => $uptime,
        ]);
    }

    /**
     * @Route("/data", name="rendimiento_data", methods={"GET"})
     */
    public function data(): Response
    {
        return $this->json([
            'cpu' => $this->getCpuStatus(),
            'memory' => $this->getMemoryStatus(),
            'disk' => $this->getDiskStatus(),
            'uptime' => $this->getUptimeStatus(),
        ]);
    }

    private function getCpuStatus(): array
    {
        $load = null;
        $cores = null;
        $description = 'No disponible';
        $coresDetail = [];

        if (function_exists('sys_getloadavg')) {
            $loads = sys_getloadavg();
            if ($loads !== false) {
                $load = $loads[0];
                $description = sprintf('Carga 1 min: %.2f', $load);
            }
        }

        if ($cores === null) {
            if (is_file('/proc/cpuinfo')) {
                $cpuinfo = file_get_contents('/proc/cpuinfo');
                preg_match_all('/^processor\s*:/mi', $cpuinfo, $matches);
                $cores = count($matches[0]);
            } elseif (stripos(PHP_OS_FAMILY, 'Windows') !== false) {
                $output = null;
                @exec('wmic cpu get NumberOfCores /value', $output);
                foreach ($output as $line) {
                    if (preg_match('/NumberOfCores=(\d+)/i', $line, $matches)) {
                        $cores = (int) $matches[1];
                        break;
                    }
                }
            }
        }

        $coresDetail = $this->getCpuCoreUsage();

        return [
            'load' => $load !== null ? sprintf('%.2f', $load) : 'N/D',
            'cores' => $cores ?? 'N/D',
            'description' => $description,
            'cores_detail' => $coresDetail,
        ];
    }

    private function getMemoryStatus(): array
    {
        $total = null;
        $free = null;
        $used = null;
        $usagePercent = null;
        $description = 'No disponible';
        $topProcesses = [];

        if (is_file('/proc/meminfo')) {
            $meminfo = file('/proc/meminfo', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $data = [];
            foreach ($meminfo as $line) {
                if (preg_match('/^([A-Za-z0-9()]+):\s+(\d+)\s+kB$/', $line, $matches)) {
                    $data[$matches[1]] = (int) $matches[2];
                }
            }

            if (isset($data['MemTotal'], $data['MemAvailable'])) {
                $total = $data['MemTotal'] * 1024;
                $free = $data['MemAvailable'] * 1024;
                $used = $total - $free;
            }
        } elseif (stripos(PHP_OS_FAMILY, 'Windows') !== false) {
            $output = null;
            @exec('wmic OS get FreePhysicalMemory,TotalVisibleMemorySize /value', $output);
            $values = [];
            foreach ($output as $line) {
                if (preg_match('/^([^=]+)=(\d+)$/', $line, $matches)) {
                    $values[$matches[1]] = (int) $matches[2];
                }
            }
            if (isset($values['TotalVisibleMemorySize'], $values['FreePhysicalMemory'])) {
                $total = $values['TotalVisibleMemorySize'] * 1024;
                $free = $values['FreePhysicalMemory'] * 1024;
                $used = $total - $free;
            }
        }

        if ($total !== null && $free !== null) {
            $usagePercent = $total > 0 ? round(($used / $total) * 100, 2) : null;
            $description = sprintf('Uso memoria: %s / %s (%s%%)', $this->formatBytes($used), $this->formatBytes($total), $usagePercent);
        }

        $topProcesses = $this->getMemoryTopProcesses();

        return [
            'total' => $total !== null ? $this->formatBytes($total) : 'N/D',
            'free' => $free !== null ? $this->formatBytes($free) : 'N/D',
            'used' => $used !== null ? $this->formatBytes($used) : 'N/D',
            'percent' => $usagePercent !== null ? $usagePercent : 'N/D',
            'description' => $description,
            'top_processes' => $topProcesses,
        ];
    }

    private function getCpuCoreUsage(): array
    {
        if (!is_file('/proc/stat')) {
            return [];
        }

        $first = $this->readCpuStat();
        if (empty($first)) {
            return [];
        }

        usleep(100000);
        $second = $this->readCpuStat();
        if (empty($second)) {
            return [];
        }

        $details = [];
        foreach ($second as $core => $data2) {
            if (!isset($first[$core])) {
                continue;
            }

            $data1 = $first[$core];
            $total1 = array_sum($data1);
            $total2 = array_sum($data2);
            $idle1 = ($data1['idle'] ?? 0) + ($data1['iowait'] ?? 0);
            $idle2 = ($data2['idle'] ?? 0) + ($data2['iowait'] ?? 0);
            $deltaTotal = $total2 - $total1;
            $deltaIdle = $idle2 - $idle1;

            if ($deltaTotal <= 0) {
                continue;
            }

            $usage = 100 * ($deltaTotal - $deltaIdle) / $deltaTotal;
            $details[] = [
                'core' => $core,
                'usage' => round($usage, 2),
            ];
        }

        return $details;
    }

    private function readCpuStat(): array
    {
        $lines = file('/proc/stat', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $stats = [];

        foreach ($lines as $line) {
            if (!preg_match('/^(cpu[0-9]+)\s+(.+)$/', $line, $matches)) {
                continue;
            }

            $values = preg_split('/\s+/', trim($matches[2]));
            $stats[$matches[1]] = [
                'user' => isset($values[0]) ? (int) $values[0] : 0,
                'nice' => isset($values[1]) ? (int) $values[1] : 0,
                'system' => isset($values[2]) ? (int) $values[2] : 0,
                'idle' => isset($values[3]) ? (int) $values[3] : 0,
                'iowait' => isset($values[4]) ? (int) $values[4] : 0,
                'irq' => isset($values[5]) ? (int) $values[5] : 0,
                'softirq' => isset($values[6]) ? (int) $values[6] : 0,
                'steal' => isset($values[7]) ? (int) $values[7] : 0,
                'guest' => isset($values[8]) ? (int) $values[8] : 0,
                'guest_nice' => isset($values[9]) ? (int) $values[9] : 0,
            ];
        }

        return $stats;
    }

    private function getMemoryTopProcesses(): array
    {
        $processes = [];

        if (stripos(PHP_OS_FAMILY, 'Windows') !== false) {
            $output = null;
            @exec('tasklist /FO CSV /NH', $output);
            foreach ($output as $line) {
                $line = trim($line);
                if ($line === '') {
                    continue;
                }

                $parts = str_getcsv($line);
                if (count($parts) < 5) {
                    continue;
                }

                $processes[] = [
                    'pid' => (int) $parts[1],
                    'command' => $parts[0],
                    'mem' => $parts[4],
                ];

                if (count($processes) >= 10) {
                    break;
                }
            }

            return $processes;
        }

        $output = null;
        @exec('ps -eo pid,comm,rss --sort=-rss --no-headers 2>/dev/null', $output);
        foreach ($output as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            if (!preg_match('/^(\d+)\s+(\S+)\s+(\d+)$/', $line, $matches)) {
                continue;
            }

            $processes[] = [
                'pid' => (int) $matches[1],
                'command' => $matches[2],
                'mem' => sprintf('%.2f MB', $matches[3] / 1024),
            ];

            if (count($processes) >= 10) {
                break;
            }
        }

        return $processes;
    }

    private function getDiskStatus(): array
    {
        $path = getcwd();
        $total = disk_total_space($path);
        $free = disk_free_space($path);
        $used = null;
        $usagePercent = null;

        if ($total !== false && $free !== false) {
            $used = $total - $free;
            $usagePercent = $total > 0 ? round(($used / $total) * 100, 2) : null;
        }

        return [
            'path' => $path,
            'total' => $total !== false ? $this->formatBytes($total) : 'N/D',
            'free' => $free !== false ? $this->formatBytes($free) : 'N/D',
            'used' => $used !== null ? $this->formatBytes($used) : 'N/D',
            'percent' => $usagePercent !== null ? $usagePercent : 'N/D',
        ];
    }

    private function getUptimeStatus(): array
    {
        $uptime = 'N/D';
        $boot = 'N/D';

        if (is_file('/proc/uptime')) {
            $parts = explode(' ', trim(file_get_contents('/proc/uptime')));
            if (isset($parts[0])) {
                $seconds = (int) floor($parts[0]);
                $uptime = $this->formatInterval($seconds);
                $boot = (new \DateTime())->sub(new \DateInterval('PT' . $seconds . 'S'))->format('Y-m-d H:i:s');
            }
        } elseif (stripos(PHP_OS_FAMILY, 'Windows') !== false) {
            $output = null;
            @exec('wmic os get LastBootUpTime /value', $output);
            foreach ($output as $line) {
                if (preg_match('/LastBootUpTime\s*=\s*([0-9]+)\.[0-9]+\+/', $line, $matches)) {
                    $bootTime = \DateTime::createFromFormat('YmdHis', substr($matches[1], 0, 14));
                    if ($bootTime !== false) {
                        $boot = $bootTime->format('Y-m-d H:i:s');
                        $seconds = time() - $bootTime->getTimestamp();
                        $uptime = $this->formatInterval($seconds);
                    }
                    break;
                }
            }
        }

        return [
            'uptime' => $uptime,
            'boot' => $boot,
        ];
    }

    private function formatInterval(int $seconds): string
    {
        $days = floor($seconds / 86400);
        $seconds %= 86400;
        $hours = floor($seconds / 3600);
        $seconds %= 3600;
        $minutes = floor($seconds / 60);
        $seconds %= 60;

        $parts = [];
        if ($days > 0) {
            $parts[] = $days . 'd';
        }
        if ($hours > 0) {
            $parts[] = $hours . 'h';
        }
        if ($minutes > 0) {
            $parts[] = $minutes . 'm';
        }
        $parts[] = $seconds . 's';

        return implode(' ', $parts);
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return sprintf('%.2f %s', $bytes, $units[$i]);
    }
}
