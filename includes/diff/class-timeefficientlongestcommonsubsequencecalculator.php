<?php
/*
 * A copy of the license applicable to this file is included in patchwork/includes/diff/LICENSE.
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 */
namespace PatchWork\Diff;

use \SplFixedArray;

final class TimeEfficientLongestCommonSubsequenceCalculator implements LongestCommonSubsequenceCalculator
{
    /**
     * {@inheritdoc}
     */
    public function calculate($from, $to)
    {
        $common     = array();
        $fromLength = count($from);
        $toLength   = count($to);
        $width      = $fromLength + 1;
        $matrix     = new SplFixedArray($width * ($toLength + 1));

        for ($i = 0; $i <= $fromLength; ++$i) {
            $matrix[$i] = 0;
        }

        for ($j = 0; $j <= $toLength; ++$j) {
            $matrix[$j * $width] = 0;
        }

        for ($i = 1; $i <= $fromLength; ++$i) {
            for ($j = 1; $j <= $toLength; ++$j) {
                $o          = ($j * $width) + $i;
                $matrix[$o] = max(
                    $matrix[$o - 1],
                    $matrix[$o - $width],
                    $from[$i - 1] === $to[$j - 1] ? $matrix[$o - $width - 1] + 1 : 0
                );
            }
        }

        $i = $fromLength;
        $j = $toLength;

        while ($i > 0 && $j > 0) {
            if ($from[$i - 1] === $to[$j - 1]) {
                $common[] = $from[$i - 1];
                --$i;
                --$j;
            } else {
                $o = ($j * $width) + $i;

                if ($matrix[$o - $width] > $matrix[$o - 1]) {
                    --$j;
                } else {
                    --$i;
                }
            }
        }

        return array_reverse($common);
    }
}
