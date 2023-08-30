<?php

namespace Lottokiller\Rules;

use Lottokiller\Game\PastLotteries;
use Lottokiller\Interfaces\RuleInterface;

//NAZWA REGUŁY: "Suma liczb z poprzednich losowań"
//
//OPIS REGUŁY:
//
//Ta reguła wyklucza z obiektu AllCombinations kombinacje, których sumy elementów
//są poza uznanymi przedziałami sum.
//
//Uznane przedziały sum to te, które występowały w dotychczasowych losowaniach
//nie rzadziej niż z podaną ważnością ('importance') wyrażoną w %.
//
////Przykład: $importance = 2; oznacza, że uznane zostaną te przedziały sum,
//w których znalazło się przynajmniej 2% z ogólnej liczb wszystkich dotychczasowych losowań.
//
//Przedziały sum są tworzone według podanego kroku ('step').
//Przykład: $step = 5; oznacza, że zostaną stworzone przedziały sum:
//1-5, 6-10, 11-15, 16-20, itd.
class SumOfPastElements implements RuleInterface
{
    private $name = 'Suma liczb z poprzednich losowań';
    private $description = 'Ta reguła wyklucza z wszystkich możliwych kombinacji te z nich, których suma wylosowanych liczb jest poza uznanymi przedziałami sum';
    private $past_lotteries;
    private $past_lotteries_sum_ranges;
    //CONFIG: Co ile ma być tworzony nowy przedział w tablicy analitycznej?
    private int $step = 4;
    //CONFIG: Jaki jest próg ważności przedziału w tablicy analitycznej?
    private int $importance = 1; //%
    
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
        if (empty($this->past_lotteries_sum_ranges)) {
            $this->sumAndAnalyzeElements();
        }
        if (
            !empty($this->past_lotteries_sum_ranges)
            && is_array($this->past_lotteries_sum_ranges)
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
        if (empty($this->past_lotteries_sum_ranges)) {
            $this->sumAndAnalyzeElements();
        }
        if (
            !empty($this->past_lotteries_sum_ranges)
            && is_array($this->past_lotteries_sum_ranges)
        ) {
            $this->drawGraph();
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
    public function setStep(int $step)
    {
        if (
            $step < 1
            || $step > 10    
        ) {
            echo '<p>W regule "' . $this->getName() . '" nie można ustawić <b>kroku tabeli sum</b> na <b>' . $step . '</b> (musi być z przedziału <b>1 - 10</b>)</p>';
        } else {
            $this->step = $step;
        }
    }
    public function setImportance (int $importance)
    {
        if (
            $importance < 1
            || $importance > 100
        ) {
            echo '<p>W regule "' . $this->getName() . '" nie można ustawić <b>progu ważności przedziału sum</b> na <b>' . $importance . '%</b> (musi być z przedziału <b>1 - 100</b>)</p>';
        } else {
            $this->importance = $importance;
        }
    }
    private function sumAndAnalyzeElements()
    {
        //Suma liczb z każdego losowania w nowej kolumnie 'lottery_sum'
        foreach ($this->past_lotteries->getAllLotteries() as $index => $lottery) {
            $lottery_sum = 0;
            for ($i = 0; $i < $this->past_lotteries->getK(); $i++)
            {
                $lottery_sum += $lottery[$i];
            }
            $this->past_lotteries->addNewColumnToRow($index, 'lottery_sum', $lottery_sum);
        }
        //Szukanie największej sumy
        $biggest_sum = 0;
        foreach ($this->past_lotteries->getAllLotteries() as $row) {
            if ($row['lottery_sum'] > $biggest_sum) {
                $biggest_sum = $row['lottery_sum'];
            }
        }
        //Tworzenie pustej tablicy analitycznej
        $analyze_array = array();
        $analyze_array_index = 0;
        $min_value = 1;
        $max_value = $this->step;
        $analyze_array[$analyze_array_index]['min'] = $min_value;
        $analyze_array[$analyze_array_index]['max'] = $max_value;
        $analyze_array[$analyze_array_index]['counter'] = 0;
        $analyze_array_index++;
        while ($max_value <= $biggest_sum) {
            $min_value += $this->step;
            $max_value += $this->step;
            $analyze_array[$analyze_array_index]['min'] = $min_value;
            $analyze_array[$analyze_array_index]['max'] = $max_value;
            $analyze_array[$analyze_array_index]['counter'] = 0;
            $analyze_array_index++;
        }
        //Wypełnianie tablicy analitycznej zliczeniami sum z danego przedziału
        foreach ($this->past_lotteries->getAllLotteries() as $row) {
            foreach ($analyze_array as &$analyze_row) {
                if (
                    $row['lottery_sum'] >= $analyze_row['min']
                    && $row['lottery_sum'] <= $analyze_row['max']
                ) {
                    $analyze_row['counter']++;
                }
            }
        }
        //Określanie, które przedziały sum należy wykluczyć z AllCombinations
        $past_lotteries_counter = count($this->past_lotteries->getAllLotteries());
        foreach ($analyze_array as &$analyze_row) {
            $analyze_row['confirm'] = false;
            $percentage = percentage($analyze_row['counter'], $past_lotteries_counter);
            if ($percentage >= $this->importance) {
                $analyze_row['confirm'] = true;
            }
        }
        $this->past_lotteries_sum_ranges = $analyze_array;
    }
    private function drawGraph()
    {
        $red_values = 0;
        $grey_values = 0;
        $past_lotteries_counter = count($this->past_lotteries->getAllLotteries());
        echo '<div class="visualizeBox">';
        echo '<p class="visualize">WIZUALIZACJA REGUŁY:<br/>&nbsp;&nbsp;&nbsp;&nbsp;<u>"' . $this->getName() . '"</u></p>';
        echo '<p class="visualize">OPIS REGUŁY:<br/ >&nbsp;&nbsp;&nbsp;&nbsp;' . $this->getDescription() . '</p>';
        echo '<p class="visualize">KONIGURACJA:<br />&nbsp;&nbsp;&nbsp;&nbsp;KROK TABELI SUM: <span style="color: red;">' . $this->step . '</span><br />&nbsp;&nbsp;&nbsp;&nbsp;PRÓG WAŻNOŚCI PRZEDZIAŁU SUM: <span style="color: red;">' . $this->importance . '%</span><br /></p>';
        echo '<p class="visualize">LEGENDA: <br/ >&nbsp;&nbsp;&nbsp;&nbsp;[minimalna wartość przedziału - maksymalna wartość przedziału] | &bull;&bull;&bull; procentowy udział przedziału we wszystkich dotychczasowych losowaniach<br /><br /></p>';
        foreach ($this->past_lotteries_sum_ranges as $draw_row) {
            echo '<p class="visualize" style="line-height: 7px; font-size: 13px">';
            echo $this->drawRange($draw_row['min'], $draw_row['max'], 10);
            echo '|&nbsp;';
            if ($draw_row['confirm'] === true) {
                echo '<span style="color: red">';
                $red_values += percentage($draw_row['counter'], $past_lotteries_counter);
            } else {
                echo '<span style="color: grey">';
                $grey_values += percentage($draw_row['counter'], $past_lotteries_counter);
            }
            for ($i=0; $i < $draw_row['counter']/10; $i++) {
                echo '&bull;';
            }
            echo '&nbsp;'.round(percentage($draw_row['counter'], $past_lotteries_counter), 2).'%</span></p>';
        }
        echo '<p class="visualize" style="font-size: 13px"><br /><span style="color: red">UZNANE PRZEDZIAŁY SUM: ' . round($red_values, 2).'%</span><br /><span style="color: grey">POMINIĘTE PRZEDZIAŁY SUM: ' . round($grey_values, 2).'%</span></b></p>';
        echo '</div>';
    }
    private function drawRange(int $min, int $max, int $tab): string
    {
        $result_string = '[' . $min . '-' . $max . ']';
        $string_lenght = strlen($result_string);
        while ($string_lenght < $tab)
        {
            $result_string .= ' ';
            $string_lenght = strlen($result_string);
        }
        $result_string = str_replace(' ', '&nbsp;', $result_string);
        return $result_string;
    }
    private function remove($all_combinations)
    {
        $removed_counter = 0;
        foreach ($all_combinations->getAllCombinations() as $index => $combination) {
            foreach ($this->past_lotteries_sum_ranges as $range) {
                $combination_sum = array_sum($combination);
                if (
                    $combination_sum >= $range['min']
                    && $combination_sum <= $range['max']
                    && $range['confirm'] === false
                ) {
                    $all_combinations->removeCombinationByIndex($index);
                    $removed_counter++;
                    break;
                }
            }
        }
        return $removed_counter;
    }
}