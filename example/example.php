<?php


require_once __DIR__ . '/../vendor/autoload.php';

use Xentixar\Mantrajna\Unicorn;

function csvExample()
{
    $unicorn = new Unicorn();
    $unicorn
        ->readCsv(__DIR__ . '/data/data.csv');

    $unicorn
        ->setColumnInfo('date:datetime:m/j/Y')
        ->sort('date', 'DESC')
        ->all()
        ->where(['date', '<', '2024-01-11'], ['home_score', '=', 3])
        ->format('date:M')
        ->update(['home_score', 'date'], [2, '02/15/2020'])
        ->selectColumns('date', 'home_score')
        ->delete()
        ->run()
        ->print();

    $unicorn
        ->clearAllFormat();
}

function jsonExample()
{
    $unicorn = new Unicorn();
    $unicorn
        ->readJson(__DIR__ . '/data/data.json');
    $unicorn->run()->print();
}

function xmlExample()
{
    $unicorn = new Unicorn();
    $unicorn
        ->readXml(__DIR__ . '/data/data.xml');
    $unicorn->run()->dumpXml(__DIR__ . '/output.xml');
}

csvExample();
