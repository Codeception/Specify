<?php

namespace Codeception\Specify;

class ResultPrinter extends \PHPUnit\TextUI\ResultPrinter
{
    /**
     * @param string $progress
     */
    protected function writeProgress($progress)
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