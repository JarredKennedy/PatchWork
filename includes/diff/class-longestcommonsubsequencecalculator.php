<?php
/*
 * A copy of the license applicable to this file is included in patchwork/includes/diff/LICENSE.
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 */
namespace PatchWork\Diff;

interface LongestCommonSubsequenceCalculator
{
    /**
     * Calculates the longest common subsequence of two arrays.
     */
    public function calculate($from, $to);
}
