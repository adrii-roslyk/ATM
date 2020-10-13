<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Title</title>
</head>
    <body>
        <?php
        try {
            $dataStr = file_get_contents ('data.json');
            $dataArr = json_decode ($dataStr,true);
        } catch (Exception $e) {
            $dataArr = [];
        }
        function getAvailableSum ($dataArr) {
            $sum = null;
            for ($i = 0; $i < count ($dataArr); $i++) {
                $denomination = $dataArr [$i] ['denomination'];
                $banknotes = $dataArr [$i] ['banknotes'];
                $oneDenominationSum = $denomination * $banknotes;
                $sum += $oneDenominationSum;
            }
            return $sum;
        }
        $availableSum = getAvailableSum ($dataArr ['availableBanknotes']);
        echo "<div><p>Доступно: $availableSum грн</p></div>";
        ?>
        <form action="index.php" method="post">
            <div><label for="input1">Введите сумму:</label></div><br>
            <div><input type="text" name="sum" id="input1" maxlength="6" size="5" autofocus></div><br>
            <div><input type="submit" value="Получить"></div>
        </form>
    </body>
</html>

