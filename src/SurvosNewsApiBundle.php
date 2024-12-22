<?php

/** generated from /home/tac/g/survos/survos/vendor/survos/maker-bundle/templates/skeleton/bundle/src/Bundle.tpl.php */

namespace Survos\NewsApiBundle;

use Survos\NewsApiBundle\Command\NewsApiConfigCommand;
use Survos\NewsApiBundle\Command\NewsApiListCommand;
use Survos\NewsApiBundle\Command\NewsApiDownloadCommand;
use Survos\NewsApiBundle\Command\NewsApiUploadCommand;
use Survos\NewsApiBundle\Controller\NewsApiController;
use Survos\NewsApiBundle\Service\NewsApiService;
use Survos\NewsApiBundle\Twig\TwigExtension;
use Survos\SimpleDatatables\SurvosSimpleDatatablesBundle;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class SurvosNewsApiBundle extends AbstractBundle
{

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        // get all bundles https://symfony.com/doc/current/bundles/prepend_extension.html
        $bundles = $builder->getParameter('kernel.bundles');
        $hasSimpleDatatables = in_array(SurvosSimpleDatatablesBundle::class, array_values($bundles));

        $serviceId = 'survos_news-api.news-api_service';
        $container->services()->alias(NewsApiService::class, $serviceId);
        $builder->autowire($serviceId, NewsApiService::class)
            ->setArgument('$config', $config)
            ->setAutoconfigured(true)
            ->setAutowired(true)
            ->setPublic(true);

        $builder->autowire(NewsApiController::class)
            ->setArgument('$simpleDatatablesInstalled', $hasSimpleDatatables)
            ->addTag('container.service_subscriber')
            ->addTag('controller.service_arguments')
        ;

        foreach ([NewsApiConfigCommand::class, NewsApiListCommand::class, NewsApiUploadCommand::class, NewsApiDownloadCommand::class] as $commandName) {
            $builder->autowire($commandName)
                ->setAutoconfigured(true)
                ->addTag('console.command')
            ;
        }

        // twig classes, for news-api_url ?
        $builder
            ->autowire('survos.news-api_twig', TwigExtension::class)
            ->setAutoconfigured(true)
            ->addTag('twig.extension');
    }

    private function addZonesSection(ArrayNodeDefinition $rootNode): void
    {
        $rootNode
            ->children()
            ->arrayNode('zones')
            ->arrayPrototype()
            ->children()
                ->scalarNode('name')->end()
                ->scalarNode('id')->end()
                ->scalarNode('region')->end()
                ->scalarNode('readonly_password')->end()
                ->scalarNode('password')->end()
            ->end()
            ->end()
            ->end();

    }

    public function configure(DefinitionConfigurator $definition): void
    {
        $rootNode = $definition->rootNode();
        $rootNode
            ->children()
                ->scalarNode('api_key')->defaultNull()->end()
                ->scalarNode('storage_zone')->defaultValue(null)->end()
//                ->scalarNode('region')->defaultValue(null)->end()
//                ->scalarNode('readonly_password')->defaultValue(null)->end()
//                ->scalarNode('password')->defaultValue(null)->end()
            ->end();

        $this->addZonesSection($rootNode);
    }

}
