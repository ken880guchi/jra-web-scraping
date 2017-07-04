<?php

require_once 'vendor/autoload.php';

use Goutte\Client;

const TOP_URL = 'http://www.jra.go.jp/';
const ODS_URL = 'http://www.jra.go.jp/JRADB/accessD.html';

function getHashFromJsCode($jsCode)
{
    $explodedJsCodeArray = explode(',', $jsCode);
    preg_match("/'.*'/", $explodedJsCodeArray[1], $match);
    return str_replace("'", '', $match[0]);
}

$client = new Client();

// request top page
$crawler = $client->request('POST', TOP_URL);
$jsCode = $crawler->filter('#q_menu4 a')->attr('onclick');
$hashForOds = getHashFromJsCode($jsCode);

// request ods list page
$crawler = $client->request('POST', ODS_URL, ['cname' => $hashForOds]);
$jsCode = $crawler->filter('.grdRaceBtn a')->attr('onclick');
$hashForOdsDetail = getHashFromJsCode($jsCode);

$previousWakuban = 0;

// request ods detail page
$crawler = $client->request('POST', ODS_URL, ['cname' => $hashForOdsDetail]);
$crawler->filter('.ozTanfukuTableUma tr')->each(function($node) use (&$previousWakuban) {
    $domClasses = [
        '枠番'     => '.waku',
        '馬番'     => '.umaban',
        '馬名'     => '.bamei a',
        '単勝'     => '.oztan',
        '複賞'     => '.fukuMin',
        '複賞'     => '.fukuHaifun',
        '複賞'     => '.fukuMax',
        '性齢'     => '.seirei',
        '馬体重'   => '.batai',
        '負担重量' => '.futan',
        '騎手'     => '.kishu',
        '調教師'   => '.choukyou',
    ];

    foreach ($domClasses as $columnName => $domClass) {
        try {
            echo $columnName . ':' . trim($node->filter($domClass)->text()) . ', ';

            if ($domClass === '.bamei a') {
                $jsCode = $node->filter($domClass)->attr('onclick');
                $hashForHorseDetail = getHashFromJsCode($jsCode);
                echo '馬名Hash:' . $hashForHorseDetail . ', ';
            }

            if ($domClass === '.kishu') {
                $jsCode = $node->filter($domClass . ' a')->attr('onclick');
                $hashForHorseDetail = getHashFromJsCode($jsCode);
                echo '騎手Hash:' . $hashForHorseDetail . ', ';
            }

            if ($domClass === '.choukyou') {
                $jsCode = $node->filter($domClass . ' a')->attr('onclick');
                $hashForHorseDetail = getHashFromJsCode($jsCode);
                echo '調教師Hash:' . $hashForHorseDetail . ', ';
            }

            if ($domClass === '.waku') {
                $previousWakuban = (int)$node->filter($domClass)->text();
            }
        } catch(InvalidArgumentException $e) {
            if ($domClass === '.waku' && $previousWakuban > 0) {
                echo $columnName . ':' . $previousWakuban . ', ';
            }
        }
    }

    echo PHP_EOL;
});

exit();
