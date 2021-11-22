<?php

declare(strict_types=1);

namespace Codeception\Specify;

use PHPUnit\TextUI\DefaultResultPrinter;

if (!class_exists(DefaultResultPrinter::class)) {
    class_alias(\PHPUnit\TextUI\ResultPrinter::class, DefaultResultPrinter::class);
}

class ResultPrinter extends DefaultResultPrinter
{
    protected function writeProgress(string $progress): void
    {
        $this->write($progress);
        ++$this->column;
        ++$this->numTestsRun;

        if ($this->column === $this->maxColumn) {
            $this->writeNewLine();
        }
    }
}
