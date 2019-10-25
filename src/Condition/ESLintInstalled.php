<?php
namespace Moxio\CaptainHook\ESLint\Condition;

use CaptainHook\App\Console\IO;
use CaptainHook\App\Hook\Condition;
use SebastianFeldmann\Git\Repository;

class ESLintInstalled implements Condition {
    public function isTrue(IO $io, Repository $repository): bool {
        $eslint_bin = str_replace("/", DIRECTORY_SEPARATOR, "./node_modules/.bin/eslint");
        return is_file($eslint_bin);
    }
}
