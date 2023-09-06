<?php

namespace Lottokiller\Selectors;

use Lottokiller\Game\AllCombinations;
use Lottokiller\Game\PastLotteries;
use Lottokiller\Interfaces\SelectorInterface;

//NAZWA SELEKTORA: "Luck of the Irish"
//
//OPIS:
//
//1. Szanse na powtórkę przynajamniej 1 liczby w kolejnym losowaniu wynoszą 50%
class LuckOfTheIrish implements SelectorInterface
{
    private $all_combinations;
    private $past_lotteries;
    private $name = 'Luck of the Irish';
    private $numbers;
    private $k;
    private $error = false;
    private $numbers_extended = null;
    private $chosen_combinations = null;
    private $saint_patricks_nod = false;
    
    public function __construct($all_combinations, $past_lotteries)
    {
        $this->all_combinations = $all_combinations;
        $this->past_lotteries = $past_lotteries;
        $this->numbers = $this->all_combinations->getNumbers();
        $this->k = $this->all_combinations->getK();
        if ($this->k != $past_lotteries->getK()) {
            $this->error = true;
        }
    }
    public function run()
    {
        $this->researchThePast();
        //$this->makeAchoice();
        //$this->checkTheFuture();
        //$this->revealTheSecret();
    }
    public function researchThePast()
    {
        if (!$this->error) {
            
        }
    }
    public function makeAchoice()
    {
        if (
            !$this->error
            && !empty($this->numbers_extended)
    ) {
            
        }
    }
    public function checkTheFuture()
    {
        if (
            !$this->error
            && !empty($this->chosen_combinations)
        ) {
            
        }
    }
    public function revealTheSecret()
    {
        if (
            !$this->error
            && $this->saint_patricks_nod
        ) {
            
        }        
    }
    public function getName()
    {
        return $this->name;
    }
    //
    // METODY NADMIAROWE WZGLĘDEM INTERFEJSU
    //    
}