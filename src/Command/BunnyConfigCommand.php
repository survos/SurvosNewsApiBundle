<?php

namespace Survos\NewsApiBundle\Command;

use Psr\Log\LoggerInterface;
use Survos\NewsApiBundle\Service\NewsApiService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\Yaml\Yaml;
use Zenstruck\Console\Attribute\Argument;
use Zenstruck\Console\Attribute\Option;
use Zenstruck\Console\InvokableServiceCommand;
use Zenstruck\Console\IO;
use Zenstruck\Console\RunsCommands;
use Zenstruck\Console\RunsProcesses;
use function Symfony\Component\String\u;

#[AsCommand('news-api:config', 'Configure your application with the news-api keys')]
final class NewsApiConfigCommand extends InvokableServiceCommand
{
    use RunsCommands;
    use RunsProcesses;

    public function __construct(
        private readonly NewsApiService $news-apiService,
        #[Autowire('%kernel.project_dir%')] private $projectDir,
    )
    {
        parent::__construct();
    }

    public function __invoke(
        IO                                                                                          $io,
        #[Argument(description: 'api key')] ?string        $apiKey=null,
        #[Option(description: "Overwrite the survos_news-api.yaml config file")] bool $force=false,
        #[Option(name: 'zone', description: 'limit configuration to just one zone')] ?string $zoneName = null,

    ): int
    {
        $apiKey = $apiKey??$this->news-apiService->getApiKey();
        if (!$apiKey) {
            $io->error("set environment variable NEWS_API_KEY or pass it as the first parameter here.");
            return self::FAILURE;
        }

        // if no zone, we could prompt
        $baseApi = $this->news-apiService->getBaseApi($apiKey);
            $zones = $baseApi->listStorageZones()->getContents();
            $zoneConfig = [];
            $env[] = "NEWS_API_KEY=$apiKey";
            foreach ($zones as $zone) {
                if ($zoneName && ($zoneName !== $zone['Name'])) {
                    continue;
                }
                $zoneConstant = u($zone['Name'])->snake()->upper()->toString();
                // inject slugger?  Or try to avoid dependencies?
//                $zoneName = str_replace('-', '_', $zoneName);
                $zoneConfig[] = [
                    'name' => $zone['Name'],
                    'id' => $zone['Id'],
                    'region' => $zone['Region'],
                    'readonly_password' => "%env(BUNNY_{$zoneConstant}_READONLY_PASSWORD)%"
                ];
                $env[] = "BUNNY_{$zoneConstant}_READONLY_PASSWORD=" . $zone['ReadOnlyPassword'];
                $env[] = "BUNNY_{$zoneConstant}_PASSWORD=" . $zone['Password'];
            }
            $config['survos_news-api'] = [
                'api_key' => "%env(NEWS_API_KEY)%",
                'zones' => $zoneConfig,
            ];

        if ($force) {
            file_put_contents($filename = $this->projectDir . '/config/packages/survos_news-api.yaml', Yaml::dump($config, inline: 4   ));
            $io->success($filename . ' written, add these to your environment, e.g. .env.local or the vault');
        }
        $io->writeln($env);

        return self::SUCCESS;
    }




}