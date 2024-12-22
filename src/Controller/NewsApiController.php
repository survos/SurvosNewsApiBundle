<?php

namespace Survos\NewsApiBundle\Controller;

use Survos\NewsApiBundle\Service\NewsApiService;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class NewsApiController extends AbstractController
{
    public function __construct(
        private NewsApiService $news-apiService,
        private $simpleDatatablesInstalled = false
    )
    {

    }

    private function checkSimpleDatatablesInstalled()
    {
        if (! $this->simpleDatatablesInstalled) {
            throw new \LogicException("This page requires SimpleDatatables\n composer req survos/simple-datatables-bundle");
        }
    }
    #[Route('/zones', name: 'survos_news-api_zones', methods: ['GET'])]
    #[Template('@SurvosNewsApi/zones.html.twig')]
    public function zones(
    ): Response|array
    {
        $this->checkSimpleDatatablesInstalled();
        $baseApi = $this->news-apiService->getBaseApi();
        return ['zones' => $baseApi->listStorageZones()->getContents()];
    }

    #[Route('/{zoneName}}/{path}/{fileName}', name: 'survos_news-api_download', methods: ['GET'], requirements: ['path'=> ".+"])]
    #[Template('@SurvosNewsApi/zone.html.twig')]
    public function download(string $zoneName, string $path, string $fileName): Response
    {

        $response = $this->news-apiService->downloadFile($fileName,$path,$zoneName);
        return new Response($response); // eh
    }


    #[Route('/{zoneName}/{id}/{path}', name: 'survos_news-api_zone', methods: ['GET'])]
    #[Template('@SurvosNewsApi/zone.html.twig')]
    public function zone(
        string $zoneName,
        string $id,
        ?string $path='/'
    ): Response|array
    {
        $this->checkSimpleDatatablesInstalled();
        $edgeStorageApi = $this->news-apiService->getEdgeApi($zoneName);
        $list = $edgeStorageApi->listFiles(
            storageZoneName: $zoneName,
            path: $path
        );
        return [
            'zoneName' => $zoneName,
            'path' => $path,
            'files' => $list->getContents()
        ];
    }
}
