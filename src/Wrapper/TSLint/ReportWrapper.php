<?php

namespace Cheppers\LintReport\Wrapper\TSLint;

use Cheppers\LintReport\ReportWrapperInterface;

/**
 * Class ReportWrapper.
 *
 * @package Cheppers\LintReport\Wrapper\TSLint
 */
class ReportWrapper implements ReportWrapperInterface
{
    /**
     * @var array
     */
    protected $report = [];

    /**
     * @var array
     */
    protected $reportInternal = [];

    /**
     * @var int|null
     */
    protected $numOfErrors = null;

    /**
     * @var int|null
     */
    protected $numOfWarnings = null;

    /**
     * {@inheritdoc}
     */
    public function __construct(array $report = null)
    {
        if ($report !== null) {
            $this->setReport($report);
        }
    }

    /**
     * @return array
     */
    public function getReport()
    {
        return $this->report;
    }

    /**
     * @param array $report
     *
     * @return $this
     */
    public function setReport($report)
    {
        $this->report = $report;
        $this->numOfErrors = null;
        $this->numOfWarnings = null;

        foreach ($report as $document) {
            foreach ($document['failures'] as $failure) {
                $failure += ['severity' => 'error'];
                $filePath = $failure['name'];
                if (!isset($this->reportInternal[$filePath])) {
                    $this->reportInternal[$filePath] = [
                        'filePath' => $filePath,
                        'errors' => 0,
                        'warnings' => 0,
                        'stats' => [],
                        'failures' => [],
                    ];
                }

                $this->reportInternal[$filePath]['failures'][] = $failure;
                $this->reportInternal[$filePath]['errors']++;
                $this->numOfErrors++;
            }
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function countFiles()
    {
        return count($this->reportInternal);
    }

    /**
     * {@inheritdoc}
     */
    public function yieldFiles()
    {
        foreach ($this->reportInternal as $file) {
            yield $file['filePath'] => new FileWrapper($file);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function numOfErrors()
    {
        return $this->numOfErrors;
    }

    /**
     * {@inheritdoc}
     */
    public function numOfWarnings()
    {
        return $this->numOfWarnings;
    }

    /**
     * @return string
     */
    public function highestSeverity()
    {
        if ($this->numOfErrors()) {
            return ReportWrapperInterface::SEVERITY_ERROR;
        }

        if ($this->numOfWarnings()) {
            return ReportWrapperInterface::SEVERITY_WARNING;
        }

        return ReportWrapperInterface::SEVERITY_OK;
    }
}
