<?php

namespace Lottokiller\Interfaces;

interface SelectorInterface
{
    public function __construct();
    public function run();
    public function researchThePast();
    public function makeAchoice();
    public function checkTheFuture();
    public function revealTheSecret();
    public function getName();
}