<?php
namespace Moxio\CaptainHook\ESLint;

use CaptainHook\App\Config;
use CaptainHook\App\Console\IO;
use CaptainHook\App\Exception\ActionFailed;
use CaptainHook\App\Hook\Action;
use SebastianFeldmann\Cli\Processor\ProcOpen;
use SebastianFeldmann\Git\Repository;

class ESLintAction implements Action {
    public function execute(Config $config, IO $io, Repository $repository, Config\Action $action): void {
        $index_operator = $repository->getIndexOperator();
        $changed_js_files = array_merge(
            $index_operator->getStagedFilesOfType("js"),
            $index_operator->getStagedFilesOfType("mjs")
        );

        if (count($changed_js_files) === 0) {
            return;
        }

        $eslint_args = [
            "--quiet",      // ignores warnings, reports only errors
        ];
        foreach ($changed_js_files as $file) {
            $eslint_args[] = escapeshellarg($file);
        }
        $eslint_process = new ProcOpen();
        $eslint_bin = str_replace("/", DIRECTORY_SEPARATOR, "./node_modules/.bin/eslint");
        $eslint_result = $eslint_process->run($eslint_bin . " " . implode(" ", $eslint_args));

        if ($eslint_result->isSuccessful() === false) {
            if ($eslint_result->getCode() === 1) {
                $base_message = "ESLint found errors in files to be committed:";
                throw new ActionFailed($base_message . PHP_EOL . $eslint_result->getStdOut());
            } else {
                $base_message = "Failed to check files using ESLint:";
                throw new \RuntimeException($base_message . PHP_EOL . $eslint_result->getStdErr());
            }
        }
    }
}
