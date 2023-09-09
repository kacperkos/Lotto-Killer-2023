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
    private $numbers_extended_copy = null;
    private $chosen_combinations = null;
    private $saint_patricks_nod = false;
    
    public function __construct()
    {
        $this->all_combinations = AllCombinations::getInstance();
        $this->past_lotteries = new PastLotteries();
        $this->numbers = $this->all_combinations->getNumbers();
        $this->k = $this->all_combinations->getK();
        if ($this->k != $this->past_lotteries->getK()) {
            $this->error = true;
        }
    }
    public function run()
    {
        $this->researchThePast();
        $this->makeAchoice();
        $this->checkTheFuture();
        $this->revealTheSecret();
    }
    public function researchThePast()
    {
        if (!$this->error) {
            $past_lotteries_counter = 0;
            $previous_lottery = [];
            $previous_previous_lottery = [];
            $repeats_stage1_counter = 0;
            $repeats_stage2_counter = 0;
            $this->past_lotteries->removeColumnGlobally('lottery_id');
            $this->past_lotteries->removeColumnGlobally('lottery_date');
            foreach ($this->past_lotteries->getAllLotteries() as $lottery) {
                //STAGE #1: Powtórka liczby w 2 kolejnych losowaniach
                if ($past_lotteries_counter > 0) {
                    foreach ($lottery as $number) {
                        if (in_array($number, $previous_lottery)) {
                            $repeats_stage1_counter++;
                            break;
                        }
                    }
                    //STAGE #2: Powtórka liczby w 3 kolejnych losowaniach
                    if ($past_lotteries_counter > 1) {
                        foreach ($lottery as $number) {
                            if (in_array($number, $previous_lottery)) {
                                foreach ($previous_lottery as $previous_number) {
                                    if (in_array($previous_number, $previous_previous_lottery)) {
                                        $repeats_stage2_counter++;
                                        break 2;
                                    }
                                }
                            }
                        }    
                    }
                    $previous_previous_lottery = $previous_lottery;
                    $previous_lottery = $lottery;
                }
                $past_lotteries_counter++;
            }
            echo '<div class="visualizeBox">';
            echo '<p class="visualize">SELEKTOR "' . $this->getName() . '"</p>';
            echo '<p class="visualize" style="line-height: 7px; font-size: 13px; color: grey;">SZANSE NA WYSTĄPIENIE POWTÓRKI LICZBOWEJ W <span style="color: red;">2 KOLEJNYCH LOSOWANIACH</span>: <span style="color: red;">' . round(percentage($repeats_stage1_counter, count($this->past_lotteries->getAllLotteries())), 2) . '%</span></p>';
            echo '<p class="visualize" style="line-height: 7px; font-size: 13px; color: grey;">SZANSE NA WYSTĄPIENIE POWTÓRKI LICZBOWEJ W <span style="color: red;">3 KOLEJNYCH LOSOWANIACH</span>: <span style="color: red;">' . round(percentage($repeats_stage2_counter, count($this->past_lotteries->getAllLotteries())), 2) . '%</span><br />&nbsp;</p>';
            echo '</div>';
            //STAGE #1: GENEROWANIE PULI: pula liczb + (pula liczb - liczby z poprzedniego losowania)
            $this->numbers_extended = $this->numbers;
            $past_lotteries_tmp = $this->past_lotteries->getAllLotteries();
            $newest_lottery = end($past_lotteries_tmp);
            $this->numbers_extended = array_diff($this->numbers_extended, $newest_lottery);
            $this->numbers_extended = array_merge($this->numbers_extended, $this->numbers);
            //Uzupełnianie puli liczb tak, aby można było wygenerować pełne zakłady
            while (count($this->numbers_extended) % $this->past_lotteries->getK() != 0) {
                $this->numbers_extended = array_merge($this->numbers_extended, $this->stPatricksTouch());
            }
            $this->numbers_extended_copy = $this->numbers_extended;
            //STAGE #2:
        }
    }
    public function makeAchoice()
    {
        if (
            !$this->error
            && !empty($this->numbers_extended)
        ) {
            $number_of_bets = count($this->numbers_extended) / $this->past_lotteries->getK();
            $past_lotteries_tmp = $this->past_lotteries->getAllLotteries();
            $newest_lottery = end($past_lotteries_tmp);
            //Losowanie kombinacji z założeniem, że liczby z ostatniego losowania
            //powinny się znaleźć na osobnych zakładach
            foreach ($newest_lottery as $newest_number) {
                $this->chosen_combinations[][0] = $newest_number;
            }
            $this->numbers_extended = array_diff($this->numbers_extended, $newest_lottery);
            //Generowanie pozostałych pustych zakładów
            for ($i = 0; $i < $number_of_bets; $i++) {
                if (!isset($this->chosen_combinations[$i])) {
                    $this->chosen_combinations[$i] = [];
                }
            }
            //Uzupełnianie wszystkich losów liczbami
            foreach ($this->chosen_combinations as $index => &$chosen_combination) {
                $start_index = 0;
                if (isset($chosen_combination[0])) {
                    $start_index = 1;
                }
                for ($i = $start_index; $i < $this->past_lotteries->getK(); $i++) {
                    do {
                        $continue = false;
                        $new_random_number_index = array_rand($this->numbers_extended);
                        $new_random_number = $this->numbers_extended[$new_random_number_index];
                        if (in_array($new_random_number, $chosen_combination)) {
                            $continue = true;
                        } else {
                            $chosen_combination[$i] = $new_random_number;
                            unset($this->numbers_extended[$new_random_number_index]);
                        }
                    } while ($continue === true);
                }
            }
        }
    }
    public function checkTheFuture()
    {
        if (
            !$this->error
            && !empty($this->chosen_combinations)
        ) {
            //Sprawdzanie, czy wylosowane kombinacje znajdują się we
            //wstępnie przetworzonej puli obiektu AllCombiantions()
            do {
                $make_a_choice_again = false;
                foreach ($this->chosen_combinations as $combination) {
                    if ($this->all_combinations->ifExistsByNumbers($combination) === false) {
                        $make_a_choice_again = true;
                        $this->chosen_combinations = null;
                        $this->chosen_combinations = [];
                        $this->numbers_extended = $this->numbers_extended_copy;
                        $this->makeAchoice();
                    }
                }
            } while ($make_a_choice_again === true);
            $this->saint_patricks_nod = true;
        }
    }
    public function revealTheSecret()
    {
        if (
            !$this->error
            && $this->saint_patricks_nod
        ) {
            var_dump($this->chosen_combinations);
        }        
    }
    public function getName()
    {
        return $this->name;
    }
    //
    // METODY NADMIAROWE WZGLĘDEM INTERFEJSU
    //
    private function stPatricksTouch() {
        $result[] = $this->numbers[rand(min(array_keys($this->numbers)), max(array_keys($this->numbers)))];
        return $result;
    }
}