<?php

namespace Lottokiller\Game;

class AllCombinations
{
    private static $instance;
    private $all_combinations = [];
    private $numbers = [1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42];
    private $k = 5;
    private $initial_chances = 0;
    
    public static function getInstance(...$args)
    {
        if (!self::$instance) {
            self::$instance = new AllCombinations(...$args);
        }
        return self::$instance;
    }
    private function __clone()
    {
    }
    private function __construct(...$args)
    {
        //DOMYŚLNIE: zostaną wygenerowane kombinacje bez powtórzeń 5 z 42 liczb
        //Aby wygenerować inne kombinacje, podaj 2 parametry:
        //#1: tablica wszystkich liczb
        //#2: liczba elementów
        if (
            !empty($args)
            && is_array($args[0])
            && is_int($args[1])
        ) {
            $this->numbers = $args[0];
            $this->k = $args[1];
            echo '<p>JUPI</p>';
        }
        //OPCJA: zamiast generować kombinacje, można załadować wstępnie
        //przetworzone kombinacje z pliku; aby to zrobić, należy podać jego nazwę.
        if (
            !empty($args)
            && is_string($args[0])
        ) {
            //Obsługa pliku wsadowego...
        }
        $this->generate($this->numbers, $this->k, [], 0, $this->all_combinations);
    }
    private function generate($numbers, $k, $comb, $start, &$result)
    {
        if ($k === 0) {
            $result[] = $comb;
            return;
        }
        for ($i = $start; $i <= count($numbers) - $k; $i++) {
            $comb[] = $numbers[$i];
            $this->generate($numbers, $k - 1, $comb, $i + 1, $result);
            array_pop($comb);
        }
    }
    public function dump()
    {
        var_dump($this->all_combinations);
    }
    //
    // METODY 'IF'
    //
    public function ifExistsByIndex($index) {
        if (isset($this->all_combinations[$index])) {
            return true;
        } else {
            return false;
        }
    }
    public function ifExistsByNumbers(array $numbers, bool $echoo = false) {
        //Metoda sprawdza, czy w pozostałej puli kombinacji obiektu AllCombinations
        //znajduje się zadana kombinacja liczb.
        //Parametry:
        //$array: tablica liczb
        //$echoo: jeżeli "true", to metoda będzie wyświetlała komunikaty
        $numbers_string = '';
        $i = 0;
        sort($numbers);
        foreach ($numbers as $number) {
            $numbers_string .= $number;
            if ($i != count($numbers) - 1) {
                $numbers_string .= ',';
            }
            $i++;
        }
        if (count($numbers) != $this->getK()) {
            if ($echoo) {
                echo '<p>[' . $numbers_string . ']: brak</p>';
            }
            return false;
        }
        foreach ($this->getAllCombinations() as $combination) {
            if ($combination == $numbers) {
                if ($echoo) {
                    echo '<p>[' . $numbers_string . ']: JEST!</p>';
                }
                return true;
            }
        }
        if ($echoo) {
            echo '<p>[' . $numbers_string . ']: brak</p>';
        }
        return false;
    }
    //
    // METODY 'GET'
    //
    public function getAllCombinations()
    {
        //Metoda zwraca wszystkie kombinacje (poza tymi już usuniętymi
        //po zastosowaniu wybranych reguł)
        return $this->all_combinations;
    }
    public function getK()
    {
        //Metoda zwraca ilość liczb w pojedyńczej kombinacji
        return $this->k;
    }
    public function getCountOfNumbers()
    {
        //Metoda zwraca ilość wszystkich liczb, z jakich może powstać kombinacja (pula)
        return count($this->numbers);
    }
    public function getNumbers()
    {
        //Metoda zwraca pulę liczb, z jakich może powstać kombinacja
        return $this->numbers;
    }
    public function getCurrentChances()
    {
        //Metoda zwraca bieżące szanse na wygraną
        return '<p>Bieżące szanse na wygraną: 1 do ' . count($this->all_combinations) . '</p>';
    }
    //
    // METODY 'REMOVE'
    //
    public function removeCombination(array $to_remove)
    {
        //Metoda usuwa konkretną kombinację z puli wszystkich kombinacji;
        //parameterem wejściowym jest tablica, której pierwsze k-elementów to liczby
        //kombinacji, która ma zostać usunięta z puli (nie muszą być posortowane)
        $new_array = [];
        $new_array_index = 0;
        for ($i = 0; $i < $this->k; $i++) {
            $new_array[$new_array_index] = $to_remove[$new_array_index];
            $new_array_index++;
        }
        sort($new_array);
        for ($i = 0; $i < count($this->all_combinations) - 1; $i++) {
            if (
                isset($this->all_combinations[$i])
                && $this->all_combinations[$i] == $new_array
            ) {
                unset($this->all_combinations[$i]);
                return true;
            }
        }
        return false;
    }
    public function removeCombinationByIndex($index)
    {
        //Metoda usuwa kombinacje/wiersz o zadanym index'ie
        unset($this->all_combinations[$index]);
    }
    public function removeCombinationsByRule($rule)
    {
        //Metoda usuwająca te kombinacje z puli wszystkich kombinacji,
        //które zostaną wykluczone przez wstrzykniętą regułę (rule)
        $result_msg = $rule->apply($this);
        return $result_msg;
    }
}