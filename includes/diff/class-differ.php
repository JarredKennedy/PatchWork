<?php
/*
 * This class is a WordPress and PHP 5 compatible adapation of the Differ class in Sebastian Bergmann's
 * Diff package. A copy of the license applicable to this file is included in patchwork/includes/diff/LICENSE.
 * 
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 */

namespace PatchWork\Diff;

use PatchWork\Differ as Differ_Spec;
use PatchWork\Diff;
use PatchWork\Types\Diff_OP;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Differ implements Differ_Spec {

	const OLD                     = 0;

    const ADDED                   = 1;

    const REMOVED                 = 2;

    const DIFF_LINE_END_WARNING   = 3;

    const NO_LINE_END_EOF_WARNING = 4;

    /**
     * Returns the diff between two arrays.
     *
     * @param array	$from
     * @param array	$to
	 * 
	 * @return PatchWork\Diff
     */
    public function diff( $from, $to ) {
        list( $from, $to, $start, $end ) = self::getArrayDiffParted( $from, $to );

		$lcs = $this->selectLcsImplementation( $from, $to );

        $common = $lcs->calculate( array_values( $from ), array_values( $to ) );
        $diff   = [];

        reset($from);
		reset($to);
		
		$original_line_number = count( $start ) + 1;
		$patched_line_number = $original_line_number;

        foreach ( $common as $token ) {
            while (($fromToken = reset($from)) !== $token) {
                $diff[] = array(
					array_shift($from),
					self::REMOVED,
					$original_line_number,
					$patched_line_number
				);

				$original_line_number++;
            }

            while (($toToken = reset($to)) !== $token) {
                $diff[] = array(
					array_shift($to),
					self::ADDED,
					$original_line_number,
					$patched_line_number
				);

				$patched_line_number++;
            }
			
			$original_line_number++;
			$patched_line_number++;

            array_shift($from);
            array_shift($to);
        }

        while (($token = array_shift($from)) !== null) {
            $diff[] = array(
				$token,
				self::REMOVED,
				$original_line_number,
				$patched_line_number
			);

			$original_line_number++;
        }

        while (($token = array_shift($to)) !== null) {
			$diff[] = array(
				$token,
				self::ADDED,
				$original_line_number,
				$patched_line_number
			);
			
			$patched_line_number++;
        }

        return $this->condense_diff( $diff );
    }

    /**
     * Produces a PatchWork\Diff object. This representation works better for completely
     * replaced files that may be added to a patch file.
     * 
     * This method is not part of the sebastian/diff package, submit issues to the PatchWork repo.
     */
    private function condense_diff( $diff ) {
        $condensed = new Diff();

        reset( $diff );

        while ( ( $line = array_shift( $diff ) ) ) {
            $op = new Diff_OP();
            $op->original_line_start = $line[2];
            $op->patched_line_start = $line[3];
            $op->original_lines_effected = 0;
            $op->patched_lines_effected = 0;

            $line_number_o = $op->original_line_start;
            $line_number_p = $op->patched_line_start;

            if ( $line[1] === self::ADDED ) {
                $op->patched[] = $line[0];
                $op->patched_lines_effected++;
            } else {
                $op->original[] = $line[0];
                $op->original_lines_effected++;
            }

            $mod_o = 1;
            $mod_p = 1;

            // Check for changes to consecutive lines. They can be added to the same diff
            // block for a smaller patch file and faster file patching.
            $next = current( $diff );
            while ( $next && (
                ( $next[1] === self::ADDED && ($next[3] == $line_number_p + $mod_p - 1 || $next[3] == $line_number_p + $mod_p) )
                || ( $next[1] === self::REMOVED && ($next[2] == $line_number_o + $mod_o - 1 || $next[2] == $line_number_o + $mod_o) )
            ) ) {

                if ( $next[1] === self::ADDED ) {
                    $op->patched[] = $next[0];
                    $op->patched_lines_effected++;
                    
                    // If it was equal to the original line, don't increment line modifier.
                    if ( $next[3] != $line_number_p + $mod_p - 1 ) {
                        $mod_p++;
                    }
                } else {
                    $op->original[] = $next[0];
                    $op->original_lines_effected++;
                    if ( $next[2] != $line_number_o + $mod_o - 1 ) {
                        $mod_o++;
                    }
                }

                array_shift( $diff );
                $next = current( $diff );
            }

            $condensed->add_op( $op );
        }

        return $condensed;
    }

    /**
     * Checks if input is string, if so it will split it line-by-line.
     */
    private function splitStringByLines($input)
    {
        return preg_split('/(.*\R)/', $input, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
    }

    private function selectLcsImplementation($from, $to)
    {
        // We do not want to use the time-efficient implementation if its memory
        // footprint will probably exceed this value. Note that the footprint
        // calculation is only an estimation for the matrix and the LCS method
        // will typically allocate a bit more memory than this.
        $memoryLimit = 100 * 1024 * 1024;

        if ($this->calculateEstimatedFootprint($from, $to) > $memoryLimit) {
            return new MemoryEfficientLongestCommonSubsequenceCalculator;
        }

        return new TimeEfficientLongestCommonSubsequenceCalculator;
    }

    /**
     * Calculates the estimated memory footprint for the DP-based method.
     *
     * @return float|int
     */
    private function calculateEstimatedFootprint($from, $to)
    {
        $itemSize = PHP_INT_SIZE === 4 ? 76 : 144;

        return $itemSize * min(count($from), count($to)) ** 2;
    }

    /**
     * Returns true if line ends don't match in a diff.
     */
    private function detectUnmatchedLineEndings($diff)
    {
        $newLineBreaks = array('' => true);
        $oldLineBreaks = array('' => true);

        foreach ($diff as $entry) {
            if (self::OLD === $entry[1]) {
                $ln                 = $this->getLinebreak($entry[0]);
                $oldLineBreaks[$ln] = true;
                $newLineBreaks[$ln] = true;
            } elseif (self::ADDED === $entry[1]) {
                $newLineBreaks[$this->getLinebreak($entry[0])] = true;
            } elseif (self::REMOVED === $entry[1]) {
                $oldLineBreaks[$this->getLinebreak($entry[0])] = true;
            }
        }

        // if either input or output is a single line without breaks than no warning should be raised
        if (array('' => true) === $newLineBreaks || array('' => true) === $oldLineBreaks) {
            return false;
        }

        // two way compare
        foreach ($newLineBreaks as $break => $set) {
            if (!isset($oldLineBreaks[$break])) {
                return true;
            }
        }

        foreach ($oldLineBreaks as $break => $set) {
            if (!isset($newLineBreaks[$break])) {
                return true;
            }
        }

        return false;
    }

    private function getLinebreak($line)
    {
        if (!is_string($line)) {
            return '';
        }

        $lc = substr($line, -1);

        if ("\r" === $lc) {
            return "\r";
        }

        if ("\n" !== $lc) {
            return '';
        }

        if ("\r\n" === substr($line, -2)) {
            return "\r\n";
        }

        return "\n";
    }

	/**
	 * Takes the original and patched lines and finds the start sequence which is common
	 * to both sets of lines and the end sequence which is common to both sets of lines.
	 * 
	 * Returns an array with the element at index 0 being the possibly adjusted
	 * original lines, the element at index 1 being the possibly adjusted patched
	 * lines, the element at index 2 being the start sequence, and the element at
	 * index 3 being the end sequence.
	 */
    private static function getArrayDiffParted(&$from, &$to)
    {
        $start = array();
        $end   = array();

        reset($to);

        foreach ($from as $k => $v) {
            $toK = key($to);

            if ($toK === $k && $v === $to[$k]) {
                $start[$k] = $v;

                unset($from[$k], $to[$k]);
            } else {
                break;
            }
        }

        end($from);
        end($to);

        do {
            $fromK = key($from);
            $toK   = key($to);

            if (null === $fromK || null === $toK || current($from) !== current($to)) {
                break;
            }

            prev($from);
            prev($to);

            $end = array($fromK => $from[$fromK]) + $end;
            unset($from[$fromK], $to[$toK]);
        } while (true);

        return array($from, $to, $start, $end);
    }

}