<?php

namespace FM\Feeder\Transport\Matcher;

interface MatcherInterface
{
    /**
     * @param  array  $files
     * @return string
     */
    public function match(array $files);

    /**
     * @return string
     */
    public function __toString();
}
