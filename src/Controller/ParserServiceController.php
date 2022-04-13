<?php

namespace App\Controller;


use GuzzleHttp\Client;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\CssSelector\CssSelectorConverter;

class ParserServiceController extends AbstractController
{
    /*
    #[Route('/parser/service', name: 'app_parser_service')]
    public function index(): Response
    {
        return $this->render('parser_service/index.html.twig', [
            'controller_name' => 'ParserServiceController',
        ]);
    }*/
    public function getGoods($url) {
        $client = new Client();

        $crawler = new Crawler();
        $crawler -> addHtmlContent(file_get_contents($url));
        //echo $crawler->html();
        $goodsArray =[];
        $converter = new CssSelectorConverter();
        //$product = $crawler->filter('.n4i');
        //$product = $crawler->filter('.widget-search-result-container >div > div');
        $product = $crawler->filterXPath('//*[@id="layoutPage"]/div[1]/div[3]/div[2]/div[2]/div[3]/div[1]/div/div/div');
        //echo $product->count();
        //$links = $crawler->filter('.widget-search-result-container >div > div > .im7')->extract(['attr' => 'href']); // сделать зависимым не от тега
        $links = $crawler->filterXPath('//*[@id="layoutPage"]/div[1]/div[3]/div[2]/div[2]/div[3]/div[1]/div/div/div/a')->extract(['attr' => 'href']); // сделать зависимым не от тега

        var_dump($links);
        $prices = $crawler->filter('.ui-t');
        foreach ($links as $link) {
            echo $this->getSKU($link).' ';
        }
        foreach ($product as $key => $domElement) {
            //echo $this->getProductName($domElement->textContent).' ';
            //echo $domElement->textContent.'____';
            //array_push($goodsArray, $domElement->textContent);
            //var_dump($domElement);
            //var_dump($domElement);
            /*foreach($domElement->childNodes as $node) {
                $html .= $domElement->ownerDocument->saveHTML($node);
                $html .='\n';
            }*/
        }
        //file_put_contents('2.txt', $html);
        //echo $html;
        return $goodsArray;
    }

    public function getSeller($string) :?string
    {
        if(mb_strpos($string, 'продавец') === false)
            return null;
        return mb_substr($string, mb_strpos($string, 'продавец') + 9);
    }

    public function getPrise($string) :?int
    {
        $matches=[];
        $string = str_replace(' ','  ',$string);
        if(preg_match_all('/[\d]*\s\s[\d]{3}\s\s/', $string, $matches)===0)
            return null;
        $price =str_replace(' ','',$matches[0][0]);
        return intval($price);
    }

    public function getCountOfReview($string) :?int
    {
        $finishIndex = mb_strpos($string, 'отз')-2;
        $string=mb_substr($string,0,$finishIndex+1);
        $matches = [];
        if(preg_match('/\d+$/',$string,$matches) === 0)
            return null;
        //var_dump($matches);
        return intval($matches[0]);
    }

    public function getProductName($string) :?string
    {
        $countOfReview = $this->getCountOfReview($string);

        $string = str_replace('Бестселлер','', $string); //Убираем слово бестселлер
        $finishLineIndex = mb_strpos($string, 'отз') - 2;
        $string = mb_substr($string, 0, $finishLineIndex + 1);

        $startIndex = mb_strpos($string, '₽'); //обрезаем строку оставляя только название и количество отзывов
        $string = mb_substr($string, $startIndex + 1);
        $startIndex = mb_strpos($string, '₽');
        $string = mb_substr($string, $startIndex + 1);

        $finishLineIndex = mb_strpos($string,strval($countOfReview));
        $string = mb_substr($string, 0, $finishLineIndex -1);
        //echo $startIndex;
        return $string;

    }

    public function getSKU($link) :?int
    {
        $matches =[];
        if(preg_match('/\d{5,}/', $link, $matches) === false)
            return null;
        return intval($matches[0]);
    }
}
