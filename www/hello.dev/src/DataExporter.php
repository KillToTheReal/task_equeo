<?php

declare(strict_types=1);

namespace App;

use Ekvio\Integration\Contracts\Invoker;
use Ekvio\Integration\Contracts\Profiler;
use League\Flysystem\Filesystem;
use RuntimeException;

/**
 * Class DataExporter
 * @package App
 */
class DataExporter implements Invoker
{
    private Profiler $profiler;
    private Filesystem $sftpAdapter;
    private string $sftpReportPath;

    public function __construct(Profiler $profiler, Filesystem $sftpAdapter, string $sftpReportPath){
        $this->profiler = $profiler;
        $this->sftpAdapter = $sftpAdapter;
        $this->sftpReportPath = $sftpReportPath;
    }

    public function __invoke(array $arguments = [])
    {
        $content = $arguments['prev'];
        $this->profiler->profile(sprintf("Save data to %s", $this->sftpReportPath));
        if(!$this->sftpAdapter->put($this->sftpReportPath, $content)) {
            throw new RuntimeException('Some problems in put report in customer SFTP');
        }
    }

    public function name(): string
    {
        return 'Export data to SFTP';
    }
}