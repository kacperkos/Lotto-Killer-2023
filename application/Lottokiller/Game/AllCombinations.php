<?php

namespace Lottokiller\Game;

class AllCombinations
{
    private static $instance;
    private $all_combinations = [];
    private $numbers = [1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42];
    private $k = 5;
    
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
        if(!empty($args) && is_array($args[0]) && is_int($args[1])) {
            $this->numbers = $args[0];
            $this->k = $args[1];
            var_dump($args);
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
    public function getAllCombinations()
    {
        return $this->all_combinations;
    }
    public function removeCombination(array $to_remove)
    {
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
            }
        }   
    }
    public function removeCombinationById($id)
    {
        unset($this->all_combinations[$id]);
    }
    public function removeCombinationsByRule($rule)
    {
        $result_msg = $rule->apply($this);
        return $result_msg;
    }
    public function getCurrentChances()
    {
        return 'Szanse na wygraną: 1 do ' . count($this->all_combinations);
    }    
}