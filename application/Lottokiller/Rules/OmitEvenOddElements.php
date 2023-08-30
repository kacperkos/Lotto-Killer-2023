<?php

namespace Lottokiller\Rules;

use Lottokiller\Game\AllCombinations;
use Lottokiller\Game\PastLotteries;
use Lottokiller\Interfaces\RuleInterface;

//NAZWA REGUŁY: "Pomiń kombinacje parzyste i/lub nieparzyste"
//
//OPIS REGUŁY:
//
//Ta reguła wyklucza z obiektu AllCombinations kombinacje, które zawierają
//tylko liczby parzyste/nieparzyste.
class OmitEvenOddElements implements RuleInterface
{
    private $name = 'Pomiń kombinacje parzyste i/lub nieparzyste';
    private $description = 'Ta reguła wyklucza ze wszystkich możliwych kombinacji te z nich, które zawierają tylko liczby parzyste/nieparzyste';
    private $past_lotteries;
    private $past_lotteries_even_chance = null;
    private $past_lotteries_odd_chance = null;
    private $all_combinations;
    private $all_combinations_even_indexes = [];
    private $all_combinations_odd_indexes = [];
    //CONFIG: Jaki jest próg ważności, według którego kombinacje parzyste
    //i nieparzyste są kwalifikowane do usunięcia z obiektu AllCombinations?
    private int $importance = 2; //%
    
    public function __construct()
    {
        $this->past_lotteries = new PastLotteries();
        $this->all_combinations = AllCombinations::getInstance();
    }
    public function __destruct()
    {
        unset($this->past_lotteries);
    }
    public function apply($all_combinations)
    {
        if (
            $this->past_lotteries_even_chance === null
            && $this->past_lotteries_odd_chance === null
        ) {
            $this->analyzePastLotteriesAndCollectEvenOddIndexes();
        }
        if (
            $this->past_lotteries_even_chance !== null
            || $this->past_lotteries_odd_chance !== null
        ) {
            $result = $this->remove($all_combinations);
        }
        if ($result === 0) {
            $result_msg = '<p>Nic nie usunięto z użyciem reguły "' . $this->getName() . '".</p>';
        } else {
            $result_msg = '<p>Użycie reguły "' . $this->getName() . '" spowodowało usunięcie ' . $result . ' kombinacji.</p>';
        }
        return $result_msg;
    }
    public function visualize()
    {
        if (
            $this->past_lotteries_even_chance === null
            && $this->past_lotteries_odd_chance === null
        ) {
            $this->analyzePastLotteriesAndCollectEvenOddIndexes();
        }
        if (
            $this->past_lotteries_even_chance !== null
            || $this->past_lotteries_odd_chance !== null
        ) {
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
    public function setImportance (int $importance)
    {
        if (
            $importance < 1
            || $importance > 100
        ) {
            echo '<p>W regule "' . $this->getName() . '" nie można ustawić <b>progu ważności</b> na <b>' . $importance . '%</b> (musi być z przedziału <b>1 - 100</b>)</p>';
        } else {
            $this->importance = $importance;
        }
    }
    private function analyzePastLotteriesAndCollectEvenOddIndexes()
    {
        //Analiza obiektu PastLotteries, w celu zbadania prawdopodobieństwa
        //występowania kombinacji parzystych i nieparzystych
        $this->past_lotteries->removeColumnGlobally('lottery_id');
        $this->past_lotteries->removeColumnGlobally('lottery_date');
        $even_lotteries_counter = 0;
        $odd_lotteries_counter = 0;
        foreach ($this->past_lotteries->getAllLotteries() as $lottery) {
            $is_even = null;
            $is_even = $this->isEven($lottery);
            if ($is_even === true) {
                $even_lotteries_counter++;
            } elseif ($is_even === false) {
                $odd_lotteries_counter++;
            }
        }
        if ($even_lotteries_counter > 0) {
            $this->past_lotteries_even_chance = percentage($even_lotteries_counter, count($this->past_lotteries->getAllLotteries()));
        }
        if ($odd_lotteries_counter > 0) {
            $this->past_lotteries_odd_chance = percentage($odd_lotteries_counter, count($this->past_lotteries->getAllLotteries()));
        }
        //Znajdź index'y wszystkich parzystych i nieparzystych kombinacji w AllCombinations
        foreach ($this->all_combinations->getAllCombinations() as $index => $combination) {
            $is_even = null;
            $is_even = $this->isEven($combination);
            if ($is_even === true) {
                $this->all_combinations_even_indexes[] = $index;
            } elseif ($is_even === false) {
                $this->all_combinations_odd_indexes[] = $index;
            }
        }
    }
    private function isEven(array $lottery): ?bool
    {
        //Funkcja sprawdza, czy zadana loteria zawiera tylko liczby parzyste;
        //jeżeli tak to zwraca "true", jeżeli nie to zwraca "false".
        //Jeżeli loteria składa się z liczb parzystych i nieparzystych to
        //zwracany jest "null".
        $even_numbers_counter = 0;
        $odd_numbers_counter = 0;
        for ($i = 0; $i < count($lottery); $i++) {
            if ($lottery[$i] % 2 == 0) {
                $even_numbers_counter++;
            } else {
                $odd_numbers_counter++;
            }
        }
        if ($even_numbers_counter == count($lottery)) {
            return true;
        } elseif ($odd_numbers_counter == count($lottery)) {
            return false;
        } else {
            return null;
        }
    }
    private function drawStats()
    {
        echo '<div class="visualizeBox">';
        echo '<p class="visualize">WIZUALIZACJA REGUŁY:<br/>&nbsp;&nbsp;&nbsp;&nbsp;<u>"' . $this->getName() . '"</u></p>';
        echo '<p class="visualize">OPIS REGUŁY:<br/ >&nbsp;&nbsp;&nbsp;&nbsp;' . $this->getDescription() . '</p>';
        echo '<p class="visualize">KONIGURACJA:<br />&nbsp;&nbsp;&nbsp;&nbsp;PRÓG WAŻNOŚCI/POMINIĘCIA: <span style="color: red;">' . round($this->importance, 2) . '%</span></p>';
        echo '<p class="visualize" style="font-size: 13px; color: grey;">SZACOWANE PRAWDOPODOBIEŃSTWO WYSTĄPIENIA KOMBINACJI <span style="color: red;">"PARZYSTEJ"</span>: <span style="color: red;">' . round($this->past_lotteries_even_chance, 2) . '%</span><br />ILOŚĆ KOMBINACJI, KTÓRE BĘDZIE MOŻNA USUNĄĆ: <span style="color: red;">' . count($this->all_combinations_even_indexes) . '</span></p>';
        echo '<p class="visualize" style="font-size: 13px; color: grey;">SZACOWANE PRAWDOPODOBIEŃSTWO WYSTĄPIENIA KOMBINACJI <span style="color: red;">"niePARZYSTEJ"</span>: <span style="color: red;">' . round($this->past_lotteries_odd_chance, 2) . '%</span><br />ILOŚĆ KOMBINACJI, KTÓRE BĘDZIE MOŻNA USUNĄĆ: <span style="color: red;">' . count($this->all_combinations_odd_indexes) . '</span></p>';
        echo '</div>';
    }
    private function remove($all_combinations)
    {
        $removed_counter = 0;
        if ($this->past_lotteries_even_chance < $this->importance) {
            foreach ($this->all_combinations_even_indexes as $index) {
                $all_combinations->removeCombinationByIndex($index);
                $removed_counter++;
            }
        }
        if ($this->past_lotteries_odd_chance < $this->importance) {
            foreach ($this->all_combinations_odd_indexes as $index) {
                $all_combinations->removeCombinationByIndex($index);
                $removed_counter++;
            }
        }
        return $removed_counter;
    }
}