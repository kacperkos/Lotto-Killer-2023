<?php

namespace Lottokiller\Rules;

use Lottokiller\Game\AllCombinations;
use Lottokiller\Game\PastLotteries;
use Lottokiller\Interfaces\RuleInterface;

//NAZWA REGUŁY: "Pomiń kombinacje z przeszłości"
//
//OPIS REGUŁY:
//
//Ta reguła wyklucza z obiektu AllCombinations kombinacje, które zostały
//wylosowane w przeszłości.
class OmitPastLotteries implements RuleInterface
{
    private $name = 'Pomiń kombinacje z przeszłości';
    private $description = 'Ta reguła wyklucza z wszystkich możliwych kombinacji te z nich, które były już wylosowane w przeszłości';
    private $past_lotteries;
    private $past_lotteries_omit_counter = 0;
    private $past_lotteries_duplicate_counter = 0;
    
    public function __construct()
    { 
        $this->past_lotteries = new PastLotteries();
    }
    public function __destruct()
    {
        unset($this->past_lotteries);
    }
    public function apply($all_combinations)
    {
        if (empty($this->past_lotteries_omit_counter)) {
            $this->analyzePastLotteries();
        }
        if (!empty($this->past_lotteries_omit_counter)) {
            $result = $this->remove($all_combinations);
        }
        if ($result === 0) {
            $result_msg = 'Nic nie usunięto z użyciem reguły "' . $this->getName() . '".';
        } else {
            $result_msg = 'Użycie reguły "' . $this->getName() . '" spowodowało usunięcie ' . $result . ' kombinacji.';
        }
        return $result_msg;
    }
    public function visualize()
    {
        if (empty($this->past_lotteries_omit_counter)) {
            $this->analyzePastLotteries();
        }
        if (!empty($this->past_lotteries_omit_counter)) {
            $this->drawStats();
        } else {
            echo '<div class="visualizeBox"><p class="visualize">Wizualizacja reguły "' . $this->getName() . '" nie jest możliwa.</p></div>';
        }
    }
    public function getName()
    {
        return $this->name;
    }    
    public function getDescription()
    {
        return $this->description;
    }
    //
    // METODY NADMIAROWE WZGLĘDEM INTERFEJSU
    //
    private function analyzePastLotteries()
    {
        //Usuwanie z obiektu PastLotteries tych losowań, które nie pasują
        //do bieżącego profilu gry, np. liczby były losowane z innej puli liczb.
        $all_combinations = AllCombinations::getInstance();
        $current_game_numbers = $all_combinations->getNumbers();
        foreach ($this->past_lotteries->getAllLotteries() as $index => $lottery) {
            for ($i = 0; $i < $this->past_lotteries->getK(); $i++) {
                if (!in_array($lottery[$i], $current_game_numbers)) {
                    $this->past_lotteries->removeLotteryByIndex($index);
                    break;
                }
            }
        }
        //$this->past_lotteries_omit_counter = count($this->past_lotteries->getAllLotteries());
        //Usuwanie z obiektu PastLotteries duplikatów losowań
        $this->past_lotteries->removeColumnGlobally('lottery_id');
        $this->past_lotteries->removeColumnGlobally('lottery_date');
        foreach ($this->past_lotteries->getAllLotteries() as $base_index => $base_lottery) {
            foreach ($this->past_lotteries->getAllLotteries() as $index => $lottery) {
                if (
                    $base_lottery == $lottery
                    && $base_index < $index
                ) {
                    $this->past_lotteries->removeLotteryByIndex($index);
                    $this->past_lotteries_duplicate_counter++;
                }
            }
        }
        $this->past_lotteries_omit_counter = count($this->past_lotteries->getAllLotteries());
    }
    private function drawStats()
    {
        echo '<div class="visualizeBox">';
        echo '<p class="visualize">WIZUALIZACJA REGUŁY:<br/>&nbsp;&nbsp;&nbsp;&nbsp;<u>"' . $this->getName() . '"</u></p>';
        echo '<p class="visualize">OPIS REGUŁY:<br/ >&nbsp;&nbsp;&nbsp;&nbsp;' . $this->getDescription() . '</p>';
        echo '<p class="visualize" style="font-size: 13px; color: grey">W PRZESZŁOŚCI <span style="color: red">' . $this->past_lotteries_duplicate_counter . ' RAZ(Y)</span> ZDARZYŁO SIĘ, ŻE WYLOSOWANO TE SAME LICZBY<br />WEDŁUG DANYCH ZE WSZYSTKICH PRZESZŁYCH LOSOWAŃ SZANSA NA KOLEJNE TAKIE ZADARZENIE WYNOSI: <span style="color: red">' . round(percentage($this->past_lotteries_duplicate_counter, count($this->past_lotteries->getAllLotteries())), 2) . '%</span></p>';
        echo '<p class="visualize" style="font-size: 13px; color: red">PRZEWIDYWANA LICZBA LOSOWAŃ Z PRZESZŁOŚCI, KTÓRE MOŻNA BĘDZIE POMINĄĆ: ' . $this->past_lotteries_omit_counter . '</p>';
        echo '</div>';
    }
    private function remove($all_combinations)
    {
        $removed_counter = 0;
        foreach ($this->past_lotteries->getAllLotteries() as $lottery) {
            foreach ($all_combinations->getAllCombinations() as $index => $combination) {
                if ($lottery == $combination) {
                    $all_combinations->removeCombinationByIndex($index);
                    $removed_counter++;
                    break;
                }
            }
        }
        return $removed_counter;
    }
}