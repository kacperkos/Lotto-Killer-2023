<?php

namespace Lottokiller\Rules;

use Lottokiller\Game\AllCombinations;
use Lottokiller\Game\PastLotteries;
use Lottokiller\Interfaces\RuleInterface;

//NAZWA REGUŁY: "Pomiń kombinacje z liczbami w rzędzie"
//
//OPIS REGUŁY:
//
//Ta reguła wyklucza z obiektu AllCombinations kombinacje, które zawierają
//liczby w n-elementowych rzędach, tzn. że wylosowano n kolejnych liczb, np.
//n=2; kombinacja=[1,7,8,13,41];
//n=3; kombinacja=[7,13,39,40,41];
//n=4; kombinacja=[20,32,33,34,35];
//n=5; kombinacja=[20,21,22,23,24];
//
//Jeżeli kombinacja zawiera rząd kolejnych liczb o długości większej od n,
//to taka kombinacja nie będzie wykluczona, np.
//n=2; kombinacja=[1,2,3,10,20];
//n=3; kombinacja=[11,12,13,14,40];
//n=4; kombinacja=[20,21,22,23,24];
class OmitInrowElements implements RuleInterface
{
    private $name = 'Pomiń kombinacje z liczbami w rzędzie';
    private $description = 'Ta reguła wyklucza ze wszystkich możliwych kombinacji te z nich, które zawierają liczby w n-elementowych rzędach';
    private $past_lotteries;
    private $past_lotteries_inrow_chance;
    private $all_combinations;
    private $all_combinations_inrows_indexes = [];
    //CONFIG: Kombinacje z iloma liczbami w rzędzie mają zostać pominięte?
    private $n = 2;
    //CONFIG: Jaki jest próg ważności, według której n-elementowy rząd zostanie
    //zakwalifikowany do pominięcia?
    private int $importance = 2; //%
    
    public function __construct()
    {
        $this->past_lotteries = new PastLotteries();
        $this->all_combinations = AllCombinations::getInstance();
    }
    public function __destruct()
    {
        unset($this->past_lotteries);
        unset($this->all_combinations);
    }
    public function apply($all_combinations)
    {
        if (empty($this->all_combinations_inrows_indexes)) {
            $this->analyzePastLotteriesAndCollectInrowsIndexes();
        }
        if (!empty($this->all_combinations_inrows_indexes)) {
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
        if (empty($this->all_combinations_inrows_indexes)) {
            $this->analyzePastLotteriesAndCollectInrowsIndexes();
        }
        if (!empty($this->all_combinations_inrows_indexes)) {
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
    public function setN (int $n)
    {
        if (
            $n < 2
            || $n > $this->all_combinations->getK()
        ) {
            echo '<p>W regule "' . $this->getName() . '" nie można ustawić <b>n=' . $n . '</b> (musi być z przedziału <b>2 - ' . $this->all_combinations->getK() . '</b>).</p>';
        } else {
            $this->n = $n;
        }
    }
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
    private function analyzePastLotteriesAndCollectInrowsIndexes()
    {
        //Analiza obiektu PastLotteries, w celu zbadania prawdopodobieństwa
        //wystąpienia n-elementowego rzędu liczb w kombinacji
        $past_lotteries_inrows_counter = 0;
        foreach ($this->past_lotteries->getAllLotteries() as $lottery) {
            if ($this->inrowCheck($lottery)) {
                $past_lotteries_inrows_counter++;
            }
        }
        $this->past_lotteries_inrow_chance = percentage($past_lotteries_inrows_counter, count($this->past_lotteries->getAllLotteries()));
        //Jeżeli prawdopodobieństwo wystąpienia n-elementowych rzędów
        //jest mniejsze od zadanego progu ważności, to znajdź index'y wszystkich
        //takich n-elementowych rzędów w obiekcie AllCombinations
        if ($this->past_lotteries_inrow_chance < $this->importance) {
            foreach ($this->all_combinations->getAllCombinations() as $index => $combination) {
                if ($this->inrowCheck($combination)) {
                    $this->all_combinations_inrows_indexes[] = $index;
                }
            }
        }
    }
    private function inrowCheck(array $lottery)
    {
        $inrow_lenght = 1;
        for ($i = 0; $i < $this->past_lotteries->getK()-1; $i++) {
            if ($lottery[$i] == $lottery[$i+1]-1) {
                $inrow_lenght++;
            } elseif ($inrow_lenght == $this->n) {
                return true;
            } else {
                $inrow_lenght = 1;
            }
            if (
                $i == $this->past_lotteries->getK()-2
                && $inrow_lenght == $this->n
            ) {
                return true;
            }
        }
        return false;
    }
    private function drawStats()
    {
        
    }
    private function remove()
    {
        
    }
}