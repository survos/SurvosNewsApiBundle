<?php
namespace Command;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Survos\NewsApiBundle\Command\NewsApiDownloadCommand;

class NewsApiUploadCommandTest extends KernelTestCase
{

    public function testExecute(): void
    {
        self::bootKernel();
        $application = new Application(self::$kernel);
        $command = $application->find('news-api:upload');

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'filename' => 'composer.json',
            'remoteDirOrFilename' => 'test',
            '--zip' => false
        ]);

        $commandTester->assertCommandIsSuccessful();

        $output = $commandTester->getDisplay();

        $this->assertStringContainsString('news-api:upload started', $output);
        $this->assertStringContainsString('Uploading composer.json to museado', $output);
        $this->assertStringContainsString('news-api:upload finished', $output);
    }

    public function testUploadFolderWithoutZip(): void
    {
        self::bootKernel();
        $application = new Application(self::$kernel);
        $command = $application->find('news-api:upload');

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'filename' => 'bin',
            'remoteDirOrFilename' => 'test',
            '--zip' => false
        ]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Please specify --zip for directories', $output);
    }

    public function testUploadFolderWithZip(): void
    {
        self::bootKernel();
        $application = new Application(self::$kernel);
        $command = $application->find('news-api:upload');

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'filename' => 'bin',
            'remoteDirOrFilename' => 'test',
            '--zip' => true
        ]);

        $commandTester->assertCommandIsSuccessful();

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('news-api:upload started', $output);
        $this->assertStringContainsString('bin.zip to museado/testbin.zip', $output);
        $this->assertStringContainsString('news-api:upload finished', $output);
    }
}
