<?php

require 'vendor/autoload.php';

use Lottokiller\Game\AllCombinations;
//use Lottokiller\Game\PastLotteries;
use Lottokiller\Rules\SumOfPastElements;

//Sposoby inicjowania zbioru wszystkich możliwych kombinacji
//Obiekt AllCombinations to Singleton!
$all_combinations = AllCombinations::getInstance();
//$all_combinations = AllCombinations::getInstance([1,2,3,4,5], 3);

//Sposób usuwania dowolnych kombinacji z obiektu AllCombinations
//$all_combinations->removeCombination([1,2,3,4,5]);

//Sposoby usuwania z obiektu AllCombnations kombinacji, które już kiedyś padły
//Konieczne jest stworzenie obiektu PastLotteries! który wczyta plik źródłowy
//$past_lotteries = new PastLotteries();
//$all_combinations->removeCombination($past_lotteries->getLotteryByIndex(0));
//$all_combinations->removeCombination($past_lotteries->getLotteryById(13));
//echo $all_combinations->getCurrentChances();

//Wyświetlanie aktualnego prawdopodobieństwa na wygraną
echo $all_combinations->getCurrentChances();
echo '<br />';

//Sposób usuwania z obiektu AllCombinations kombinacji według przyjętej reguły
//Tworzenie obiektu reguły
$rule = new SumOfPastElements();
//Sposób działania reguły można najpierw zwizualizować!
//$rule->visualize();
//$all_combinations->removeCombinationsByRule($rule);
//Wywołanie poprzez echo spowoduje wyświetlenie komunikatu zwrotnego
echo $all_combinations->removeCombinationsByRule($rule);
echo '<br />';
unset($rule);
//Wyświetlanie aktualnego stanu obiektu AllCombinations
//$all_combinations->dump();

echo $all_combinations->getCurrentChances();
echo '<br />';