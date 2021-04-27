<?php

/*
 * Copyright (c) 2021 Konstantin Deryabin
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace ExpressLogger\Writer;

trait LogLevelTrait
{
    protected int $codeLevelMin = PHP_INT_MIN;
    protected int $codeLevelMax = PHP_INT_MAX;

    /**
     * @param int $codeLevelMin
     * @return $this
     */
    public function setCodeLevelMin(int $codeLevelMin)
    {
        $this->codeLevelMin = $codeLevelMin;
        return $this;
    }

    /**
     * @param int $codeLevelMax
     * @return $this
     */
    public function setCodeLevelMax(int $codeLevelMax)
    {
        $this->codeLevelMax = $codeLevelMax;
        return $this;
    }

    /**
     * @param int $codeLevel
     * @return bool
     */
    public function canLog(int $codeLevel): bool
    {
        return $codeLevel >= $this->codeLevelMin && $codeLevel <= $this->codeLevelMax;
    }
}
