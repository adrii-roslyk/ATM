<?php
//VARIABLES-------------------------------------------------------------------------------------------------------------

$requestSum = htmlspecialchars($_POST ['sum']); // сумма введенная пользователем
$residueSum = $requestSum; // разница между запрошенной пользователем суммой и начисленной для выдачи в ходе выполнения программы
$getSum = null; // сумма денег для выдачи пользователю (начисляется в ходе выполнения программы)

try {
    $dataStr = file_get_contents ('data.json');
    $dataArr = json_decode ($dataStr,true); // массив с информацией о доступных банкнотах
} catch (Exception $e) {
    $dataArr = [];
}

$availableSum = getAvailableSum ($dataArr ['availableBanknotes']); // сумма доступная в банкомате
$errValidForm = validateForm ($requestSum); // строка с описанием ошибки при некорректном вводе суммы
$noSum = verifySum ($requestSum, $availableSum); // строка с описанием ошибки при отсутствии требуемой суммы в банкомате
$noBanknotes = searchBanknotes ($dataArr, $residueSum); // строка с описанием ошибки при отсутствии необходимых банкнот
$errVerification = null; // строка с описанием ошибки при отсутствии требуемой суммы или банкнот в банкомате

if ($noSum) {
    $errVerification = $noSum;
} elseif ($noBanknotes) {
    $errVerification = $noBanknotes;
}

$totalUsedBanknotes = []; // массив с кол-вом использованных банкнот каждого номинала

//EXECUTION-------------------------------------------------------------------------------------------------------------

if ($errValidForm) { //проверяем корректность ввода суммы пользователем
    echo "<div><p>$errValidForm</p></div>";
    echo "<div><p>Доступно $availableSum грн</p></div>";
    buttonBack ();
} elseif ($errVerification) { //проверяем наличие требуемой суммы или банкнот в банкомате
    echo "<div><p>$errVerification</p></div>";
    echo "<div><p>Доступно $availableSum грн</p></div>";
    buttonBack();
} else {
    for ($i = 0; $i < count($dataArr ['availableBanknotes']); $i++) { // перебираем массив с инф. о доступных банкнотах
        $denomination = $dataArr ['availableBanknotes'] [$i] ['denomination']; // номинал купюр
        $banknotes = $dataArr ['availableBanknotes'] [$i] ['banknotes']; // кол-во доступных банкнот каждого нминала
        $usedBanknotes = null; //кол-во использованных банкнот
        while ($banknotes && $residueSum >= $denomination) {
            $getSum += $denomination;
            $residueSum -= $denomination;
            $banknotes--; // снимаем деньги
            $dataArr ['availableBanknotes'] [$i] ['banknotes'] = $banknotes;
            $usedBanknotes++;
        }
        if ($usedBanknotes) $totalUsedBanknotes [] = "$usedBanknotes шт. x $denomination грн"; // добавляем количество использованных банкнот в массив
    }

    $numberBanknotes = implode(', ', $totalUsedBanknotes); // объединяем элементы массива с кол-вом использ. банкнот в строку
    $availableSum = getAvailableSum($dataArr ['availableBanknotes']); //сумма доступная в банкомате после снятия денег

    echo "Получите $getSum грн </br></br>";
    echo "Число купюр: $numberBanknotes</br></br>";
    echo "Еще доступно $availableSum грн </br></br>";
    buttonBack();

    $newDataSrt = json_encode($dataArr, JSON_PRETTY_PRINT);
    $result = file_put_contents('data.json', $newDataSrt); // обновляем файл с инф. о доступных банкнотах
}

//FUNCTIONS-------------------------------------------------------------------------------------------------------------

function getAvailableSum ($dataArr) { // ф-ция для получения суммы доступной в банкомате
    $sum = null;
    for ($i = 0; $i < count ($dataArr); $i++) { // перебираем все имеющиеся номиналы купюр
        $denomination = $dataArr [$i] ['denomination']; // номинал купюр
        $banknotes = $dataArr [$i] ['banknotes']; // кол-во доступных банкнот каждого нминала
        $oneDenominationSum = $denomination * $banknotes; // узнаем сумму доступную купюрами одного номинала
        $sum += $oneDenominationSum; // узнаем общую сумму доступную купюрами всех номиналов
    }
    return $sum;
}

function validateForm ($requestSum) { // ф-ция для проверки корректности ввода суммы пользователем
    $errorValidation = null; // этой переменной присваиваем описание ошибки при некорректном вводе суммы
    switch (true) {
        case (empty ($requestSum)): // пользователь ввел 0 либо отправил пустую строку
            $errorValidation = 'Неверно указана сумма: ноль выдать невозможно либо поле для ввода суммы пустое';
            break;
        case (!is_numeric ($requestSum)): // пользователь ввел нечисловой символ
            $errorValidation = 'Неверно указана сумма: допускается ввод суммы только цифрами';
            break;
        case (!filter_var($requestSum, FILTER_VALIDATE_INT)): // пользователь ввел сумму дробным числом
            $errorValidation = 'Неверно указана сумма: не допускается ввод суммы дробным числом';
            break;
        case ($requestSum % 5 !== 0): // если при делении суммы на 5 есть остаток, сумма не кратна 5
            $errorValidation = 'Неверно указана сумма: сумма должна быть кратной 5';
    }
    return $errorValidation;
}

function verifySum ($requestSum, $availableSum) {  // ф-ция для проверки наличия требуемой суммы в банкомате
    $errorVerification = null;
    if ($requestSum > $availableSum) {
        $errorVerification = 'Указанная сумма не может быть выдана: в банкомате недостаточно денежных средств';
    }
    return $errorVerification;
}

function searchBanknotes ($dataArr, $residueSum) { //ф-ция проверяющая наличие требуемых банкнот в банкомате
    for ($i = 0; $i < count ($dataArr ['availableBanknotes']); $i++) {
        $denomination = $dataArr ['availableBanknotes'] [$i] ['denomination']; // номинал купюр
        $banknotes = $dataArr ['availableBanknotes'] [$i] ['banknotes']; // кол-во доступных банкнот каждого нминала
        while ($banknotes && $residueSum >= $denomination) {
            $residueSum -= $denomination;
            $banknotes--;
        }
    }
    if ($residueSum) {
        return "Не осталось необходимых купюр";
    } else {
        return false;
    }
}

function buttonBack () { // кнопка "Назад"
    echo '<form action="index.php">';
    echo '<button>Назад</button>';
    echo '<form>';
}










