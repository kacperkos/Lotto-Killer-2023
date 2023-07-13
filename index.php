<?php

require 'vendor/autoload.php';

use Lottokiller\Game\AllCombinations;
//use Lottokiller\Game\PastLotteries;
use Lottokiller\Rules\SumOfPastElements;
use Lottokiller\Rules\OmitPastLotteries;

?>
<html>
    <head>
        <meta http-equiv="content-type" content="text/html; charset=utf-8" />
        <link rel="stylesheet" type="text/css" href="css/style.css" />
        <title>Lotto-Killer</title>
    </head>
    <body>
<?php

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

//Wyświetlanie aktualnego stanu obiektu AllCombinations
//$all_combinations->dump();

//Header
echo '<p>Lotto-Killer | version: pre-alpha</p>';
echo '<p>GRA: ' . $all_combinations->getK() . ' z ' . $all_combinations->getCountOfNumbers() . ' liczb</p>';

//Wyświetlanie aktualnego prawdopodobieństwa na wygraną
echo '<p>';
echo $all_combinations->getCurrentChances();
echo '</p>';

//REGUŁA #1 (+objaśnienia)
//Sposób usuwania z obiektu AllCombinations kombinacji według przyjętej reguły
//Tworzenie obiektu reguły
$rule_1 = new SumOfPastElements();
//Sposób działania reguły można najpierw zwizualizować!
$rule_1->visualize();
//$all_combinations->removeCombinationsByRule($rule_1);
//Wywołanie poprzez echo spowoduje wyświetlenie komunikatu zwrotnego
echo '<p>';
echo $all_combinations->removeCombinationsByRule($rule_1);
unset($rule_1);
echo '</p>';
echo '<p>';
echo $all_combinations->getCurrentChances();
echo '</p>';

//REGUŁA #2
$rule_2 = new OmitPastLotteries();
$rule_2->visualize();
echo '<p>';
echo $all_combinations->removeCombinationsByRule($rule_2);
unset($rule_2);
echo '</p>';
echo '<p>';
echo $all_combinations->getCurrentChances();
echo '</p>';

?>
    </body>
</html>