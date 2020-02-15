<?php

namespace Codeception\Specify;

use PHPUnit\TextUI\DefaultResultPrinter;

if (!class_exists(DefaultResultPrinter::class)) {
    class_alias(\PHPUnit\TextUI\ResultPrinter::class, DefaultResultPrinter::class);
}

class ResultPrinter extends DefaultResultPrinter
{
    /**
     * @param string $progress
     */
    protected function writeProgress(string $progress) : void
    {
        $this->write($progress);
        $this->column++;
        $this->numTestsRun++;

        if ($this->column == $this->maxColumn || $this->numTestsRun == $this->numTests) {
            if ($this->column == $this->maxColumn) {
                $this->writeNewLine();
            }
        }
    }


}
