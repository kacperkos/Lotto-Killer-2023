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
    private $past_lotteries_inrow_chance = [];
    private $all_combinations;
    private $all_combinations_inrows_indexes = [];
    //CONFIG: N określa minimalną/dolną ilość elementów w rzędzie, który
    //zakwalifikuje kombinację z obiektu AllCombinations do usunięcia.
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
    public function setMinimumN (int $n)
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
        //wystąpienia n-elementowych rzędów liczb w kombinacjach
        for ($n = $this->n; $n <= $this->past_lotteries->getK(); $n++) {
            $past_lotteries_inrows_counter = 0;
            foreach ($this->past_lotteries->getAllLotteries() as $lottery) {
                if ($this->inrowCheck($n, $lottery)) {
                    $past_lotteries_inrows_counter++;
                }
            }
            $this->past_lotteries_inrow_chance[$n] = percentage($past_lotteries_inrows_counter, count($this->past_lotteries->getAllLotteries()));
        }
        //Znajdź index'y wszystkich n-elementowych rzędów w AllCombinations
        for ($n = $this->n; $n <= $this->all_combinations->getK(); $n++) {
            foreach ($this->all_combinations->getAllCombinations() as $index => $combination) {
                if ($this->inrowCheck($n, $combination)) {
                    $this->all_combinations_inrows_indexes[$n][] = $index;
                }
            }
        }
    }
    private function inrowCheck(int $n, array $lottery)
    {
        $inrow_lenght = 1;
        for ($i = 0; $i < $this->past_lotteries->getK()-1; $i++) {
            if ($lottery[$i] == $lottery[$i+1]-1) {
                $inrow_lenght++;
            } elseif ($inrow_lenght == $n) {
                return true;
            } else {
                $inrow_lenght = 1;
            }
            if (
                $i == $this->past_lotteries->getK()-2
                && $inrow_lenght == $n
            ) {
                return true;
            }
        }
        return false;
    }
    private function drawStats()
    {
        echo '<div class="visualizeBox">';
        echo '<p class="visualize">WIZUALIZACJA REGUŁY:<br/>&nbsp;&nbsp;&nbsp;&nbsp;<u>"' . $this->getName() . '"</u></p>';
        echo '<p class="visualize">OPIS REGUŁY:<br/ >&nbsp;&nbsp;&nbsp;&nbsp;' . $this->getDescription() . '</p>';
        echo '<p class="visualize">KONIGURACJA:<br />&nbsp;&nbsp;&nbsp;&nbsp;MINIMALNA DŁUGOŚĆ RZĘDU: <span style="color: red;">' . $this->n . '</span><br />&nbsp;&nbsp;&nbsp;&nbsp;PRÓG WAŻNOŚCI/POMINIĘCIA: <span style="color: red;">' . round($this->importance, 2) . '%</span></p>';
        for ($n = $this->n; $n <= $this->all_combinations->getK(); $n++) {
            echo '<p class="visualize" style="font-size: 13px; color: grey;">ILOŚĆ KOMBINACJI Z <span style="color: red;">' . $n . '</span>-ELEMENTOWYMI RZĘDAMI: <span style="color: red;">' . count($this->all_combinations_inrows_indexes[$n]) . '</span><br />SZACOWANE PRAWDOPODOBIEŃSTWO WYSTĄPIENIA KOMBINACJI Z TAKIMI RZĘDAMI W PRZYSZŁOŚCI: <span style="color: red;">' . round($this->past_lotteries_inrow_chance[$n], 2) . '%</span><br />';
            if ($this->past_lotteries_inrow_chance[$n] < $this->importance) {
                echo '<span style="color: red;">[ZAKWALIFIKOWANO DO USUNIĘCIA]</span></p>';
            } else {
                echo '<span style="color: grey;">[NIE ZAKWALIFIKOWANO DO USUNIĘCIA]</span></p>';
            }
        }
        echo '<p class="visualize" style="font-size: 13px; color: red;">UWAGA:<br /><span style="color: grey;">SUMARYCZNA ILOŚĆ USUNIĘTYCH KOMBINACJI MOŻE BYĆ MNIEJSZA, PONIEWAŻ NIEKTÓRE Z KOMBINACJI ZAWIERAJĄ WIĘCEJ NIŻ 1 N-ELEMENROWYCH RZĘDÓW,<br />NP. KOMBINACJA LICZB [2, 3, 39, 40, 41] ZAWIERA ZARÓWNO RZĄD 2-ELEMENTOWY JAK I 3-ELEMENTOWY;<br />KAŻDY Z TYCH RZĘDÓW ZOSTAŁ DODANY DO POWYŻSZYCH SUM OSOBNO, MIMO ŻE ZNAJDUJĄ SIĘ ONE W TEJ SAMEJ KOMBINACJI</span></p>';
        echo '</div>';
    }
    private function remove($all_combinations)
    {
        $removed_counter = 0;
        for ($n = $this->n; $n <= $this->past_lotteries->getK(); $n++) {
            if ($this->past_lotteries_inrow_chance[$n] < $this->importance) {
                foreach ($this->all_combinations_inrows_indexes[$n] as $index) {
                    if ($all_combinations->ifExistsByIndex($index)) {
                        $all_combinations->removeCombinationByIndex($index);
                        $removed_counter++;
                    }
                }
            }
        }
        return $removed_counter;
    }
}