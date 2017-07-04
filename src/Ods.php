<?php

namespace Ken880guchi\Jra;

class Ods extends App
{
    const ODS_URL = 'http://www.jra.go.jp/JRADB/accessO.html';

    public $showPageString = [];

    public function __construct()
    {
        parent::__construct();
        $this->showPageString = $this->getShowPageStrings('#q_menu4 a', self::TOP_URL);
    }

    public function getShowRacePageStrings()
    {
        $showPageStrings = $this->getShowPageStrings('.joSelectArea a', self::ODS_URL, $this->showPageString);
        var_dump($showPageStrings);
        return $this->getShowPageStrings('.raceNo a', self::ODS_URL, $showPageStrings);
    }

    public function HeavyAwardsListOfThisWeek()
    {
        return $this->getShowPageStrings('.grdRaceBtn a', self::ODS_URL, $this->showPageString);
    }

    public function detail($paramsForOdsDetailPage)
    {
        $previousWakuban = 0;

        $crawler = $this->client->request('POST', ODS_URL, ['cname' => $paramsForOdsDetailPage]);
        $crawler->filter('.ozTanfukuTableUma tr')->each(function ($node) use (&$previousWakuban) {
            $domClasses = [
                '枠番'   => '.waku',
                '馬番'   => '.umaban',
                '馬名'   => '.bamei a',
                '単勝'   => '.oztan',
                '複賞'   => '.fukuMin',
                '複賞'   => '.fukuHaifun',
                '複賞'   => '.fukuMax',
                '性齢'   => '.seirei',
                '馬体重'  => '.batai',
                '負担重量' => '.futan',
                '騎手'   => '.kishu',
                '調教師'  => '.choukyou',
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
                } catch (InvalidArgumentException $e) {
                    if ($domClass === '.waku' && $previousWakuban > 0) {
                        echo $columnName . ':' . $previousWakuban . ', ';
                    }
                }
            }

            echo PHP_EOL;
        });
    }
}
