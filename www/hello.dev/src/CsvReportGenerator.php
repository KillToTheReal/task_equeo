<?php

declare(strict_types=1);

namespace App;

use Ekvio\Integration\Contracts\Invoker;
use Ekvio\Integration\Contracts\Profiler;
use RuntimeException;

/**
 * Class CSVReportIntegration
 * @package App
 */
class CsvReportGenerator implements Invoker
{
    private const NAME = 'User with personal assignments for CSV converter';
    private Profiler $profiler;
    private array $csvHeader;

    /**
     * CSVReportIntegration constructor.
     * @param Profiler $profiler
     * @param array $csvHeader
     */
    public function __construct(Profiler $profiler, array $csvHeader)
    {
        $this->profiler = $profiler;
        $this->csvHeader = $csvHeader;
    }

    public function __invoke(array $arguments = [])
    {
        $accounts = $arguments['prev'];
        if(empty($accounts)) {
            throw new RuntimeException('Accounts not found');
        }
        return $this->exportCsv(array_merge([$this->csvHeader], $accounts));
    }

    /**
     * @param array $data
     * @param string $delimiter
     */
    private function exportCsv(array $data, string $delimiter = ";"): string
    {
        $this->profiler->profile("Constructing CSV file...");
        $arrayCsv = [];

        /** @var array<int> $line */
        foreach ($data as $line) {
            $arrayCsv[] = implode($delimiter, $line);
        }
        return implode("\n", $arrayCsv);
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return self::NAME;
    }
}
