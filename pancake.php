<?php declare(strict_types=1);

class ImpossibleException extends Exception {};

$opt_run_tests = false;

/**
 * Return the number of flips taken to turn all pancakes
 * happy side up or null if impossible
 */
function solveLine(string $input): ?int {
    [$s, $k] = explode(' ', $input);
    $n = strlen($s);

    $k = (int)$k; // spatula size
    $goal = str_repeat("+", $n);

    $flips = 0;
    // I'm sure this all could be done more efficiently
    // operating on raw bits treating + as 1 and - as 0
    if ($s === $goal) {
        return $flips;
    }

    try {
        return flipR($s, $k, $flips);
    } catch (ImpossibleException $e) {
        return null;
    }
}

/**
 * Recursive function - tail call format although php doesn't optimize that
 * @throws ImpossibleException
 */
function flipR(string $s, int $k, int $flips): int {
    // base case
    if (strlen($s) < $k) {
        if ($s === str_repeat("+", strlen($s))) {
            return $flips;
        } else {
            // I don't really like using exceptions as part of control flow
            // but not sure there's a better way to bail out of the
            // recursive calls
            throw new ImpossibleException("Impossible");
        }
    }

    // check for "jump ahead condition"
    if (substr($s, 0, $k) === str_repeat("+", $k)) {
        $s = substr($s, $k);
    } else {
        if ($s[0] === '-') {
            // flip k pancakes starting from the leftmost edge
            $s = flip(substr($s, 0, $k)) . substr($s, $k);
            $flips++;
        }

        // pop the leading pancake which is known to be +
        $s = substr($s, 1);
    }

    return flipR($s, $k, $flips);
}

/**
 * @throws RuntimeException
 */
function flip(string $s): string {
    $flipped = '';
    // this really should be bits
    for ($i = 0; $i < strlen($s); $i++) {
        if ($s[$i] === '+') {
            $flipped .= '-';
        } elseif ($s[$i] === '-') {
            $flipped .= '+';
        } else {
            throw new RuntimeException("Unrecognized char " . $s[$i] . " in input");
        }
    }

    return $flipped;
}

function logResult(int $casenum, ?int $flips) {
    if (is_null($flips)) {
        echo sprintf("Case #%d: IMPOSSIBLE\n", $casenum);
        return;
    }
    echo sprintf("Case #%d: %d\n", $casenum, $flips);
}

function test() {
    $cases = [
        ["---+-++- 3", 3],
        ["+++++ 4", 0],
        ["-+-+- 4", null],
    ];

    foreach ($cases as $i => [$in, $e]) {
        $res = solveLine($in);
        if ($e === $res) {
            echo "Test $i: PASS\n";
        } else {
            echo "Test $i: Failed asserting expected $e equals $res\n";
        }
    }
}

function main(array $argv) {
    if (!isset($argv[1])) {
        fwrite(STDERR, "No input file given\n");
        exit(1);
    }

    $fh = fopen($argv[1], 'r');

    $cases = (int)(fgets($fh));

    for ($i = 1; $i <= $cases; $i++) { // O(T)
        $l = fgets($fh);
        if ($l < 0) {
            fwrite(STDERR, "Unable to read input file on line $i\n");
            exit(1);
        }

        $flips = solveLine($l);
        logResult($i, $flips);
    } // end loop over input lines

    fclose($fh);
}

$shortopts = 't';
$longopts  = [];

$opts = getopt($shortopts, $longopts);
foreach ($opts as $opt => $val) {
    switch ($opt) {
        case 't':
            $opt_run_tests = true;
            break;
        default:
            fwrite(STDERR, "Unrecognized argument $opt\n");
            exit(1);
    }
}

if ($opt_run_tests) {
    test();
} else {
    main($argv);
}
