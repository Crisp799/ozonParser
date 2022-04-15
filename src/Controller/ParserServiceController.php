<?php

namespace App\Controller;

use App\Entity\Seller;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DomCrawler\Crawler;

class ParserServiceController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function urlParser(string $url): array
    {
        $matches = [];
        preg_match('/(https:\/\/)?(www.ozon.ru\/)?(category\/)?([\w-]+\/)?(\?page=)?([\d]+)?/', $url, $matches);
        return $matches;
    }

    public function collect(string $url, ?int $pageCount = null): array
    {
        $parsedUrl = $this->urlParser($url);
        $resultInfo = [
            'collectDataCount' => 0,
            'addDataCount' => 0,
        ];
        $defoultPageUrl = '';
        for ($i = 1; $i < 5; ++$i)
            $defoultPageUrl .= $parsedUrl[$i];
        $customPageUrl = $defoultPageUrl . '?page=';
        $startPageIndex = isset($parsedUrl[6]) ? $parsedUrl[6] : 1;

        for ($i = 0; $i < $pageCount; ++$i) {
            $iterResultInfo = [];
            if ($i === 0 && $startPageIndex === 1) {
                $iterResultInfo = $this->pageParser($defoultPageUrl);
            } else
                $iterResultInfo = $this->pageParser($customPageUrl . $startPageIndex + $i);

            $resultInfo['collectDataCount'] += $iterResultInfo['collectDataCount'];
            $resultInfo['addDataCount'] += $iterResultInfo['addDataCount'];
        }

        return $resultInfo;

    }

    public function pageParser(string $url): array
    {
        $client = new Client();
        $response = $client->request('get', $url);
        $response = $response->getBody()->getContents();
        $crawler = new Crawler($response);

        $allData = $crawler->filterXPath('//*[@id="state-searchResultsV2-252189-default-1"]');

        $jsonData = $allData->outerHtml(); //  может начать ругаться на outerHtml() и выкидывать ошибку, для решения надо перезагрузить страницу
                                           //  решить эту проблему без изменения класса crawler так и не получилось
                                           //  из-за этого, чтобы уменьшить вероятность появление ошибки, количество страниц было ограничено

        $encodeData = stristr($jsonData, '{"items');
        $encodeData = stristr($encodeData, '\'></div>', true);
        $encodeData = json_decode($encodeData, true);
        $goodsData = [];

        $collectDataCount = 0;

        foreach ($encodeData['items'] as $itemData) {
            if (isset($itemData['multiButton']['ozonSubtitle'])) {
                ++$collectDataCount;
                $goodData = [
                    'seller' => $this->getSeller(strip_tags($itemData['multiButton']['ozonSubtitle']['textAtomWithIcon']['text'])),
                    'productName' => $this->getProductName($itemData['mainState']),
                    'price' => $this->getPrise($itemData['mainState'][0]['atom']['price']['price']),
                    'countOfReviews' => $this->getCountOfReview($itemData['mainState']),
                    'sku' => $itemData['topRightButtons'][0]['favoriteProductMolecule']['sku'],
                ];
                array_push($goodsData, $goodData);
            }
        }
        $addDataCount = $this->saveProduct($goodsData);

        return [
            'collectDataCount' => $collectDataCount,
            'addDataCount' => $addDataCount,
        ];

    }

    private function saveProduct(array $productsArray): int
    {
        $addDataCount = 0;
        foreach ($productsArray as $productData) {

            $seller = $this->isSellerJustExistInTable($productData['seller']);
            if ($seller === null) {
                $seller = new Seller();
                $seller->setName($productData['seller']);
                $this->entityManager->persist($seller);

                $this->entityManager->flush();
            }
            if ($this->isProductJustExistInTable($productData) === false) {
                $product = new Product($seller);
                $product->setName($productData['productName']);
                $product->setPrice($productData['price']);
                $product->setSku($productData['sku']);
                $product->setReviewsCount($productData['countOfReviews']);
                $product->setCreatedDateValue();
                $product->setUpdatedDateValue();
                $product->setSellerId($seller);
                $this->entityManager->persist($product);

                $this->entityManager->flush();
                ++$addDataCount;
            }

        }
        return $addDataCount;
    }

    public function updateProduct(Product $product)
    {
        $url = $product->getOzonLink();
        $client = new Client();
        $response = $client->request('get', $url);
        while ($response->getStatusCode() !== 200) {
            $response = $client->request('get', $url);
        }
        $response = $response->getBody()->getContents();
        $crawler = new Crawler($response); //state-searchResultsV2-311178-default-1
        $jsonData = $crawler->filterXPath('//script[@type="application/ld+json"]')->outerHtml();
        $matches = [];
        preg_match('/{.+}/', $jsonData, $matches);
        $encodeData = json_decode($matches[0], true);

        $this->updater($product, $encodeData);
    }

    public function updater(Product $product, array $dataArray)
    {
        $product->setReviewsCount($dataArray['aggregateRating']['reviewCount']);
        $product->setPrice($dataArray['offers']['price']);
        $product->setUpdatedDateValue();
        $this->entityManager->persist($product);
        $this->entityManager->flush();
    }

    public function isProductJustExistInTable($productData): bool
    {
        $skuString = $productData['sku'];
        $repository = $this->entityManager->getRepository(Product::class);
        $dbData = $repository->findOneBy(['sku' => $skuString]);
        if (empty($dbData))
            return false;
        return true;
    }

    public function isSellerJustExistInTable(string $string): ?Seller
    {
        $repository = $this->entityManager->getRepository(Seller::class);
        $dbData = $repository->findOneBy(['name' => $string]);
        if (!empty($dbData))
            return $dbData;

        return null;
    }

    public function isSellerExistInTable(int $id): bool
    {
        $repository = $this->entityManager->getRepository(Seller::class);
        $dbData = $repository->findOneBy(['id' => $id]);
        if (empty($dbData))
            return false;
        return true;
    }

    public function getSeller($string): ?string
    {
        if (mb_strpos($string, 'продавец') === false)
            return null;
        return mb_substr($string, mb_strpos($string, 'продавец') + 9);
    }

    public function getPrise($string): ?int
    {
        $matches = [];
        $string = str_replace(' ', ' ', $string);
        if (preg_match_all('/[\d]*\s?[\d]{0,3}/', $string, $matches) === 0)
            return null;
        $price = str_replace(' ', '', $matches[0][0]);
        return intval($price);
    }

    public function getCountOfReview($dataArray): ?int
    {
        foreach ($dataArray as $data) {
            if ($data['atom']['type'] === 'rating') {
                $string = $data['atom']['rating']['count'];
                $matches = [];
                if (preg_match('/\d*/', $string, $matches) === 0)
                    return null;
                return intval($matches[0]);
            }
        }
        return 0;
    }

    public function getProductName($dataArray): ?string
    {
        foreach ($dataArray as $atomArray) {
            if ($atomArray['id'] === 'name') {
                return $atomArray['atom']['textAtom']['text'];
            }
        }
        return null;
    }
}
