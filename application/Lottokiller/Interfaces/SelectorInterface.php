<?php

namespace Lottokiller\Interfaces;

interface SelectorInterface
{
    public function __construct($all_combinations, $past_lotteries);
    public function run();
    public function researchThePast();
    public function makeAchoice();
    public function checkTheFuture();
    public function revealTheSecret();
    public function getName();
}