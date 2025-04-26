<?php

// Получаем данные из POST или GET
$method = $_SERVER['REQUEST_METHOD'];
$data = $method === 'POST' ? $_POST : $_GET;

// Проверяем наличие обязательных полей
if (!isset($data['user_name'], $data['user_second_name'], $data['user_last_name'], $data['api_key'], $data['secret_key'])) {
    die('Не хватает данных для отправки запроса.');
}

// Склеиваем ФИО в одну строку
$fullname = trim($data['user_last_name'] . ' ' . $data['user_name'] . ' ' . $data['user_second_name']);

// Получаем ключи
$token = $data['api_key'];
$secret = $data['secret_key'];

// Формируем запрос к API Dadata
$url = "https://dadata.ru/api/v2/clean/name";
$options = [
    'http' => [
        'header'  => "Content-type: application/json\r\n" .
                     "Accept: application/json\r\n" .
                     "Authorization: Token $token\r\n" .
                     "X-Secret: $secret\r\n",
        'method'  => 'POST',
        'content' => json_encode([$fullname]),
    ],
];
$context  = stream_context_create($options);
$result = file_get_contents($url, false, $context);

// Обрабатываем ответ
if ($result === FALSE) {
    die('Ошибка при запросе к Dadata');
}

$response = json_decode($result, true);

// Выводим красиво
echo "<h1>Результат стандартизации ФИО:</h1>";

if (!empty($response[0])) {
    echo "<ul>";
    echo "<li><strong>Исходное ФИО:</strong> " . htmlspecialchars($response[0]['source']) . "</li>";
    echo "<li><strong>Стандартизованное ФИО:</strong> " . htmlspecialchars($response[0]['result']) . "</li>";
    echo "<li><strong>Фамилия:</strong> " . htmlspecialchars($response[0]['surname']) . "</li>";
    echo "<li><strong>Имя:</strong> " . htmlspecialchars($response[0]['name']) . "</li>";
    echo "<li><strong>Отчество:</strong> " . htmlspecialchars($response[0]['patronymic']) . "</li>";
    echo "<li><strong>Пол:</strong> " . htmlspecialchars($response[0]['gender']) . "</li>";
    echo "<li><strong>Родительный падеж:</strong> " . htmlspecialchars($response[0]['result_genitive']) . "</li>";
    echo "<li><strong>Дательный падеж:</strong> " . htmlspecialchars($response[0]['result_dative']) . "</li>";
    echo "<li><strong>Творительный падеж:</strong> " . htmlspecialchars($response[0]['result_ablative']) . "</li>";
    echo "</ul>";
} else {
    echo "Не удалось распознать ФИО.";
}
?>
