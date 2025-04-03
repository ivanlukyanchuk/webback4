<?php
header('Content-Type: text/html; charset=UTF-8');

$dsn = 'mysql:host=localhost;dbname=u68581;charset=utf8';
$user = 'u68581';
$pass = '4027467';
$options = [
    PDO::ATTR_PERSISTENT => true,
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
];

try {
    $db = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die('Ошибка подключения к базе данных: ' . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $messages = [];

    if (!empty($_COOKIE['save'])) {
        setcookie('save', '', time() - 3600);
        $messages[] = 'Спасибо, результаты сохранены.';
    }

    $errors = [
        'fio' => !empty($_COOKIE['fio_error']),
        'phone' => !empty($_COOKIE['phone_error']),
        'email' => !empty($_COOKIE['email_error']),
        'dob' => !empty($_COOKIE['dob_error']),
        'gender' => !empty($_COOKIE['gender_error']),
        'languages' => !empty($_COOKIE['languages_error']),
        'bio' => !empty($_COOKIE['bio_error']),
        'contract' => !empty($_COOKIE['contract_error'])
    ];

    foreach ($errors as $key => $error) {
        if ($error) {
            setcookie("{$key}_error", '', time() - 3600);
            $messages[] = "<div class='error'>Ошибка в поле: {$key}</div>";
        }
    }

    $values = [
        'fio' => $_COOKIE['fio_value'] ?? '',
        'phone' => $_COOKIE['phone_value'] ?? '',
        'email' => $_COOKIE['email_value'] ?? '',
        'dob' => $_COOKIE['dob_value'] ?? '',
        'gender' => $_COOKIE['gender_value'] ?? '',
        'languages' => !empty($_COOKIE['languages_value']) ? json_decode($_COOKIE['languages_value'], true) : [],
        'bio' => $_COOKIE['bio_value'] ?? '',
        'contract' => !empty($_COOKIE['contract_value'])
    ];

    include('form.php');
} else {
    $errors = false;

    function validate($field, $pattern)
    {
        global $errors;
        if (empty($_POST[$field]) || !preg_match($pattern, $_POST[$field])) {
            setcookie("{$field}_error", '1', time() + 24 * 60 * 60);
            $errors = true;
        }
        setcookie("{$field}_value", $_POST[$field] ?? '', time() + 365 * 24 * 60 * 60);
    }

    validate('fio', '/^[a-zA-Zа-яА-Я\s]{1,150}$/u');
    validate('phone', '/^\+?\d{10,15}$/');
    validate('email', '/^[^@]+@[^@]+\.[a-zA-Z]{2,}$/');
    validate('dob', '/^\d{4}-\d{2}-\d{2}$/');

    if (empty($_POST['gender']) || !in_array($_POST['gender'], ['male', 'female'])) {
        setcookie('gender_error', '1', time() + 24 * 60 * 60);
        $errors = true;
    }
    setcookie('gender_value', $_POST['gender'] ?? '', time() + 365 * 24 * 60 * 60);

    if (empty($_POST['languages'])) {
        setcookie('languages_error', '1', time() + 24 * 60 * 60);
        $errors = true;
    }
    setcookie('languages_value', json_encode($_POST['languages'] ?? []), time() + 365 * 24 * 60 * 60);

    if (empty($_POST['bio'])) {
        setcookie('bio_error', '1', time() + 24 * 60 * 60);
        $errors = true;
    }
    setcookie('bio_value', $_POST['bio'] ?? '', time() + 365 * 24 * 60 * 60);

    if (empty($_POST['contract'])) {
        setcookie('contract_error', '1', time() + 24 * 60 * 60);
        $errors = true;
    }
    setcookie('contract_value', $_POST['contract'] ?? '', time() + 365 * 24 * 60 * 60);

    if ($errors) {
        header('Location: index.php');
        exit();
    }

    setcookie('fio_error', '', time() - 3600);
    setcookie('phone_error', '', time() - 3600);
    setcookie('email_error', '', time() - 3600);
    setcookie('dob_error', '', time() - 3600);
    setcookie('gender_error', '', time() - 3600);
    setcookie('languages_error', '', time() - 3600);
    setcookie('bio_error', '', time() - 3600);
    setcookie('contract_error', '', time() - 3600);

    try {
        $db->beginTransaction();

        $stmt = $db->prepare("INSERT INTO applications (full_name, phone, email, birth_date, gender, bio, contract) 
                              VALUES (:fio, :phone, :email, :dob, :gender, :bio, :contract)");
        $stmt->execute([
            ':fio' => $_POST['fio'],
            ':phone' => $_POST['phone'],
            ':email' => $_POST['email'],
            ':dob' => $_POST['dob'],
            ':gender' => $_POST['gender'],
            ':bio' => $_POST['bio'],
            ':contract' => isset($_POST['contract']) ? 1 : 0
        ]);

        $application_id = $db->lastInsertId();

        $stmt = $db->prepare("SELECT id FROM languages WHERE name = :name");
        $insertLang = $db->prepare("INSERT INTO languages (name) VALUES (:name)");
        $linkStmt = $db->prepare("INSERT INTO application_languages (application_id, language_id) 
                                  VALUES (:application_id, :language_id)");

        foreach ($_POST['languages'] as $language) {
            $stmt->execute([':name' => $language]);
            $languageData = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$languageData) {
                $insertLang->execute([':name' => $language]);
                $language_id = $db->lastInsertId();
            } else {
                $language_id = $languageData['id'];
            }

            $linkStmt->execute([
                ':application_id' => $application_id,
                ':language_id' => $language_id
            ]);
        }

        $db->commit();
        setcookie('save', '1', time() + 365 * 24 * 60 * 60);
        header('Location: index.php');
    } catch (PDOException $e) {
        $db->rollBack();
        die('Ошибка при сохранении данных: ' . $e->getMessage());
    }
}
?>
