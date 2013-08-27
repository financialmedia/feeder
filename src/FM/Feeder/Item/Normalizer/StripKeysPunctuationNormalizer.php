<?php

namespace FM\Feeder\Item\Normalizer;

use Symfony\Component\HttpFoundation\ParameterBag;

class StripKeysPunctuationNormalizer implements NormalizerInterface
{
    protected $punctuation;

    public function __construct(array $punctuation = array('.', ',', ':', ';'))
    {
        $this->punctuation = $punctuation;
    }

    public function normalize(ParameterBag $item)
    {
        $parameters = $item->all();
        $this->stripKeysPunctuation($parameters);
        $item->replace($parameters);
    }

    protected function stripKeysPunctuation(array &$arr)
    {
        $new = [];

        foreach ($arr as $key => &$value) {
            if (is_array($value)) {
                $this->stripKeysPunctuation($value);
            }

            $new[$this->strip($key)] = $value;
        }

        $arr = $new;
    }

    /**
     * @param  string $string
     * @return string
     */
    protected function strip($string)
    {
        $regex = sprintf(
            '/[%s+]/',
            implode(
                '',
                array_map(
                    function($value) {
                        return preg_quote($value, '/');
                    },
                    $this->punctuation
                )
            )
        );

        return preg_replace($regex, '', $string);
    }
}
