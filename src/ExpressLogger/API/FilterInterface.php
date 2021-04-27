<?php

namespace ExpressLogger\API;

interface FilterInterface
{
    /**
     * @param array $data Log data to filter.
     * @return array|false Filtered data or FALSE if data does not pass a filter
     */
    public function filter(array $data);
}
