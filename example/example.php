<?php


require_once __DIR__ . '/../vendor/autoload.php';

use Xentixar\MantrajnaTest\Unicorn;

$unicorn = new Unicorn();

$unicorn
    ->readCsv(__DIR__ . '/data/data.csv');

$unicorn
    ->setColumnInfo('date:datetime:m/j/Y')
    ->sort('date', 'DESC')
    ->all()
    ->where(['date', '=', '2024-01-11'], ['home_score', '=', 3])
    ->selectColumns('date', 'home_score')
    ->format('date:M')
    ->run()
    ->print();

$unicorn
    ->clearAllFormat();

$unicorn
    ->run()
    ->print();
