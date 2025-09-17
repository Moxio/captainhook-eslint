<?php
namespace Moxio\CaptainHook\ESLint\Test;

use CaptainHook\App\Config;
use CaptainHook\App\Console\IO\NullIO;
use CaptainHook\App\Exception\ActionFailed;
use Moxio\CaptainHook\ESLint\ESLintAction;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SebastianFeldmann\Cli\Command\Result;
use SebastianFeldmann\Cli\Processor;
use SebastianFeldmann\Git\Operator\Index;
use SebastianFeldmann\Git\Repository;

class ESLintActionTest extends TestCase {
    /** @var MockObject|Processor */
    private $processor;
    /** @var ESLintAction */
    private $eslint_action;
    /** @var MockObject|Config */
    private $config;
    /** @var MockObject|Repository */
    private $repository;
    /** @var MockObject|Index */
    private $index_operator;

    protected function setUp(): void {
        $this->processor = $this->createMock(Processor::class);
        $this->eslint_action = new ESLintAction($this->processor);

        $this->config = $this->createMock(Config::class);
        $this->repository = $this->createMock(Repository::class);
        $this->index_operator = $this->createMock(Index::class);
        $this->repository->expects($this->any())
            ->method("getIndexOperator")
            ->willReturn($this->index_operator);
    }

    public function testReturnsWhenNoFilesOfRelevantTypesWereChanged(): void {
        $this->index_operator->expects($this->any())
            ->method("getStagedFilesOfType")
            ->willReturnMap([
                [ "js", [] ],
                [ "mjs", [] ],
            ]);

        $io = new NullIO();
        $config_action = new Config\Action(ESLintAction::class);

        $this->expectNotToPerformAssertions();
        $this->eslint_action->execute($this->config, $io, $this->repository, $config_action);
    }

    public function testRunsESLintOnChangedFilesOfRelevantTypes(): void {
        $this->index_operator->expects($this->any())
            ->method("getStagedFilesOfType")
            ->willReturnMap([
                [ "js", [ "foo.js" ] ],
                [ "mjs", [ "dir/bar.mjs" ] ],
            ]);

        $io = new NullIO();
        $config_action = new Config\Action(ESLintAction::class);

        $ds = DIRECTORY_SEPARATOR;
        $expected_cmd = ".{$ds}node_modules{$ds}.bin{$ds}eslint --quiet " . escapeshellarg("foo.js") . " " . escapeshellarg("dir/bar.mjs");
        $this->processor->expects($this->once())
            ->method("run")
            ->with($expected_cmd)
            ->willReturn(new Result($expected_cmd, 0));

        $this->eslint_action->execute($this->config, $io, $this->repository, $config_action);
    }

    public function testThrowsActionFailedIfESLintFoundErrors(): void {
        $this->index_operator->expects($this->any())
            ->method("getStagedFilesOfType")
            ->willReturnMap([
                [ "js", [ "foo.js" ] ],
                [ "mjs", [ "dir/bar.mjs" ] ],
            ]);

        $io = new NullIO();
        $config_action = new Config\Action(ESLintAction::class);

        $ds = DIRECTORY_SEPARATOR;
        $expected_cmd = ".{$ds}node_modules{$ds}.bin{$ds}eslint --quiet " . escapeshellarg("foo.js") . " " . escapeshellarg("dir/bar.mjs");
        $this->processor->expects($this->once())
            ->method("run")
            ->with($expected_cmd)
            ->willReturn(new Result($expected_cmd, 1, "Some error in foo.js"));

        $this->expectException(ActionFailed::class);
        $this->eslint_action->execute($this->config, $io, $this->repository, $config_action);
    }

    public function testThrowsRuntimeExceptionIfESLintFailedToRun(): void {
        $this->index_operator->expects($this->any())
            ->method("getStagedFilesOfType")
            ->willReturnMap([
                [ "js", [ "foo.js" ] ],
                [ "mjs", [ "dir/bar.mjs" ] ],
            ]);

        $io = new NullIO();
        $config_action = new Config\Action(ESLintAction::class);

        $ds = DIRECTORY_SEPARATOR;
        $expected_cmd = ".{$ds}node_modules{$ds}.bin{$ds}eslint --quiet " . escapeshellarg("foo.js") . " " . escapeshellarg("dir/bar.mjs");
        $this->processor->expects($this->once())
            ->method("run")
            ->with($expected_cmd)
            ->willReturn(new Result($expected_cmd, 2, "", "ESLint configuration is invalid"));

        $this->expectException(\RuntimeException::class);
        $this->eslint_action->execute($this->config, $io, $this->repository, $config_action);
    }

    public function testAllowsConfiguringExtensionsOfFilesToCheck(): void {
        $this->index_operator->expects($this->any())
            ->method("getStagedFilesOfType")
            ->willReturnMap([
                [ "js", [ "foo.js" ] ],
                [ "mjs", [ "dir/bar.mjs" ] ],
                [ "ts", [ "qux.ts" ] ],
            ]);

        $io = new NullIO();
        $config_action = new Config\Action(ESLintAction::class, [ "extensions" => [ "js", "ts" ] ]);

        $ds = DIRECTORY_SEPARATOR;
        $expected_cmd = ".{$ds}node_modules{$ds}.bin{$ds}eslint --quiet " . escapeshellarg("foo.js") . " " . escapeshellarg("qux.ts");
        $this->processor->expects($this->once())
            ->method("run")
            ->with($expected_cmd)
            ->willReturn(new Result($expected_cmd, 0));

        $this->eslint_action->execute($this->config, $io, $this->repository, $config_action);
    }
}
