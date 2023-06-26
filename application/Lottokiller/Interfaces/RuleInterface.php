<?php

namespace Lottokiller\Interfaces;

interface RuleInterface
{
    public function __construct();
    public function __destruct();
    public function apply($all_combinations);
    public function visualize();
    public function getName();
    public function getDescription();
}