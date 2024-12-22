<?php

// load and translate news

declare(strict_types=1);

namespace Survos\NewsApiBundle\Service;

use jcobhams\NewsApi\NewsApi;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\CacheItem;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Contracts\Cache\CacheInterface;

class NewsApiService
{
    private NewsApi $newsApi;
    public function __construct(
        private ?string $apiKey=null,
        private CacheInterface $cache,
        private LoggerInterface $logger
    )
    {
        // can any calls be made without the key?
        if ($apiKey !== null) {
            $this->newsApi = new NewsApi($this->apiKey);
        }

    }

    public function getSources(string $language): object|array
    {
        $sources = $this->cache->get('sources_' . $language,
            fn(CacheItem $item) => $this->newsApi->getSources(language: $language));
        return $sources->sources;
    }

    public function loadSources(string $language)
    {
        $sources = $this->getSources($language);
        dd($sources); // where should we move to model?
        foreach ($sources as $data) {
            $data = (array)$data;
            $origLanguage = $data['language'];
            if (!$source = $this->entityManager->getRepository(Source::class)
                ->findOneBy(['code' => $data['id']])) {
                $source = (new Source())
                    ->setCode($data['id']);
                $this->entityManager->persist($source);
            }
            // set the originals, not really translations
            $source->setDefaultLocale($origLanguage);
            $source
                ->translate($origLanguage)->setDescription($data['description']);
            $source
                ->translate($origLanguage)->setName($data['name']);
            $source
                ->setUrl($data['url'])
                ->setCountry($data['country'])
                ->setLanguage($data['language']);
        }
        $this->entityManager->flush();

    }

    public function loadArticles(string $language, string $q='tobacco')
    {
        $slugger = new AsciiSlugger();
        $key = sprintf("art_%s_%s", $language, $slugger->slug($q));
        $articles = $this->cache->get($key, fn(CacheItem $cacheItem) =>
            $this->newsApi->getEverything($q, language: $language)
        );
        dd($articles);
        return $articles;
        foreach ($articles->articles as $idx => $a) {
            $s = $a->source;
            if (!$s->id) {
                continue;
            }
            dump('orig: ' . $language . '/' . $a->title);
            $source = $this->sourceRepository->findOneBy(['code' => $s->id]);
            if (!$source) {
                continue;
            }

            $article = $this->articleRepository->get($a->url);
            assert($source, $s->id);
//            $source->addArticle($article); // update count?
            $article->setSource($source);
            $article->setUrl($a->url)
                ->setAuthor($a->author)
                ->setPublishedAt(new \DateTimeImmutable($a->publishedAt))
                ->setLanguage($language)
                ->setDefaultLocale($language)
            ;
            $article
                ->translate($language)->setDescription($a->description)
            ;
            $article
                ->translate($language)->setTitle($a->title);
            $article->mergeNewTranslations();
        }
        $this->entityManager->flush();
    }

}
