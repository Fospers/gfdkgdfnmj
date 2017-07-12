<?php

$link = mysqli_connect('localhost', 'root', 'YFFTvL4Jtf5sA3Wrb','game'); //В переменной - коннект к БД.
mysqli_set_charset($link, "utf8"); // Устанавливаем кодировку в БД.

set_time_limit(0); //Убираем таймаут у скрипта, выполняется столько, сколько нужно.

// Установка токена


$botToken = "337374040:AAETzde7M4VI1yWDA68L-x2VJMV0ICQDLhE"; // В переменной - токен бота, который мы получили у BotFather'а.
$website = "https://api.telegram.org/bot".$botToken; // В переменной - авторизация бота через его токен.
$website_file = "https://api.telegram.org/file/bot".$botToken."/";

// Получаем запрос от Telegram

$content = file_get_contents("php://input"); // В переменной - все входящие данные от телеграмма. Webhook.

file_put_contents("response.txt",$content); //Кладём последний входящий хук от телеграмма в файл response.txt.

// Разгребаем этот запрос : Получаем внутренний номер чата Telegram и команду, введённую пользователем в чате и т.д.

$update = json_decode($content, TRUE); //В переменной - декодированные данные Webhook'а от телеграмма. TRUE - декодировать в т.ч. всё древо данных, а не только корень.

$message = $update['message']; // В переменной - массив данных (Webhook), который мы получили, когда пользователь отправил нам что-то простым сообщением или нижней кнопкой.
$callback_query = $update['callback_query']; //В переменной - массив данных (Webhook), который мы получили, когда пользователь отправил нам что-то верхней кнопкой.

$username = $message["from"]["username"];
$chat_id = $message["chat"]["id"]; // В переменной - уникальный id пользователя, который мы получили, когда пользователь отправил что-то СООБЩЕНИЕМ или НИЖНЕЙ кнопкой.
$text = $message["text"]; // В переменной - текст сообщения, который прислал нам пользователь, когда отправил что-то СООБЩЕНИЕМ или НИЖНЕЙ кнопкой (а не верхней!).

$data = $callback_query['data']; // В переменной - текущая информация отправки сообщения (типо любимое число), которое пользователь отправил верхней кнопкой.
$message_id = $callback_query['message']['message_id']; // В переменной - уникальный id сообщения для этого чата, который был получен от нажатия пользователем верхней кнопки.
$chat_id_in = $callback_query['message']['chat']['id']; // В переменной - уникальный id пользователя, который мы получили, когда пользователь отправил что-то ВЕРХНЕЙ кнопкой.

// Получаем всю информацию о юзере
///////////////////////////////////////123123123///////////////////////////////////////////////////////////////////////////////////////////

if($chat_id_in == true) // Если человек ввёл что-то с верхней кнопки, то:
{
	$result = mysqli_query($link,"SELECT * FROM users WHERE chat_id = $chat_id_in"); // В - переменную заносится вся строка пользователя со всей его инфой, которую мы нашли по его уникальному id, который получили из массива с данными, которые к нам пришли, когда он отправил нам что-то с верхней кнопки.
	$chat_id = $chat_id_in; // В переменной - уникальный id пользователя. Т.к. мы в скрипте повсюду используем переменную chat_id (а не chat_id_in) - то нужно её задать, ибо id есть (из-за того что хук пришёл с верхней кнопки) только в chat_id_in. Просто прировняли их.
}
else // Иначе (если человек ввёл что-то с нижней кнопки или текстом, то):
{
	$result = mysqli_query($link,"SELECT * FROM users WHERE chat_id = $chat_id"); // В переменную - заносится вся строка пользователя со всей его инфой, которую мы нашли по его уникальному id, который получили из массива с данными, которые к нам пришли, когда он отправил нам что-то с нижней кнопки или сообщением.
}

$attr = mysqli_fetch_array($result,MYSQLI_ASSOC); // В переменной - массив из полученных выше данных, точнее одна строка из базы, и все её столбцы. MYSQLI_ASSOC - вариант ассоциативного режима, т.е. когда будем забирать информацию из массива, нужно будет указать имя столбца, а не его номер (если нужно номер, то юзаем MYSQLI_NUM, либо MYSQLI_BOTH для того и того).

// Проверяем новый он или старый
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
$isOld = mysqli_num_rows($result); // В переменной - количество найденых строк с уникальным id пользователя в БД. Если 0 - то он новый. Если 1 - то уже был.

if(stripos($text,"/start") !== false and $isOld == 0) // Если текст пользователя = /start и он новенький, то:
{
	$fromReferal = substr($text, 7);
	$result = mysqli_query($link,"INSERT INTO users (chat_id, username, regStatus, fromReferal) VALUES ($chat_id,'$username',0,'$fromReferal')"); // В переменной - добавляется поле в БД, в таблицу юзеров, уникальный id юзера, рег статус = 0, и поинты = 0).

	$mes = "Choose your language:"; // В переменной - текст, который будет выводиться юзеру.

	$keyboard = [["Русский", "English"]]; // В переменной - массив, т.к. скобки - мы добавляем указанный массив в массив.

	$resp = array("keyboard" => $keyboard,"resize_keyboard" => true, "one_time_keyboard" => false); // В переменной - массив, с ключами и их значениями.
	$reply = json_encode($resp); // В переменной - кодируем данные(массив) в json для отправки.

	$url = $website."/sendmessage?chat_id=".$chat_id."&text=".urlencode($mes)."&reply_markup=".$reply; // В переменной - то, что мы отправляем телеграмму, дабы дать пользователю ответ.

	file_get_contents($url); // Собственно отправляем телеграму всю эту инфу.

	return; // Чтоб дальше не выполнялся скрипт.
}
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if($attr["regStatus"] == 0) // Чел прислал нам свой язык, если всё корректно - добавляем ему язык, и отсылаем запрос на выбор рассы.
{
	if($text == 'Russian' or $text == 'English' or $text == 'Русский')
	{
		if($text == 'Russian' or $text == 'Русский')
		{
			$result = mysqli_query($link,"SELECT rus from text where id = 1"); // В переменной - выбираем столбец rus из таблицы text, где столбец id равен 1. Это просто поиск всей строки со всеми данными.
			$mes = mysqli_fetch_array($result,MYSQLI_ASSOC)["rus"]; // В переменной - текст сообщения, который мы отправляем пользователю, выбираем текст из столбца RUS.
			$result = mysqli_query($link,"UPDATE users SET regStatus = 1, lang = 'Russian' where chat_id = $chat_id"); // В переменной - обновляем данные таблицы users, задаём столбец RegStatus = 1, столбец lang = Russian, тому пользователю, у которого его chat_id = его уникальному id.
			$race1q = mysqli_query($link,"SELECT name_rus from races where id = 1");
			$race1 = mysqli_fetch_array($race1q,MYSQLI_ASSOC)["name_rus"];
			$race2q = mysqli_query($link,"SELECT name_rus from races where id = 2");
			$race2 = mysqli_fetch_array($race2q,MYSQLI_ASSOC)["name_rus"];
			$race3q = mysqli_query($link,"SELECT name_rus from races where id = 3");
			$race3 = mysqli_fetch_array($race3q,MYSQLI_ASSOC)["name_rus"];
			$race4q = mysqli_query($link,"SELECT name_rus from races where id = 4");
			$race4 = mysqli_fetch_array($race4q,MYSQLI_ASSOC)["name_rus"];
			$race5q = mysqli_query($link,"SELECT name_rus from races where id = 5");
			$race5 = mysqli_fetch_array($race5q,MYSQLI_ASSOC)["name_rus"];
			$race6q = mysqli_query($link,"SELECT name_rus from races where id = 6");
			$race6 = mysqli_fetch_array($race6q,MYSQLI_ASSOC)["name_rus"];
			$race7q = mysqli_query($link,"SELECT name_rus from races where id = 7");
			$race7 = mysqli_fetch_array($race7q,MYSQLI_ASSOC)["name_rus"];
			$race8q = mysqli_query($link,"SELECT name_rus from races where id = 8");
			$race8 = mysqli_fetch_array($race8q,MYSQLI_ASSOC)["name_rus"];
		}
		if($text == 'English')
		{
			$result = mysqli_query($link,"SELECT eng from text where id = 1");
			$mes = mysqli_fetch_array($result,MYSQLI_ASSOC)["eng"];
			$result = mysqli_query($link,"UPDATE users SET regStatus = 1, lang = 'English' where chat_id = $chat_id");
			$race1q = mysqli_query($link,"SELECT name_eng from races where id = 1");
			$race1 = mysqli_fetch_array($race1q,MYSQLI_ASSOC)["name_eng"];
			$race2q = mysqli_query($link,"SELECT name_eng from races where id = 2");
			$race2 = mysqli_fetch_array($race2q,MYSQLI_ASSOC)["name_eng"];
			$race3q = mysqli_query($link,"SELECT name_eng from races where id = 3");
			$race3 = mysqli_fetch_array($race3q,MYSQLI_ASSOC)["name_eng"];
			$race4q = mysqli_query($link,"SELECT name_eng from races where id = 4");
			$race4 = mysqli_fetch_array($race4q,MYSQLI_ASSOC)["name_eng"];
			$race5q = mysqli_query($link,"SELECT name_eng from races where id = 5");
			$race5 = mysqli_fetch_array($race5q,MYSQLI_ASSOC)["name_eng"];
			$race6q = mysqli_query($link,"SELECT name_eng from races where id = 6");
			$race6 = mysqli_fetch_array($race6q,MYSQLI_ASSOC)["name_eng"];
			$race7q = mysqli_query($link,"SELECT name_eng from races where id = 7");
			$race7 = mysqli_fetch_array($race7q,MYSQLI_ASSOC)["name_eng"];
			$race8q = mysqli_query($link,"SELECT name_eng from races where id = 8");
			$race8 = mysqli_fetch_array($race8q,MYSQLI_ASSOC)["name_eng"];

		}
		$keyboard = [[$race1, $race2, $race8],[$race3, $race5],[$race4, $race7, $race6]];
		$resp = array("keyboard" => $keyboard,"resize_keyboard" => true, "one_time_keyboard" => false);
		$reply = json_encode($resp);
		$url = $website."/sendmessage?chat_id=".$chat_id."&parse_mode=".'HTML'."&text=".urlencode($mes)."&reply_markup=".$reply;
		file_get_contents($url);
		return;
	}
	else
	{
		$mes = "Choose your language:";
		$keyboard = [["Русский", "English"]];
		$resp = array("keyboard" => $keyboard,"resize_keyboard" => true, "one_time_keyboard" => false);
		$reply = json_encode($resp);
		$url = $website."/sendmessage?chat_id=".$chat_id."&text=".urlencode($mes)."&reply_markup=".$reply;
		file_get_contents($url);
		return;
	}
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if($attr["regStatus"] == 1) // Чел отправил нам свою расу, проверяем, если корректно - добавляем в его профиль, если нет - повторяем регстатус 0. Отправляем в ответку выбор класса.
{
	$result = mysqli_query($link,"SELECT * FROM races WHERE name_rus = '$text' OR name_eng = '$text'");
	$raceid = mysqli_fetch_array($result,MYSQLI_ASSOC)["id"];
	if($raceid >= 1 and $raceid <= 8) // Если в тексте с рассой, который прислал юзер есть хоть какая-нибудь правильно-выбранная расса, то:
	{
		$result = mysqli_query($link,"SELECT * FROM races WHERE id = '$raceid'");
		$racename = mysqli_fetch_array($result,MYSQLI_ASSOC)["name"];
		if($attr["lang"] == 'Russian')
		{
			switch($raceid)
			{
				case 1:
					$result = mysqli_query($link,"SELECT rus from text where id = 2");
					$mes = mysqli_fetch_array($result,MYSQLI_ASSOC)["rus"]; // В переменной - текст сообщения, который мы отправляем пользователю.
					$result = mysqli_query($link,"UPDATE users SET regStatus = 2, race = '$racename', location = 'СтартоваяЛокацияЛюдей' where chat_id = $chat_id"); // В переменной - обновляем данные таблицы users, задаём столбец RegStatus = 2, столбец race = выбранная раса, тому пользователю, у которого его chat_id = его уникальному id.
					$class1q = mysqli_query($link,"SELECT name_rus from classes where id = 1");
					$class1 = mysqli_fetch_array($class1q,MYSQLI_ASSOC)["name_rus"];
					$class2q = mysqli_query($link,"SELECT name_rus from classes where id = 7");
					$class2 = mysqli_fetch_array($class2q,MYSQLI_ASSOC)["name_rus"];
					$class3q = mysqli_query($link,"SELECT name_rus from classes where id = 25");
					$class3 = mysqli_fetch_array($class3q,MYSQLI_ASSOC)["name_rus"];
					break;

				case 2:
					$result = mysqli_query($link,"SELECT rus from text where id = 2");
					$mes = mysqli_fetch_array($result,MYSQLI_ASSOC)["rus"];
					$result = mysqli_query($link,"UPDATE users SET regStatus = 2, race = '$racename', location = 'СтартоваяЛокацияОрков' where chat_id = $chat_id");
					$class1q = mysqli_query($link,"SELECT name_rus from classes where id = 1");
					$class1 = mysqli_fetch_array($class1q,MYSQLI_ASSOC)["name_rus"];
					$class2q = mysqli_query($link,"SELECT name_rus from classes where id = 13");
					$class2 = mysqli_fetch_array($class2q,MYSQLI_ASSOC)["name_rus"];
					$class3q = mysqli_query($link,"SELECT name_rus from classes where id = 19");
					$class3 = mysqli_fetch_array($class3q,MYSQLI_ASSOC)["name_rus"];
					break;

				case 3:
					$result = mysqli_query($link,"SELECT rus from text where id = 2");
					$mes = mysqli_fetch_array($result,MYSQLI_ASSOC)["rus"];
					$result = mysqli_query($link,"UPDATE users SET regStatus = 2, race = '$racename', location = 'СтартоваяЛокацияВысшихЭльфов' where chat_id = $chat_id");
					$class1q = mysqli_query($link,"SELECT name_rus from classes where id = 7");
					$class1 = mysqli_fetch_array($class1q,MYSQLI_ASSOC)["name_rus"];
					$class2q = mysqli_query($link,"SELECT name_rus from classes where id = 19");
					$class2 = mysqli_fetch_array($class2q,MYSQLI_ASSOC)["name_rus"];
					$class3q = mysqli_query($link,"SELECT name_rus from classes where id = 25");
					$class3 = mysqli_fetch_array($class3q,MYSQLI_ASSOC)["name_rus"];
					break;

				case 4:
					$result = mysqli_query($link,"SELECT rus from text where id = 2");
					$mes = mysqli_fetch_array($result,MYSQLI_ASSOC)["rus"];
					$result = mysqli_query($link,"UPDATE users SET regStatus = 2, race = '$racename', location = 'СтартоваяЛокацияГоблинов' where chat_id = $chat_id");
					$class1q = mysqli_query($link,"SELECT name_rus from classes where id = 1");
					$class1 = mysqli_fetch_array($class1q,MYSQLI_ASSOC)["name_rus"];
					$class2q = mysqli_query($link,"SELECT name_rus from classes where id = 7");
					$class2 = mysqli_fetch_array($class2q,MYSQLI_ASSOC)["name_rus"];
					$class3q = mysqli_query($link,"SELECT name_rus from classes where id = 25");
					$class3 = mysqli_fetch_array($class3q,MYSQLI_ASSOC)["name_rus"];
					break;

				case 5:
					$result = mysqli_query($link,"SELECT rus from text where id = 2");
					$mes = mysqli_fetch_array($result,MYSQLI_ASSOC)["rus"];
					$result = mysqli_query($link,"UPDATE users SET regStatus = 2, race = '$racename', location = 'СтартоваяЛокацияТёмныхЭльфов' where chat_id = $chat_id");
					$class1q = mysqli_query($link,"SELECT name_rus from classes where id = 7");
					$class1 = mysqli_fetch_array($class1q,MYSQLI_ASSOC)["name_rus"];
					$class2q = mysqli_query($link,"SELECT name_rus from classes where id = 13");
					$class2 = mysqli_fetch_array($class2q,MYSQLI_ASSOC)["name_rus"];
					$class3q = mysqli_query($link,"SELECT name_rus from classes where id = 19");
					$class3 = mysqli_fetch_array($class3q,MYSQLI_ASSOC)["name_rus"];
					break;

				case 6:
					$result = mysqli_query($link,"SELECT rus from text where id = 2");
					$mes = mysqli_fetch_array($result,MYSQLI_ASSOC)["rus"];
					$result = mysqli_query($link,"UPDATE users SET regStatus = 2, race = '$racename', location = 'СтартоваяЛокацияТроллей' where chat_id = $chat_id");
					$class1q = mysqli_query($link,"SELECT name_rus from classes where id = 1");
					$class1 = mysqli_fetch_array($class1q,MYSQLI_ASSOC)["name_rus"];
					$class2q = mysqli_query($link,"SELECT name_rus from classes where id = 13");
					$class2 = mysqli_fetch_array($class2q,MYSQLI_ASSOC)["name_rus"];
					$class3q = mysqli_query($link,"SELECT name_rus from classes where id = 19");
					$class3 = mysqli_fetch_array($class3q,MYSQLI_ASSOC)["name_rus"];
					break;

				case 7:
					$result = mysqli_query($link,"SELECT rus from text where id = 2");
					$mes = mysqli_fetch_array($result,MYSQLI_ASSOC)["rus"];
					$result = mysqli_query($link,"UPDATE users SET regStatus = 2, race = '$racename', location = 'СтартоваяЛокацияГномов' where chat_id = $chat_id");
					$class1q = mysqli_query($link,"SELECT name_rus from classes where id = 1");
					$class1 = mysqli_fetch_array($class1q,MYSQLI_ASSOC)["name_rus"];
					$class2q = mysqli_query($link,"SELECT name_rus from classes where id = 19");
					$class2 = mysqli_fetch_array($class2q,MYSQLI_ASSOC)["name_rus"];
					$class3q = mysqli_query($link,"SELECT name_rus from classes where id = 25");
					$class3 = mysqli_fetch_array($class3q,MYSQLI_ASSOC)["name_rus"];
					break;

				case 8:
					$result = mysqli_query($link,"SELECT rus from text where id = 2");
					$mes = mysqli_fetch_array($result,MYSQLI_ASSOC)["rus"];
					$result = mysqli_query($link,"UPDATE users SET regStatus = 2, race = '$racename', location = 'СтартоваяЛокацияДворфов' where chat_id = $chat_id");
					$class1q = mysqli_query($link,"SELECT name_rus from classes where id = 7");
					$class1 = mysqli_fetch_array($class1q,MYSQLI_ASSOC)["name_rus"];
					$class2q = mysqli_query($link,"SELECT name_rus from classes where id = 13");
					$class2 = mysqli_fetch_array($class2q,MYSQLI_ASSOC)["name_rus"];
					$class3q = mysqli_query($link,"SELECT name_rus from classes where id = 25");
					$class3 = mysqli_fetch_array($class3q,MYSQLI_ASSOC)["name_rus"];
					break;
			}
		}
		if($attr["lang"] == 'English')
		{
			switch($result)
			{
				case 1:
					$result = mysqli_query($link,"SELECT eng from text where id = 2");
					$mes = mysqli_fetch_array($result,MYSQLI_ASSOC)["eng"];
					$result = mysqli_query($link,"UPDATE users SET regStatus = 2, race = '$racename', location = 'StartHumanLocation' where chat_id = $chat_id");
					$class1q = mysqli_query($link,"SELECT name_eng from classes where id = 1");
					$class1 = mysqli_fetch_array($class1q,MYSQLI_ASSOC)["name_eng"];
					$class2q = mysqli_query($link,"SELECT name_eng from classes where id = 7");
					$class2 = mysqli_fetch_array($class2q,MYSQLI_ASSOC)["name_eng"];
					$class3q = mysqli_query($link,"SELECT name_eng from classes where id = 25");
					$class3 = mysqli_fetch_array($class3q,MYSQLI_ASSOC)["name_eng"];
					break;

				case 2:
					$result = mysqli_query($link,"SELECT eng from text where id = 2");
					$mes = mysqli_fetch_array($result,MYSQLI_ASSOC)["eng"];
					$result = mysqli_query($link,"UPDATE users SET regStatus = 2, race = '$racename', location = 'StartOrcLocation' where chat_id = $chat_id");
					$class1q = mysqli_query($link,"SELECT name_eng from classes where id = 1");
					$class1 = mysqli_fetch_array($class1q,MYSQLI_ASSOC)["name_eng"];
					$class2q = mysqli_query($link,"SELECT name_eng from classes where id = 13");
					$class2 = mysqli_fetch_array($class2q,MYSQLI_ASSOC)["name_eng"];
					$class3q = mysqli_query($link,"SELECT name_eng from classes where id = 19");
					$class3 = mysqli_fetch_array($class3q,MYSQLI_ASSOC)["name_eng"];
					break;

				case 3:
					$result = mysqli_query($link,"SELECT eng from text where id = 2");
					$mes = mysqli_fetch_array($result,MYSQLI_ASSOC)["eng"];
					$result = mysqli_query($link,"UPDATE users SET regStatus = 2, race = '$racename', location = 'StartHighElfLocation' where chat_id = $chat_id");
					$class1q = mysqli_query($link,"SELECT name_eng from classes where id = 7");
					$class1 = mysqli_fetch_array($class1q,MYSQLI_ASSOC)["name_eng"];
					$class2q = mysqli_query($link,"SELECT name_eng from classes where id = 19");
					$class2 = mysqli_fetch_array($class2q,MYSQLI_ASSOC)["name_eng"];
					$class3q = mysqli_query($link,"SELECT name_eng from classes where id = 25");
					$class3 = mysqli_fetch_array($class3q,MYSQLI_ASSOC)["name_eng"];
					break;

				case 4:
					$result = mysqli_query($link,"SELECT eng from text where id = 2");
					$mes = mysqli_fetch_array($result,MYSQLI_ASSOC)["eng"];
					$result = mysqli_query($link,"UPDATE users SET regStatus = 2, race = '$racename', location = 'StartGoblinLocation' where chat_id = $chat_id");
					$class1q = mysqli_query($link,"SELECT name_eng from classes where id = 1");
					$class1 = mysqli_fetch_array($class1q,MYSQLI_ASSOC)["name_eng"];
					$class2q = mysqli_query($link,"SELECT name_eng from classes where id = 7");
					$class2 = mysqli_fetch_array($class2q,MYSQLI_ASSOC)["name_eng"];
					$class3q = mysqli_query($link,"SELECT name_eng from classes where id = 25");
					$class3 = mysqli_fetch_array($class3q,MYSQLI_ASSOC)["name_eng"];
					break;

				case 5:
					$result = mysqli_query($link,"SELECT eng from text where id = 2");
					$mes = mysqli_fetch_array($result,MYSQLI_ASSOC)["eng"];
					$result = mysqli_query($link,"UPDATE users SET regStatus = 2, race = '$racename', location = 'StartDarkElfLocation' where chat_id = $chat_id");
					$class1q = mysqli_query($link,"SELECT name_eng from classes where id = 7");
					$class1 = mysqli_fetch_array($class1q,MYSQLI_ASSOC)["name_eng"];
					$class2q = mysqli_query($link,"SELECT name_eng from classes where id = 13");
					$class2 = mysqli_fetch_array($class2q,MYSQLI_ASSOC)["name_eng"];
					$class3q = mysqli_query($link,"SELECT name_eng from classes where id = 19");
					$class3 = mysqli_fetch_array($class3q,MYSQLI_ASSOC)["name_eng"];
					break;

				case 6:
					$result = mysqli_query($link,"SELECT eng from text where id = 2");
					$mes = mysqli_fetch_array($result,MYSQLI_ASSOC)["eng"];
					$result = mysqli_query($link,"UPDATE users SET regStatus = 2, race = '$racename', location = 'StartTrollLocation' where chat_id = $chat_id");
					$class1q = mysqli_query($link,"SELECT name_eng from classes where id = 1");
					$class1 = mysqli_fetch_array($class1q,MYSQLI_ASSOC)["name_eng"];
					$class2q = mysqli_query($link,"SELECT name_eng from classes where id = 13");
					$class2 = mysqli_fetch_array($class2q,MYSQLI_ASSOC)["name_eng"];
					$class3q = mysqli_query($link,"SELECT name_eng from classes where id = 19");
					$class3 = mysqli_fetch_array($class3q,MYSQLI_ASSOC)["name_eng"];
					break;

				case 7:
					$result = mysqli_query($link,"SELECT eng from text where id = 2");
					$mes = mysqli_fetch_array($result,MYSQLI_ASSOC)["eng"];
					$result = mysqli_query($link,"UPDATE users SET regStatus = 2, race = '$racename', location = 'StartGnomeLocation' where chat_id = $chat_id");
					$class1q = mysqli_query($link,"SELECT name_eng from classes where id = 1");
					$class1 = mysqli_fetch_array($class1q,MYSQLI_ASSOC)["name_eng"];
					$class2q = mysqli_query($link,"SELECT name_eng from classes where id = 19");
					$class2 = mysqli_fetch_array($class2q,MYSQLI_ASSOC)["name_eng"];
					$class3q = mysqli_query($link,"SELECT name_eng from classes where id = 25");
					$class3 = mysqli_fetch_array($class3q,MYSQLI_ASSOC)["name_eng"];
					break;

				case 8:
					$result = mysqli_query($link,"SELECT eng from text where id = 2");
					$mes = mysqli_fetch_array($result,MYSQLI_ASSOC)["eng"];
					$result = mysqli_query($link,"UPDATE users SET regStatus = 2, race = '$racename', location = 'StartDwarfLocation' where chat_id = $chat_id");
					$class1q = mysqli_query($link,"SELECT name_eng from classes where id = 7");
					$class1 = mysqli_fetch_array($class1q,MYSQLI_ASSOC)["name_eng"];
					$class2q = mysqli_query($link,"SELECT name_eng from classes where id = 13");
					$class2 = mysqli_fetch_array($class2q,MYSQLI_ASSOC)["name_eng"];
					$class3q = mysqli_query($link,"SELECT name_eng from classes where id = 25");
					$class3 = mysqli_fetch_array($class3q,MYSQLI_ASSOC)["name_eng"];
					break;
			}
		}
		$keyboard = [[$class1, $class2, $class3]];
		$resp = array("keyboard" => $keyboard,"resize_keyboard" => true, "one_time_keyboard" => false);
		$reply = json_encode($resp);
		$url = $website."/sendmessage?chat_id=".$chat_id."&parse_mode=".'HTML'."&text=".urlencode($mes)."&reply_markup=".$reply;
		file_get_contents($url);
		return;
	}
	else
	{
		$result = mysqli_query($link,"SELECT * FROM races WHERE name_rus = '$text' OR name_eng = '$text'");
		$raceid = mysqli_fetch_array($result,MYSQLI_ASSOC)["id"];
		if($attr["lang"] == 'Russian') // Если текст равен Russian, то:
		{
			$result = mysqli_query($link,"SELECT rus from text where id = 1"); // В переменной - выбираем столбец rus из таблицы text, где столбец id равен 1. Это просто поиск всей строки со всеми данными.
			$mes = mysqli_fetch_array($result,MYSQLI_ASSOC)["rus"]; // В переменной - текст сообщения, который мы отправляем пользователю, выбираем текст из столбца RUS.
			$result = mysqli_query($link,"UPDATE users SET regStatus = 1, lang = 'Russian' where chat_id = $chat_id"); // В переменной - обновляем данные таблицы users, задаём столбец RegStatus = 1, столбец lang = Russian, тому пользователю, у которого его chat_id = его уникальному id.
			$race1q = mysqli_query($link,"SELECT name_rus from races where id = 1");
			$race1 = mysqli_fetch_array($race1q,MYSQLI_ASSOC)["name_rus"];
			$race2q = mysqli_query($link,"SELECT name_rus from races where id = 2");
			$race2 = mysqli_fetch_array($race2q,MYSQLI_ASSOC)["name_rus"];
			$race3q = mysqli_query($link,"SELECT name_rus from races where id = 3");
			$race3 = mysqli_fetch_array($race3q,MYSQLI_ASSOC)["name_rus"];
			$race4q = mysqli_query($link,"SELECT name_rus from races where id = 4");
			$race4 = mysqli_fetch_array($race4q,MYSQLI_ASSOC)["name_rus"];
			$race5q = mysqli_query($link,"SELECT name_rus from races where id = 5");
			$race5 = mysqli_fetch_array($race5q,MYSQLI_ASSOC)["name_rus"];
			$race6q = mysqli_query($link,"SELECT name_rus from races where id = 6");
			$race6 = mysqli_fetch_array($race6q,MYSQLI_ASSOC)["name_rus"];
			$race7q = mysqli_query($link,"SELECT name_rus from races where id = 7");
			$race7 = mysqli_fetch_array($race7q,MYSQLI_ASSOC)["name_rus"];
			$race8q = mysqli_query($link,"SELECT name_rus from races where id = 8");
			$race8 = mysqli_fetch_array($race8q,MYSQLI_ASSOC)["name_rus"];
		}
		if($attr["lang"] == 'English')
		{
			$result = mysqli_query($link,"SELECT eng from text where id = 1");
			$mes = mysqli_fetch_array($result,MYSQLI_ASSOC)["eng"];
			$result = mysqli_query($link,"UPDATE users SET regStatus = 1, lang = 'English' where chat_id = $chat_id");
			$race1q = mysqli_query($link,"SELECT name_eng from races where id = 1");
			$race1 = mysqli_fetch_array($race1q,MYSQLI_ASSOC)["name_eng"];
			$race2q = mysqli_query($link,"SELECT name_eng from races where id = 2");
			$race2 = mysqli_fetch_array($race2q,MYSQLI_ASSOC)["name_eng"];
			$race3q = mysqli_query($link,"SELECT name_eng from races where id = 3");
			$race3 = mysqli_fetch_array($race3q,MYSQLI_ASSOC)["name_eng"];
			$race4q = mysqli_query($link,"SELECT name_eng from races where id = 4");
			$race4 = mysqli_fetch_array($race4q,MYSQLI_ASSOC)["name_eng"];
			$race5q = mysqli_query($link,"SELECT name_eng from races where id = 5");
			$race5 = mysqli_fetch_array($race5q,MYSQLI_ASSOC)["name_eng"];
			$race6q = mysqli_query($link,"SELECT name_eng from races where id = 6");
			$race6 = mysqli_fetch_array($race6q,MYSQLI_ASSOC)["name_eng"];
			$race7q = mysqli_query($link,"SELECT name_eng from races where id = 7");
			$race7 = mysqli_fetch_array($race7q,MYSQLI_ASSOC)["name_eng"];
			$race8q = mysqli_query($link,"SELECT name_eng from races where id = 8");
			$race8 = mysqli_fetch_array($race8q,MYSQLI_ASSOC)["name_eng"];

		}
		$keyboard = [[$race1, $race2, $race8],[$race3, $race5],[$race4, $race7, $race6]];
		$resp = array("keyboard" => $keyboard,"resize_keyboard" => true, "one_time_keyboard" => false);
		$reply = json_encode($resp);
		$url = $website."/sendmessage?chat_id=".$chat_id."&parse_mode=".'HTML'."&text=".urlencode($mes)."&reply_markup=".$reply;
		file_get_contents($url);
		return;
	}

}
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if($attr["regStatus"] == 2) // Чел отправил нам свой класс, проверяем, если корректно - добавляем в его профиль, если нет - повторяем регстатус 1. Отправляем в ответку просьбу написать свой ник.
{
	$result = mysqli_query($link,"SELECT * FROM classes WHERE name_rus = '$text' OR name_eng = '$text'");
	$classid = mysqli_fetch_array($result,MYSQLI_ASSOC)["id"];
	if($classid == 1 or $classid == 7 or $classid == 13 or $classid == 19 or $classid == 25)
	{
		$result = mysqli_query($link,"SELECT name from classes where id = $classid"); //////////вопрос Максу
		$classname = mysqli_fetch_array($result,MYSQLI_ASSOC)["name"];
		if($attr["lang"] == 'Russian')
		{
			$result = mysqli_query($link,"SELECT rus from text where id = 3");
			$mes = mysqli_fetch_array($result,MYSQLI_ASSOC)["rus"];
			$result = mysqli_query($link,"UPDATE users SET regStatus = 3, class = '$classname' where chat_id = $chat_id");
		}
		if($attr["lang"] == 'English')
		{
			$result = mysqli_query($link,"SELECT eng from text where id = 3");
			$mes = mysqli_fetch_array($result,MYSQLI_ASSOC)["eng"];
			$result = mysqli_query($link,"UPDATE users SET regStatus = 3, class = '$classname' where chat_id = $chat_id");
		}
		$resp = array("remove_keyboard" => true);
		$reply = json_encode($resp);
		$url = $website."/sendmessage?chat_id=".$chat_id."&parse_mode=".'HTML'."&text=".urlencode($mes)."&reply_markup=".$reply;
		file_get_contents($url);

		return;
	}
	else
	{
		$result = mysqli_query($link,"SELECT * FROM races WHERE name = '$attr[race]'");
		$raceid = mysqli_fetch_array($result,MYSQLI_ASSOC)["id"];
		if($attr["lang"] == 'Russian')
		{
			switch($raceid)
			{
				case 1:
					$result = mysqli_query($link,"SELECT rus from text where id = 2");
					$mes = mysqli_fetch_array($result,MYSQLI_ASSOC)["rus"];
					$class1q = mysqli_query($link,"SELECT name_rus from classes where id = 1");
					$class1 = mysqli_fetch_array($class1q,MYSQLI_ASSOC)["name_rus"];
					$class2q = mysqli_query($link,"SELECT name_rus from classes where id = 7");
					$class2 = mysqli_fetch_array($class2q,MYSQLI_ASSOC)["name_rus"];
					$class3q = mysqli_query($link,"SELECT name_rus from classes where id = 25");
					$class3 = mysqli_fetch_array($class3q,MYSQLI_ASSOC)["name_rus"];
					break;

				case 2:
					$result = mysqli_query($link,"SELECT rus from text where id = 2");
					$mes = mysqli_fetch_array($result,MYSQLI_ASSOC)["rus"];
					$class1q = mysqli_query($link,"SELECT name_rus from classes where id = 1");
					$class1 = mysqli_fetch_array($class1q,MYSQLI_ASSOC)["name_rus"];
					$class2q = mysqli_query($link,"SELECT name_rus from classes where id = 13");
					$class2 = mysqli_fetch_array($class2q,MYSQLI_ASSOC)["name_rus"];
					$class3q = mysqli_query($link,"SELECT name_rus from classes where id = 19");
					$class3 = mysqli_fetch_array($class3q,MYSQLI_ASSOC)["name_rus"];
					break;

				case 3:
					$result = mysqli_query($link,"SELECT rus from text where id = 2");
					$mes = mysqli_fetch_array($result,MYSQLI_ASSOC)["rus"];
					$class1q = mysqli_query($link,"SELECT name_rus from classes where id = 7");
					$class1 = mysqli_fetch_array($class1q,MYSQLI_ASSOC)["name_rus"];
					$class2q = mysqli_query($link,"SELECT name_rus from classes where id = 19");
					$class2 = mysqli_fetch_array($class2q,MYSQLI_ASSOC)["name_rus"];
					$class3q = mysqli_query($link,"SELECT name_rus from classes where id = 25");
					$class3 = mysqli_fetch_array($class3q,MYSQLI_ASSOC)["name_rus"];
					break;

				case 4:
					$result = mysqli_query($link,"SELECT rus from text where id = 2");
					$mes = mysqli_fetch_array($result,MYSQLI_ASSOC)["rus"];
					$class1q = mysqli_query($link,"SELECT name_rus from classes where id = 1");
					$class1 = mysqli_fetch_array($class1q,MYSQLI_ASSOC)["name_rus"];
					$class2q = mysqli_query($link,"SELECT name_rus from classes where id = 7");
					$class2 = mysqli_fetch_array($class2q,MYSQLI_ASSOC)["name_rus"];
					$class3q = mysqli_query($link,"SELECT name_rus from classes where id = 25");
					$class3 = mysqli_fetch_array($class3q,MYSQLI_ASSOC)["name_rus"];
					break;

				case 5:
					$result = mysqli_query($link,"SELECT rus from text where id = 2");
					$mes = mysqli_fetch_array($result,MYSQLI_ASSOC)["rus"];
					$class1q = mysqli_query($link,"SELECT name_rus from classes where id = 7");
					$class1 = mysqli_fetch_array($class1q,MYSQLI_ASSOC)["name_rus"];
					$class2q = mysqli_query($link,"SELECT name_rus from classes where id = 13");
					$class2 = mysqli_fetch_array($class2q,MYSQLI_ASSOC)["name_rus"];
					$class3q = mysqli_query($link,"SELECT name_rus from classes where id = 19");
					$class3 = mysqli_fetch_array($class3q,MYSQLI_ASSOC)["name_rus"];
					break;

				case 6:
					$result = mysqli_query($link,"SELECT rus from text where id = 2");
					$mes = mysqli_fetch_array($result,MYSQLI_ASSOC)["rus"];
					$class1q = mysqli_query($link,"SELECT name_rus from classes where id = 1");
					$class1 = mysqli_fetch_array($class1q,MYSQLI_ASSOC)["name_rus"];
					$class2q = mysqli_query($link,"SELECT name_rus from classes where id = 13");
					$class2 = mysqli_fetch_array($class2q,MYSQLI_ASSOC)["name_rus"];
					$class3q = mysqli_query($link,"SELECT name_rus from classes where id = 19");
					$class3 = mysqli_fetch_array($class3q,MYSQLI_ASSOC)["name_rus"];
					break;

				case 7:
					$result = mysqli_query($link,"SELECT rus from text where id = 2");
					$mes = mysqli_fetch_array($result,MYSQLI_ASSOC)["rus"];
					$class1q = mysqli_query($link,"SELECT name_rus from classes where id = 1");
					$class1 = mysqli_fetch_array($class1q,MYSQLI_ASSOC)["name_rus"];
					$class2q = mysqli_query($link,"SELECT name_rus from classes where id = 19");
					$class2 = mysqli_fetch_array($class2q,MYSQLI_ASSOC)["name_rus"];
					$class3q = mysqli_query($link,"SELECT name_rus from classes where id = 25");
					$class3 = mysqli_fetch_array($class3q,MYSQLI_ASSOC)["name_rus"];
					break;

				case 8:
					$result = mysqli_query($link,"SELECT rus from text where id = 2");
					$mes = mysqli_fetch_array($result,MYSQLI_ASSOC)["rus"];
					$class1q = mysqli_query($link,"SELECT name_rus from classes where id = 7");
					$class1 = mysqli_fetch_array($class1q,MYSQLI_ASSOC)["name_rus"];
					$class2q = mysqli_query($link,"SELECT name_rus from classes where id = 13");
					$class2 = mysqli_fetch_array($class2q,MYSQLI_ASSOC)["name_rus"];
					$class3q = mysqli_query($link,"SELECT name_rus from classes where id = 25");
					$class3 = mysqli_fetch_array($class3q,MYSQLI_ASSOC)["name_rus"];
					break;
			}
		}
		if($attr["lang"] == 'English')
		{
			switch($raceid)
			{
				case 1:
					$result = mysqli_query($link,"SELECT eng from text where id = 2");
					$mes = mysqli_fetch_array($result,MYSQLI_ASSOC)["eng"];
					$class1q = mysqli_query($link,"SELECT name_eng from classes where id = 1");
					$class1 = mysqli_fetch_array($class1q,MYSQLI_ASSOC)["name_eng"];
					$class2q = mysqli_query($link,"SELECT name_eng from classes where id = 7");
					$class2 = mysqli_fetch_array($class2q,MYSQLI_ASSOC)["name_eng"];
					$class3q = mysqli_query($link,"SELECT name_eng from classes where id = 25");
					$class3 = mysqli_fetch_array($class3q,MYSQLI_ASSOC)["name_eng"];
					break;

				case 2:
					$result = mysqli_query($link,"SELECT eng from text where id = 2");
					$mes = mysqli_fetch_array($result,MYSQLI_ASSOC)["eng"];
					$class1q = mysqli_query($link,"SELECT name_eng from classes where id = 1");
					$class1 = mysqli_fetch_array($class1q,MYSQLI_ASSOC)["name_eng"];
					$class2q = mysqli_query($link,"SELECT name_eng from classes where id = 13");
					$class2 = mysqli_fetch_array($class2q,MYSQLI_ASSOC)["name_eng"];
					$class3q = mysqli_query($link,"SELECT name_eng from classes where id = 19");
					$class3 = mysqli_fetch_array($class3q,MYSQLI_ASSOC)["name_eng"];
					break;

				case 3:
					$result = mysqli_query($link,"SELECT eng from text where id = 2");
					$mes = mysqli_fetch_array($result,MYSQLI_ASSOC)["eng"];
					$class1q = mysqli_query($link,"SELECT name_eng from classes where id = 7");
					$class1 = mysqli_fetch_array($class1q,MYSQLI_ASSOC)["name_eng"];
					$class2q = mysqli_query($link,"SELECT name_eng from classes where id = 19");
					$class2 = mysqli_fetch_array($class2q,MYSQLI_ASSOC)["name_eng"];
					$class3q = mysqli_query($link,"SELECT name_eng from classes where id = 25");
					$class3 = mysqli_fetch_array($class3q,MYSQLI_ASSOC)["name_eng"];
					break;

				case 4:
					$result = mysqli_query($link,"SELECT eng from text where id = 2");
					$mes = mysqli_fetch_array($result,MYSQLI_ASSOC)["eng"];
					$class1q = mysqli_query($link,"SELECT name_eng from classes where id = 1");
					$class1 = mysqli_fetch_array($class1q,MYSQLI_ASSOC)["name_eng"];
					$class2q = mysqli_query($link,"SELECT name_eng from classes where id = 7");
					$class2 = mysqli_fetch_array($class2q,MYSQLI_ASSOC)["name_eng"];
					$class3q = mysqli_query($link,"SELECT name_eng from classes where id = 25");
					$class3 = mysqli_fetch_array($class3q,MYSQLI_ASSOC)["name_eng"];
					break;

				case 5:
					$result = mysqli_query($link,"SELECT eng from text where id = 2");
					$mes = mysqli_fetch_array($result,MYSQLI_ASSOC)["eng"];
					$class1q = mysqli_query($link,"SELECT name_eng from classes where id = 7");
					$class1 = mysqli_fetch_array($class1q,MYSQLI_ASSOC)["name_eng"];
					$class2q = mysqli_query($link,"SELECT name_eng from classes where id = 13");
					$class2 = mysqli_fetch_array($class2q,MYSQLI_ASSOC)["name_eng"];
					$class3q = mysqli_query($link,"SELECT name_eng from classes where id = 19");
					$class3 = mysqli_fetch_array($class3q,MYSQLI_ASSOC)["name_eng"];
					break;

				case 6:
					$result = mysqli_query($link,"SELECT eng from text where id = 2");
					$mes = mysqli_fetch_array($result,MYSQLI_ASSOC)["eng"];
					$class1q = mysqli_query($link,"SELECT name_eng from classes where id = 1");
					$class1 = mysqli_fetch_array($class1q,MYSQLI_ASSOC)["name_eng"];
					$class2q = mysqli_query($link,"SELECT name_eng from classes where id = 13");
					$class2 = mysqli_fetch_array($class2q,MYSQLI_ASSOC)["name_eng"];
					$class3q = mysqli_query($link,"SELECT name_eng from classes where id = 19");
					$class3 = mysqli_fetch_array($class3q,MYSQLI_ASSOC)["name_eng"];
					break;

				case 7:
					$result = mysqli_query($link,"SELECT eng from text where id = 2");
					$mes = mysqli_fetch_array($result,MYSQLI_ASSOC)["eng"];
					$class1q = mysqli_query($link,"SELECT name_eng from classes where id = 1");
					$class1 = mysqli_fetch_array($class1q,MYSQLI_ASSOC)["name_eng"];
					$class2q = mysqli_query($link,"SELECT name_eng from classes where id = 19");
					$class2 = mysqli_fetch_array($class2q,MYSQLI_ASSOC)["name_eng"];
					$class3q = mysqli_query($link,"SELECT name_eng from classes where id = 25");
					$class3 = mysqli_fetch_array($class3q,MYSQLI_ASSOC)["name_eng"];
					break;

				case 8:
					$result = mysqli_query($link,"SELECT eng from text where id = 2");
					$mes = mysqli_fetch_array($result,MYSQLI_ASSOC)["eng"];
					$class1q = mysqli_query($link,"SELECT name_eng from classes where id = 7");
					$class1 = mysqli_fetch_array($class1q,MYSQLI_ASSOC)["name_eng"];
					$class2q = mysqli_query($link,"SELECT name_eng from classes where id = 13");
					$class2 = mysqli_fetch_array($class2q,MYSQLI_ASSOC)["name_eng"];
					$class3q = mysqli_query($link,"SELECT name_eng from classes where id = 25");
					$class3 = mysqli_fetch_array($class3q,MYSQLI_ASSOC)["name_eng"];
					break;
			}
		}
		$keyboard = [[$class1, $class2, $class3]];
		$resp = array("keyboard" => $keyboard,"resize_keyboard" => true, "one_time_keyboard" => false);
		$url = $website."/sendmessage?chat_id=".$chat_id."&parse_mode=".'HTML'."&text=".urlencode($mes)."&reply_markup=".$reply;
		file_get_contents($url);
		return;
	}
}
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if($attr["regStatus"] == 3) // Чел отправил нам свой ник, проверяем, если корректно - добавляем в его профиль, если нет - повторяем регстатус 2. Отправляем в ответку просьбу написать свой ник.
{
	if (preg_match('/^[a-zA-Z0-9_-]+$/', $text) and mb_strlen($text) >= 3 and mb_strlen($text) <= 15) // проверка ника
	{
		$result = mysqli_query($link,"SELECT * from users where nick = '$text'");
		$isTaken = mysqli_num_rows($result);
		if($isTaken != 0)
		{
			if($attr["lang"] == 'Russian')
			{
				$result = mysqli_query($link,"SELECT rus from text where id = 5");
				$mes = mysqli_fetch_array($result,MYSQLI_ASSOC)["rus"];
			}
			if($attr["lang"] == 'English')
			{
				$result = mysqli_query($link,"SELECT eng from text where id = 5");
				$mes = mysqli_fetch_array($result,MYSQLI_ASSOC)["eng"];
			}
			$url = $website."/sendmessage?chat_id=".$chat_id."&text=".urlencode($mes);
			file_get_contents($url);
			return;
		}
		$result = mysqli_query($link,"UPDATE users SET regStatus = 4, nick = '$text' where chat_id = $chat_id");
		$result = mysqli_query($link,"SELECT * from users where chat_id = $chat_id");
		$nick = mysqli_fetch_array($result,MYSQLI_ASSOC)["nick"];
		if ($attr["lang"] == 'Russian')
		{
			$result = mysqli_query($link,"SELECT * from races where name = '$attr[race]'");
			$race = mysqli_fetch_array($result,MYSQLI_ASSOC)["name_rus"];
			$result = mysqli_query($link,"SELECT * from classes where name = '$attr[class]'");
			$class = mysqli_fetch_array($result,MYSQLI_ASSOC)["name_rus"];
			$mes = "Язык: Русский\nРаса: $race\nКласс: $class\nНик: $nick"; //Текст для пользователя
			$keyboard = [["Принять ✔️", "Изменить ❌"]];
		}
		if ($attr["lang"] == 'English')
		{
			$mes = "Language: English\nRace: $attr[race]\nClass: $attr[class]\nNick: $nick";
			$keyboard = [["Apply ✔️", "Change ❌"]];
		}

		$resp = array("keyboard" => $keyboard,"resize_keyboard" => true, "one_time_keyboard" => false);
		$reply = json_encode($resp);

		$url = $website."/sendmessage?chat_id=".$chat_id."&text=".urlencode($mes)."&reply_markup=".$reply;
		file_get_contents($url);

		return;
	}
	else
	{
		if($attr["lang"] == 'Russian')
		{
			$result = mysqli_query($link,"SELECT rus from text where id = 3");
			$mes = mysqli_fetch_array($result,MYSQLI_ASSOC)["rus"];
		}
		if($attr["lang"] == 'English')
		{
			$result = mysqli_query($link,"SELECT eng from text where id = 3");
			$mes = mysqli_fetch_array($result,MYSQLI_ASSOC)["eng"];
		}

		$resp = array("remove_keyboard" => true);
		$reply = json_encode($resp);
		$url = $website."/sendmessage?chat_id=".$chat_id."&parse_mode=".'HTML'."&text=".urlencode($mes)."&reply_markup=".$reply;
		file_get_contents($url);

		return;
	}
}
if($attr["regStatus"] == 4) // Окончание регистрации
{
	if($text == "Apply ✔️" or $text == "Принять ✔️")
	{
		$referal = md5($chat_id);
		if($attr["lang"] == 'Russian')
		{
			$result = mysqli_query($link,"UPDATE users SET regStatus = 5, lang = NULL, subClass = 'Доступен с <b>10</b> уровня', specialization = 'Доступна с <b>20</b> уровня', spell1 = '<i>Не выбрана</i>', spell2 = 'Доступна с <b>10</b> уровня', spell3 = 'Доступна с <b>20</b> уровня', guild = '<i>Отсутствует</i>', referal = '$referal' where chat_id = $chat_id");
			$result = mysqli_query($link,"SELECT * from text where id = 4");
			$mes = mysqli_fetch_array($result,MYSQLI_ASSOC)["rus"];
			$keyboard = [["Местность","В город","Карта"],["Группа","Гильдия","Альянс"],["🥋 Герой","Инвентарь","Помощь"],["Удалить профиль..❌"]];
			$resp = array("keyboard" => $keyboard,"resize_keyboard" => true, "one_time_keyboard" => false);
			$reply = json_encode($resp);
			$url = $website."/sendmessage?chat_id=".$chat_id."&parse_mode=".'HTML'."&text=".urlencode($mes)."&reply_markup=".$reply;
			file_get_contents($url);
			return;
		}
		if($attr["lang"] == 'English')
		{
			$result = mysqli_query($link,"UPDATE users SET regStatus = 5, lang = NULL, subClass = 'Available from level <b>10</b>', specialization = 'Available from level <b>20</b>', spell1 = '<i>Not selected</i>', spell2 = 'Available from level <b>10</b>', spell3 = 'Available from level <b>20</b>', guild = '<i>Not in the guild</i>', referal = '$referal' where chat_id = $chat_id");
			$result = mysqli_query($link,"SELECT * from text where id = 4");
			$mes = mysqli_fetch_array($result,MYSQLI_ASSOC)["eng"];
			$keyboard = [["Terrain","In Town","Map"],["Group","Guild","Alliance"],["Hero","Inventory","Help"],["Delete profile..❌"]];
			$resp = array("keyboard" => $keyboard,"resize_keyboard" => true, "one_time_keyboard" => false);
			$reply = json_encode($resp);
			$url = $website."/sendmessage?chat_id=".$chat_id."&parse_mode=".'HTML'."&text=".urlencode($mes)."&reply_markup=".$reply;
			file_get_contents($url);
			return;
		}
	}
	if($text == "Change ❌" or $text == "Изменить ❌")
	{
		$result = mysqli_query($link,"UPDATE users SET heroStatus = NULL, regStatus = 0, lang = NULL, nick = NULL, energy = NULL, maxEnergy = NULL, level = NULL, subClass = NULL, specialization = NULL, spell1 = NULL, spell2 = NULL, spell3 = NULL, freePoints = NULL, guild = NULL, xp = NULL, gold = NULL, sGold = NULL, referal = NULL where chat_id = $chat_id");

		$mes = "Choose your language:";

		$keyboard = [["Русский","English"]];

		$resp = array("keyboard" => $keyboard,"resize_keyboard" => true, "one_time_keyboard" => false,"remove_keyboard" => true);
		$reply = json_encode($resp);

		$url = $website."/sendmessage?chat_id=".$chat_id."&text=".urlencode($mes)."&reply_markup=".$reply;
		file_get_contents($url);

		return;
	}
	else
	{
		if($attr["lang"] == 'Russian')
		{
			$result = mysqli_query($link,"SELECT * from races where name = '$attr[race]'");
			$race = mysqli_fetch_array($result,MYSQLI_ASSOC)["name_rus"];
			$result = mysqli_query($link,"SELECT * from classes where name = '$attr[class]'");
			$class = mysqli_fetch_array($result,MYSQLI_ASSOC)["name_rus"];
			$mes = "Язык: Русский\nРаса: $race\nКласс: $class\nНик: $attr[nick]"; //Текст для пользователя
			$keyboard = [["Принять ✔️", "Изменить ❌"]];
		}
		if($attr["lang"] == 'English')
		{
			$mes = "Language: English\nRace: $attr[race]\nClass: $attr[class]\nNick: $attr[nick]";
			$keyboard = [["Apply ✔️", "Change ❌"]];
		}

		$resp = array("keyboard" => $keyboard,"resize_keyboard" => true, "one_time_keyboard" => false);
		$reply = json_encode($resp);

		$url = $website."/sendmessage?chat_id=".$chat_id."&text=".urlencode($mes)."&reply_markup=".$reply;
		file_get_contents($url);

		return;
	}
}
////////////////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////ИГРА НОВИЧКА//////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////
if($attr["regStatus"] == 5)
{
	//////////////////////////////////////////////////////////////////////////////////////
	////////////////////////////////////////ДЛЯ РУССКИХ//////////////////////////////////
	//////////////////////////////////////////////////////////////////////////////////////
	if($attr["lang"] == 'Russian')
	{
		switch($text)
		{	/////ГЕРОЙ
			case "⬅️ Герой":
			case "🥋 Герой":
				$mes = "<b>Меню героя</b>";
				$keyboard = [["👤 Информация","🎒 Инвентарь"],["🏅 Класс","📖Атрибуты","🎓Способности"],["🎽Экипировка","⚒Ремесло","🏠Гл. меню"]];
				$resp = array("keyboard" => $keyboard,"resize_keyboard" => true, "one_time_keyboard" => false);
				$reply = json_encode($resp);
				$url = $website."/sendmessage?chat_id=".$chat_id."&parse_mode=".'HTML'."&text=".urlencode($mes)."&reply_markup=".$reply;
				file_get_contents($url);
				return;
			/////ИНФОРМАЦИЯ
			case "👤 Информация":
				$nick = $attr[nick];
				$energy = $attr[energy];
				$maxEnergy = $attr[maxEnergy];
				$level = $attr[level];
				$result = mysqli_query($link,"SELECT * from races where name = '$attr[race]'");
				$race = mysqli_fetch_array($result,MYSQLI_ASSOC)["name_rus"];
				$result = mysqli_query($link,"SELECT * from classes where name = '$attr[class]'");
				$class = mysqli_fetch_array($result,MYSQLI_ASSOC)["name_rus"];
				///
				$subClass = $attr[subClass];
				$result = mysqli_query($link,"SELECT * from classes where name = '$attr[subClass]'");
				$isTaken = mysqli_num_rows($result);
				if($isTaken > 0)
				{
					$subClass = mysqli_fetch_array($result,MYSQLI_ASSOC)["name_rus"];
				}
				///
				$specialization = $attr[specialization];
				$result = mysqli_query($link,"SELECT * from classes where name = '$attr[specialization]'");
				$isTaken = mysqli_num_rows($result);
				if($isTaken > 0)
				{
					$specialization = mysqli_fetch_array($result,MYSQLI_ASSOC)["name_rus"];
				}
				///
				$nowClass = $class;
				$result = mysqli_query($link,"SELECT * from classes where name = '$attr[subClass]'");
				$haveSubclass = mysqli_num_rows($result);
				if($haveSubclass > 0)
				{
					$nowClass = mysqli_fetch_array($result,MYSQLI_ASSOC)["name_rus"];
					$result = mysqli_query($link,"SELECT * from classes where name = '$attr[specialization]'");
					$haveSpecialization = mysqli_num_rows($result);
					if($haveSpecialization > 0)
					{
						$nowClass = mysqli_fetch_array($result,MYSQLI_ASSOC)["name_rus"];
					}
				}
				///
				$guild = $attr[guild];
				$xp = $attr[xp];
				$nextLevel = $level+1;
				$result = mysqli_query($link,"SELECT * from levels where level = $nextLevel");
				$needXp = mysqli_fetch_array($result,MYSQLI_ASSOC)["xp"];
				$gold = $attr[gold];
				$sGold = $attr[sGold];
				$location = $attr[location];
				$armor = $attr[armor];
				$hp = $attr[hp];
				$strength = $attr[strength];
				$intellect = $attr[intellect];
				$agility = $attr[agility];
				$spirit = $attr[spirit];
				$freePoints = $attr[freePoints];
				$attack = $attr[attack];
				//////////
				$mes = "👤 <b>$nick</b>, $race - $nowClass, $level уровня\n\n⚡️ Энергия: $energy \ $maxEnergy\n✨ Опыт: $xp \ $needXp\n\n❤️ Здоровье: $hp\n⚔️ Атака: $attack\n🛡 Броня: $armor\n\n💪 Сила: $strength\n📚 Интеллект: $intellect\n🤺 Ловкость: $agility\n🔮 Дух: $spirit\n📖 Не распределено: $freePoints\n\n👨‍👩‍👧‍👦 Гильдия: $guild\n\n💰 Золото: $gold\n💧 Слеза Анк'хара: $sGold\n\n🏝 Локация: $location\n\nПригласить друга: /referal";
				$keyboard = [["👤 Информация","🎒 Инвентарь"],["🏅 Класс","📖Атрибуты","🎓Способности"],["🎽Экипировка","⚒Ремесло","🏠Гл. меню"]];
				$resp = array("keyboard" => $keyboard,"resize_keyboard" => true, "one_time_keyboard" => false);
				$reply = json_encode($resp);
				$url = $website."/sendmessage?chat_id=".$chat_id."&parse_mode=".'HTML'."&text=".urlencode($mes)."&reply_markup=".$reply;
				file_get_contents($url);
				return;
			/////ИНВЕНТАРЬ
			case "🎒 Инвентарь":
				$result = mysqli_query($link,"SELECT * FROM inventory LEFT JOIN items ON inventory.item_id=items.id where inventory.user_chat_id = $attr[chat_id] AND items.type = 'resource'");
				$resourceCount = 0;
				while ($row = mysqli_fetch_assoc($result)) {
					$resourceCount = $resourceCount + $row["count"];
				}
				$result = mysqli_query($link,"SELECT * FROM inventory LEFT JOIN items ON inventory.item_id=items.id where inventory.user_chat_id = $attr[chat_id] AND items.type = 'consumable'");
				$consumableCount = 0;
				while ($row = mysqli_fetch_assoc($result)) {
					$consumableCount = $consumableCount + $row["count"];
				}
				$result = mysqli_query($link,"SELECT * FROM inventory LEFT JOIN items ON inventory.item_id=items.id where inventory.user_chat_id = $attr[chat_id] AND items.type = 'armor'");
				$armorCount = 0;
				while ($row = mysqli_fetch_assoc($result)) {
					$armorCount = $armorCount + $row["count"];
				}
				$result = mysqli_query($link,"SELECT * FROM inventory LEFT JOIN items ON inventory.item_id=items.id where inventory.user_chat_id = $attr[chat_id] AND items.type = 'weapon'");
				$weaponCount = 0;
				while ($row = mysqli_fetch_assoc($result)) {
					$weaponCount = $weaponCount + $row["count"];
				}
				$itemsCount = $resourceCount + $consumableCount + $armorCount + $weaponCount;
				$mes = "<b>Содержимое инвентаря</b>\n\nРесурсы: $resourceCount шт.\nРасходуемые: $consumableCount шт.\nПредметы брони: $armorCount шт.\nПредметы оружия: $weaponCount шт.\n\nИтого предметов: <b>$itemsCount</b>\nМакс. предметов: <b>$attr[bagMax]</b>\n\nКнопка <b>На склад</b> - позволяет перенести все предметы из инвентаря на склад того замка, в котором Вы находитесь.";
				$keyboard = [["🎒 Ресурсы","🎒 Расходуемое"],["🎒 Броня","🎒 Оружие"],["📤 На склад","⬅️ Герой","🏠Гл. меню"]];
				$resp = array("keyboard" => $keyboard,"resize_keyboard" => true, "one_time_keyboard" => false);
				$reply = json_encode($resp);
				$url = $website."/sendmessage?chat_id=".$chat_id."&parse_mode=".'HTML'."&text=".urlencode($mes)."&reply_markup=".$reply;
				file_get_contents($url);


				return;
			/////ИНВЕНТАРЬ-РЕСУРСЫ
			case "🎒 Ресурсы":
				$result = mysqli_query($link,"SELECT * FROM inventory LEFT JOIN items ON inventory.item_id=items.id where inventory.user_chat_id = $attr[chat_id] AND items.type = 'resource'");
				$message = "";
				while ($row = mysqli_fetch_assoc($result)) {
					$message = $message . $row["count"] . " x " . $row["name_rus"] . "\n";
				}
				$mes = "<b>Ресурсы в инвентаре:</b>\n\n$message";
				$keyboard = [["🎒 Ресурсы","🎒 Расходуемое"],["🎒 Броня","🎒 Оружие"],["📤 На склад","⬅️ Герой","🏠Гл. меню"]];
				$resp = array("keyboard" => $keyboard,"resize_keyboard" => true, "one_time_keyboard" => false);
				$reply = json_encode($resp);
				$url = $website."/sendmessage?chat_id=".$chat_id."&parse_mode=".'HTML'."&text=".urlencode($mes)."&reply_markup=".$reply;
				file_get_contents($url);
				return;
			/////ИНВЕНТАРЬ-РАСХОДУЕМОЕ
			case "🎒 Расходуемое":
				$result = mysqli_query($link,"SELECT * FROM inventory LEFT JOIN items ON inventory.item_id=items.id where inventory.user_chat_id = $attr[chat_id] AND items.type = 'consumable'");
				$message = "";
				while ($row = mysqli_fetch_assoc($result)) {
					$message = $message . $row["count"] . " x " . $row["name_rus"] . "\n";
				}
				$mes = "<b>Расходуемое в инвентаре:</b>\n\n$message";
				$keyboard = [["🎒 Ресурсы","🎒 Расходуемое"],["🎒 Броня","🎒 Оружие"],["📤 На склад","⬅️ Герой","🏠Гл. меню"]];
				$resp = array("keyboard" => $keyboard,"resize_keyboard" => true, "one_time_keyboard" => false);
				$reply = json_encode($resp);
				$url = $website."/sendmessage?chat_id=".$chat_id."&parse_mode=".'HTML'."&text=".urlencode($mes)."&reply_markup=".$reply;
				file_get_contents($url);
				return;
			/////ИНВЕНТАРЬ-БРОНЯ
			case "🎒 Броня":
				$result = mysqli_query($link,"SELECT * FROM inventory LEFT JOIN items ON inventory.item_id=items.id where inventory.user_chat_id = $attr[chat_id] AND items.type = 'armor'");
				$message = "";
				while ($row = mysqli_fetch_assoc($result)) {
					$message = $message . $row["count"] . " x " . $row["name_rus"] . "\n";
				}
				$mes = "<b>Броня в инвентаре:</b>\n\n$message";
				$keyboard = [["🎒 Ресурсы","🎒 Расходуемое"],["🎒 Броня","🎒 Оружие"],["📤 На склад","⬅️ Герой","🏠Гл. меню"]];
				$resp = array("keyboard" => $keyboard,"resize_keyboard" => true, "one_time_keyboard" => false);
				$reply = json_encode($resp);
				$url = $website."/sendmessage?chat_id=".$chat_id."&parse_mode=".'HTML'."&text=".urlencode($mes)."&reply_markup=".$reply;
				file_get_contents($url);
				return;
			/////ИНВЕНТАРЬ-ОРУЖИЕ
			case "🎒 Оружие":
				$result = mysqli_query($link,"SELECT * FROM inventory LEFT JOIN items ON inventory.item_id=items.id where inventory.user_chat_id = $attr[chat_id] AND items.type = 'weapon'");
				$message = "";
				while ($row = mysqli_fetch_assoc($result)) {
					$message = $message . $row["count"] . " x " . $row["name_rus"] . "\n";
				}
				$mes = "<b>Оружие в инвентаре:</b>\n\n$message";
				$keyboard = [["🎒 Ресурсы","🎒 Расходуемое"],["🎒 Броня","🎒 Оружие"],["📤 На склад","⬅️ Герой","🏠Гл. меню"]];
				$resp = array("keyboard" => $keyboard,"resize_keyboard" => true, "one_time_keyboard" => false);
				$reply = json_encode($resp);
				$url = $website."/sendmessage?chat_id=".$chat_id."&parse_mode=".'HTML'."&text=".urlencode($mes)."&reply_markup=".$reply;
				file_get_contents($url);
				return;
			/////КЛАСС
			case "⬅️ Класс":
			case "🏅 Класс":
				$result = mysqli_query($link,"SELECT * from classes where name = '$attr[class]'");
				$class = mysqli_fetch_array($result,MYSQLI_ASSOC)["name_rus"];
				$subClass = $attr[subClass];
				$result = mysqli_query($link,"SELECT * from classes where name = '$attr[subClass]'");
				$isTaken = mysqli_num_rows($result);
				if($isTaken > 0)
				{
					$subClass = mysqli_fetch_array($result,MYSQLI_ASSOC)["name_rus"];
				}
				///
				$specialization = $attr[specialization];
				$result = mysqli_query($link,"SELECT * from classes where name = '$attr[specialization]'");
				$isTaken = mysqli_num_rows($result);
				if($isTaken > 0)
				{
					$specialization = mysqli_fetch_array($result,MYSQLI_ASSOC)["name_rus"];
				}
				$mes = "Информация о Вашем <b>классе</b>, <b>подклассе</b>, и <b>специализации</b>:\n\n🥇 Класс: $class\n🥈 Подкласс: $subClass\n🥉 Специализация: $specialization\n\nПодробнее о <b>классах</b>, их <b>подклассах</b> и <b>специализациях</b> - Вы можете узнать командой: /cl_info";
				$keyboard = [["👤 Информация","🎒 Инвентарь"],["🏅 Класс","📖Атрибуты","🎓Способности"],["🎽Экипировка","⚒Ремесло","🏠Гл. меню"]];
				$resp = array("keyboard" => $keyboard,"resize_keyboard" => true, "one_time_keyboard" => false);
				$reply = json_encode($resp);
				$url = $website."/sendmessage?chat_id=".$chat_id."&parse_mode=".'HTML'."&text=".urlencode($mes)."&reply_markup=".$reply;
				file_get_contents($url);
				return;
			/////CHOOSE SUBCLASS
			case "/sc_choose":
				$result = mysqli_query($link,"SELECT * from classes where name = '$attr[subClass]'");
				$isTaken = mysqli_num_rows($result);
				if($isTaken == 0 and $attr[level] >= 10)
				{
					if($attr['class'] == "Warrior" or $attr['class'] == "Wizard")
					{
						if($attr['class'] == "Warrior")
						{
							$mes = "Выберите Ваш <b>подкласс</b>:\n\n<b>Внимание!</b>\nПодкласс <b>нельзя</b> будет изменить!\n\nЕсли Вы не уверены в своём выборе, или Вам необходима доп. информация по подклассам - воспользуйтесь командой: /sc_info";
							$keyboard = [["Паладин","Ведьмак"],["⬅️ Класс"]];
							$resp = array("keyboard" => $keyboard,"resize_keyboard" => true, "one_time_keyboard" => false);
							$reply = json_encode($resp);
							$url = $website."/sendmessage?chat_id=".$chat_id."&parse_mode=".'HTML'."&text=".urlencode($mes)."&reply_markup=".$reply;
							file_get_contents($url);
							return;
						}
						$mes = "Выберите Ваш <b>подкласс</b>:\n\n<b>Внимание!</b>\nПодкласс <b>нельзя</b> будет изменить!\n\nЕсли Вы не уверены в своём выборе, или Вам необходима доп. информация по подклассам - воспользуйтесь командой: /sc_info";
						$keyboard = [["Чародей","Заклинатель"],["⬅️ Класс"]];
						$resp = array("keyboard" => $keyboard,"resize_keyboard" => true, "one_time_keyboard" => false);
						$reply = json_encode($resp);
						$url = $website."/sendmessage?chat_id=".$chat_id."&parse_mode=".'HTML'."&text=".urlencode($mes)."&reply_markup=".$reply;
						file_get_contents($url);
						return;
						return;
					}
					if($attr['class'] == "Pathfinder" or $attr['class'] == "Sorcerer")
					{
						if($attr['class'] == "Pathfinder")
						{
							$mes = "Выберите Ваш <b>подкласс</b>:\n\n<b>Внимание!</b>\nПодкласс <b>нельзя</b> будет изменить!\n\nЕсли Вы не уверены в своём выборе, или Вам необходима доп. информация по подклассам - воспользуйтесь командой: /sc_info";
							$keyboard = [["Охотник","Рейнджер"],["⬅️ Класс"]];
							$resp = array("keyboard" => $keyboard,"resize_keyboard" => true, "one_time_keyboard" => false);
							$reply = json_encode($resp);
							$url = $website."/sendmessage?chat_id=".$chat_id."&parse_mode=".'HTML'."&text=".urlencode($mes)."&reply_markup=".$reply;
							file_get_contents($url);
							return;
						}
						$mes = "Выберите Ваш <b>подкласс</b>:\n\n<b>Внимание!</b>\nПодкласс <b>нельзя</b> будет изменить!\n\nЕсли Вы не уверены в своём выборе, или Вам необходима доп. информация по подклассам - воспользуйтесь командой: /sc_info";
						$keyboard = [["Некромант","Чернокнижник"],["⬅️ Класс"]];
						$resp = array("keyboard" => $keyboard,"resize_keyboard" => true, "one_time_keyboard" => false);
						$reply = json_encode($resp);
						$url = $website."/sendmessage?chat_id=".$chat_id."&parse_mode=".'HTML'."&text=".urlencode($mes)."&reply_markup=".$reply;
						file_get_contents($url);
						return;
					}
					$mes = "Выберите Ваш <b>подкласс</b>:\n\n<b>Внимание!</b>\nПодкласс <b>нельзя</b> будет изменить!\n\nЕсли Вы не уверены в своём выборе, или Вам необходима доп. информация по подклассам - воспользуйтесь командой: /sc_info";
					$keyboard = [["Оракул","Шаман"],["⬅️ Класс"]];
					$resp = array("keyboard" => $keyboard,"resize_keyboard" => true, "one_time_keyboard" => false);
					$reply = json_encode($resp);
					$url = $website."/sendmessage?chat_id=".$chat_id."&parse_mode=".'HTML'."&text=".urlencode($mes)."&reply_markup=".$reply;
					file_get_contents($url);
					return;
				}
				$mes = "Подкласс уже был выбран или не пренадлежит Вашему классу, либо Вы ещё не достигли 10-ого уровня.";
				$keyboard = [["👤 Информация","🎒 Инвентарь"],["🏅 Класс","📖Атрибуты","🎓Способности"],["🎽Экипировка","⚒Ремесло","🏠Гл. меню"]];
				$resp = array("keyboard" => $keyboard,"resize_keyboard" => true, "one_time_keyboard" => false);
				$reply = json_encode($resp);
				$url = $website."/sendmessage?chat_id=".$chat_id."&parse_mode=".'HTML'."&text=".urlencode($mes)."&reply_markup=".$reply;
				file_get_contents($url);
				return;
			/////SUBCLASS CHOOSE
			case "Паладин":
				$result = mysqli_query($link,"SELECT * from classes where name = '$attr[subClass]'");
				$isTaken = mysqli_num_rows($result);
				if($isTaken == 0 and $attr[level] >= 10 and $attr['class'] == "Warrior")
				{
					$result = mysqli_query($link,"UPDATE users SET subClass = 'Paladin' where chat_id = $chat_id");
					$mes = "Вы выбрали подкласс: <b>Паладин</b>.";
					$keyboard = [["👤 Информация","🎒 Инвентарь"],["🏅 Класс","📖Атрибуты","🎓Способности"],["🎽Экипировка","⚒Ремесло","🏠Гл. меню"]];
					$resp = array("keyboard" => $keyboard,"resize_keyboard" => true, "one_time_keyboard" => false);
					$reply = json_encode($resp);
					$url = $website."/sendmessage?chat_id=".$chat_id."&parse_mode=".'HTML'."&text=".urlencode($mes)."&reply_markup=".$reply;
					file_get_contents($url);
					return;
				}
				$mes = "Подкласс уже был выбран или не пренадлежит Вашему классу, либо Вы ещё не достигли 10-ого уровня.";
				$keyboard = [["👤 Информация","🎒 Инвентарь"],["🏅 Класс","📖Атрибуты","🎓Способности"],["🎽Экипировка","⚒Ремесло","🏠Гл. меню"]];
				$resp = array("keyboard" => $keyboard,"resize_keyboard" => true, "one_time_keyboard" => false);
				$reply = json_encode($resp);
				$url = $website."/sendmessage?chat_id=".$chat_id."&parse_mode=".'HTML'."&text=".urlencode($mes)."&reply_markup=".$reply;
				file_get_contents($url);
				return;
			case "Ведьмак":
				$result = mysqli_query($link,"SELECT * from classes where name = '$attr[subClass]'");
				$isTaken = mysqli_num_rows($result);
				if($isTaken == 0 and $attr[level] >= 10 and $attr['class'] == "Warrior")
				{
					$result = mysqli_query($link,"UPDATE users SET subClass = 'Witcher' where chat_id = $chat_id");
					$mes = "Вы выбрали подкласс: <b>Ведьмак</b>.";
					$keyboard = [["👤 Информация","🎒 Инвентарь"],["🏅 Класс","📖Атрибуты","🎓Способности"],["🎽Экипировка","⚒Ремесло","🏠Гл. меню"]];
					$resp = array("keyboard" => $keyboard,"resize_keyboard" => true, "one_time_keyboard" => false);
					$reply = json_encode($resp);
					$url = $website."/sendmessage?chat_id=".$chat_id."&parse_mode=".'HTML'."&text=".urlencode($mes)."&reply_markup=".$reply;
					file_get_contents($url);
					return;
				}
				$mes = "Подкласс уже был выбран или не пренадлежит Вашему классу, либо Вы ещё не достигли 10-ого уровня.";
				$keyboard = [["👤 Информация","🎒 Инвентарь"],["🏅 Класс","📖Атрибуты","🎓Способности"],["🎽Экипировка","⚒Ремесло","🏠Гл. меню"]];
				$resp = array("keyboard" => $keyboard,"resize_keyboard" => true, "one_time_keyboard" => false);
				$reply = json_encode($resp);
				$url = $website."/sendmessage?chat_id=".$chat_id."&parse_mode=".'HTML'."&text=".urlencode($mes)."&reply_markup=".$reply;
				file_get_contents($url);
				return;
			case "Чародей":
				$result = mysqli_query($link,"SELECT * from classes where name = '$attr[subClass]'");
				$isTaken = mysqli_num_rows($result);
				if($isTaken == 0 and $attr[level] >= 10 and $attr['class'] == "Wizard")
				{
					$result = mysqli_query($link,"UPDATE users SET subClass = 'Magician' where chat_id = $chat_id");
					$mes = "Вы выбрали подкласс: <b>Чародей</b>.";
					$keyboard = [["👤 Информация","🎒 Инвентарь"],["🏅 Класс","📖Атрибуты","🎓Способности"],["🎽Экипировка","⚒Ремесло","🏠Гл. меню"]];
					$resp = array("keyboard" => $keyboard,"resize_keyboard" => true, "one_time_keyboard" => false);
					$reply = json_encode($resp);
					$url = $website."/sendmessage?chat_id=".$chat_id."&parse_mode=".'HTML'."&text=".urlencode($mes)."&reply_markup=".$reply;
					file_get_contents($url);
					return;
				}
				$mes = "Подкласс уже был выбран или не пренадлежит Вашему классу, либо Вы ещё не достигли 10-ого уровня.";
				$keyboard = [["👤 Информация","🎒 Инвентарь"],["🏅 Класс","📖Атрибуты","🎓Способности"],["🎽Экипировка","⚒Ремесло","🏠Гл. меню"]];
				$resp = array("keyboard" => $keyboard,"resize_keyboard" => true, "one_time_keyboard" => false);
				$reply = json_encode($resp);
				$url = $website."/sendmessage?chat_id=".$chat_id."&parse_mode=".'HTML'."&text=".urlencode($mes)."&reply_markup=".$reply;
				file_get_contents($url);
				return;
			case "Заклинатель":
				$result = mysqli_query($link,"SELECT * from classes where name = '$attr[subClass]'");
				$isTaken = mysqli_num_rows($result);
				if($isTaken == 0 and $attr[level] >= 10 and $attr['class'] == "Wizard")
				{
					$result = mysqli_query($link,"UPDATE users SET subClass = 'Exorcist' where chat_id = $chat_id");
					$mes = "Вы выбрали подкласс: <b>Заклинатель</b>.";
					$keyboard = [["👤 Информация","🎒 Инвентарь"],["🏅 Класс","📖Атрибуты","🎓Способности"],["🎽Экипировка","⚒Ремесло","🏠Гл. меню"]];
					$resp = array("keyboard" => $keyboard,"resize_keyboard" => true, "one_time_keyboard" => false);
					$reply = json_encode($resp);
					$url = $website."/sendmessage?chat_id=".$chat_id."&parse_mode=".'HTML'."&text=".urlencode($mes)."&reply_markup=".$reply;
					file_get_contents($url);
					return;
				}
				$mes = "Подкласс уже был выбран или не пренадлежит Вашему классу, либо Вы ещё не достигли 10-ого уровня.";
				$keyboard = [["👤 Информация","🎒 Инвентарь"],["🏅 Класс","📖Атрибуты","🎓Способности"],["🎽Экипировка","⚒Ремесло","🏠Гл. меню"]];
				$resp = array("keyboard" => $keyboard,"resize_keyboard" => true, "one_time_keyboard" => false);
				$reply = json_encode($resp);
				$url = $website."/sendmessage?chat_id=".$chat_id."&parse_mode=".'HTML'."&text=".urlencode($mes)."&reply_markup=".$reply;
				file_get_contents($url);
				return;
			case "Охотник":
				$result = mysqli_query($link,"SELECT * from classes where name = '$attr[subClass]'");
				$isTaken = mysqli_num_rows($result);
				if($isTaken == 0 and $attr[level] >= 10 and $attr['class'] == "Pathfinder")
				{
					$result = mysqli_query($link,"UPDATE users SET subClass = 'Hunter' where chat_id = $chat_id");
					$mes = "Вы выбрали подкласс: <b>Охотник</b>.";
					$keyboard = [["👤 Информация","🎒 Инвентарь"],["🏅 Класс","📖Атрибуты","🎓Способности"],["🎽Экипировка","⚒Ремесло","🏠Гл. меню"]];
					$resp = array("keyboard" => $keyboard,"resize_keyboard" => true, "one_time_keyboard" => false);
					$reply = json_encode($resp);
					$url = $website."/sendmessage?chat_id=".$chat_id."&parse_mode=".'HTML'."&text=".urlencode($mes)."&reply_markup=".$reply;
					file_get_contents($url);
					return;
				}
				$mes = "Подкласс уже был выбран или не пренадлежит Вашему классу, либо Вы ещё не достигли 10-ого уровня.";
				$keyboard = [["👤 Информация","🎒 Инвентарь"],["🏅 Класс","📖Атрибуты","🎓Способности"],["🎽Экипировка","⚒Ремесло","🏠Гл. меню"]];
				$resp = array("keyboard" => $keyboard,"resize_keyboard" => true, "one_time_keyboard" => false);
				$reply = json_encode($resp);
				$url = $website."/sendmessage?chat_id=".$chat_id."&parse_mode=".'HTML'."&text=".urlencode($mes)."&reply_markup=".$reply;
				file_get_contents($url);
				return;
			case "Рейнджер":
				$result = mysqli_query($link,"SELECT * from classes where name = '$attr[subClass]'");
				$isTaken = mysqli_num_rows($result);
				if($isTaken == 0 and $attr[level] >= 10 and $attr['class'] == "Pathfinder")
				{
					$result = mysqli_query($link,"UPDATE users SET subClass = 'Ranger' where chat_id = $chat_id");
					$mes = "Вы выбрали подкласс: <b>Рейнджер</b>.";
					$keyboard = [["👤 Информация","🎒 Инвентарь"],["🏅 Класс","📖Атрибуты","🎓Способности"],["🎽Экипировка","⚒Ремесло","🏠Гл. меню"]];
					$resp = array("keyboard" => $keyboard,"resize_keyboard" => true, "one_time_keyboard" => false);
					$reply = json_encode($resp);
					$url = $website."/sendmessage?chat_id=".$chat_id."&parse_mode=".'HTML'."&text=".urlencode($mes)."&reply_markup=".$reply;
					file_get_contents($url);
					return;
				}
				$mes = "Подкласс уже был выбран или не пренадлежит Вашему классу, либо Вы ещё не достигли 10-ого уровня.";
				$keyboard = [["👤 Информация","🎒 Инвентарь"],["🏅 Класс","📖Атрибуты","🎓Способности"],["🎽Экипировка","⚒Ремесло","🏠Гл. меню"]];
				$resp = array("keyboard" => $keyboard,"resize_keyboard" => true, "one_time_keyboard" => false);
				$reply = json_encode($resp);
				$url = $website."/sendmessage?chat_id=".$chat_id."&parse_mode=".'HTML'."&text=".urlencode($mes)."&reply_markup=".$reply;
				file_get_contents($url);
				return;
			case "Некромант":
				$result = mysqli_query($link,"SELECT * from classes where name = '$attr[subClass]'");
				$isTaken = mysqli_num_rows($result);
				if($isTaken == 0 and $attr[level] >= 10 and $attr['class'] == "Sorcerer")
				{
					$result = mysqli_query($link,"UPDATE users SET subClass = 'Necromancer' where chat_id = $chat_id");
					$mes = "Вы выбрали подкласс: <b>Некромант</b>.";
					$keyboard = [["👤 Информация","🎒 Инвентарь"],["🏅 Класс","📖Атрибуты","🎓Способности"],["🎽Экипировка","⚒Ремесло","🏠Гл. меню"]];
					$resp = array("keyboard" => $keyboard,"resize_keyboard" => true, "one_time_keyboard" => false);
					$reply = json_encode($resp);
					$url = $website."/sendmessage?chat_id=".$chat_id."&parse_mode=".'HTML'."&text=".urlencode($mes)."&reply_markup=".$reply;
					file_get_contents($url);
					return;
				}
				$mes = "Подкласс уже был выбран или не пренадлежит Вашему классу, либо Вы ещё не достигли 10-ого уровня.";
				$keyboard = [["👤 Информация","🎒 Инвентарь"],["🏅 Класс","📖Атрибуты","🎓Способности"],["🎽Экипировка","⚒Ремесло","🏠Гл. меню"]];
				$resp = array("keyboard" => $keyboard,"resize_keyboard" => true, "one_time_keyboard" => false);
				$reply = json_encode($resp);
				$url = $website."/sendmessage?chat_id=".$chat_id."&parse_mode=".'HTML'."&text=".urlencode($mes)."&reply_markup=".$reply;
				file_get_contents($url);
				return;
			case "Чернокнижник":
				$result = mysqli_query($link,"SELECT * from classes where name = '$attr[subClass]'");
				$isTaken = mysqli_num_rows($result);
				if($isTaken == 0 and $attr[level] >= 10 and $attr['class'] == "Sorcerer")
				{
					$result = mysqli_query($link,"UPDATE users SET subClass = 'Warlock' where chat_id = $chat_id");
					$mes = "Вы выбрали подкласс: <b>Чернокнижник</b>.";
					$keyboard = [["👤 Информация","🎒 Инвентарь"],["🏅 Класс","📖Атрибуты","🎓Способности"],["🎽Экипировка","⚒Ремесло","🏠Гл. меню"]];
					$resp = array("keyboard" => $keyboard,"resize_keyboard" => true, "one_time_keyboard" => false);
					$reply = json_encode($resp);
					$url = $website."/sendmessage?chat_id=".$chat_id."&parse_mode=".'HTML'."&text=".urlencode($mes)."&reply_markup=".$reply;
					file_get_contents($url);
					return;
				}
				$mes = "Подкласс уже был выбран или не пренадлежит Вашему классу, либо Вы ещё не достигли 10-ого уровня.";
				$keyboard = [["👤 Информация","🎒 Инвентарь"],["🏅 Класс","📖Атрибуты","🎓Способности"],["🎽Экипировка","⚒Ремесло","🏠Гл. меню"]];
				$resp = array("keyboard" => $keyboard,"resize_keyboard" => true, "one_time_keyboard" => false);
				$reply = json_encode($resp);
				$url = $website."/sendmessage?chat_id=".$chat_id."&parse_mode=".'HTML'."&text=".urlencode($mes)."&reply_markup=".$reply;
				file_get_contents($url);
				return;
			case "Оракул":
				$result = mysqli_query($link,"SELECT * from classes where name = '$attr[subClass]'");
				$isTaken = mysqli_num_rows($result);
				if($isTaken == 0 and $attr[level] >= 10 and $attr['class'] == "Cleric")
				{
					$result = mysqli_query($link,"UPDATE users SET subClass = 'Oracle' where chat_id = $chat_id");
					$mes = "Вы выбрали подкласс: <b>Оракул</b>.";
					$keyboard = [["👤 Информация","🎒 Инвентарь"],["🏅 Класс","📖Атрибуты","🎓Способности"],["🎽Экипировка","⚒Ремесло","🏠Гл. меню"]];
					$resp = array("keyboard" => $keyboard,"resize_keyboard" => true, "one_time_keyboard" => false);
					$reply = json_encode($resp);
					$url = $website."/sendmessage?chat_id=".$chat_id."&parse_mode=".'HTML'."&text=".urlencode($mes)."&reply_markup=".$reply;
					file_get_contents($url);
					return;
				}
				$mes = "Подкласс уже был выбран или не пренадлежит Вашему классу, либо Вы ещё не достигли 10-ого уровня.";
				$keyboard = [["👤 Информация","🎒 Инвентарь"],["🏅 Класс","📖Атрибуты","🎓Способности"],["🎽Экипировка","⚒Ремесло","🏠Гл. меню"]];
				$resp = array("keyboard" => $keyboard,"resize_keyboard" => true, "one_time_keyboard" => false);
				$reply = json_encode($resp);
				$url = $website."/sendmessage?chat_id=".$chat_id."&parse_mode=".'HTML'."&text=".urlencode($mes)."&reply_markup=".$reply;
				file_get_contents($url);
				return;
			case "Шаман":
				$result = mysqli_query($link,"SELECT * from classes where name = '$attr[subClass]'");
				$isTaken = mysqli_num_rows($result);
				if($isTaken == 0 and $attr[level] >= 10 and $attr['class'] == "Cleric")
				{
					$result = mysqli_query($link,"UPDATE users SET subClass = 'Shaman' where chat_id = $chat_id");
					$mes = "Вы выбрали подкласс: <b>Шаман</b>.";
					$keyboard = [["👤 Информация","🎒 Инвентарь"],["🏅 Класс","📖Атрибуты","🎓Способности"],["🎽Экипировка","⚒Ремесло","🏠Гл. меню"]];
					$resp = array("keyboard" => $keyboard,"resize_keyboard" => true, "one_time_keyboard" => false);
					$reply = json_encode($resp);
					$url = $website."/sendmessage?chat_id=".$chat_id."&parse_mode=".'HTML'."&text=".urlencode($mes)."&reply_markup=".$reply;
					file_get_contents($url);
					return;
				}
				$mes = "Подкласс уже был выбран или не пренадлежит Вашему классу, либо Вы ещё не достигли 10-ого уровня.";
				$keyboard = [["👤 Информация","🎒 Инвентарь"],["🏅 Класс","📖Атрибуты","🎓Способности"],["🎽Экипировка","⚒Ремесло","🏠Гл. меню"]];
				$resp = array("keyboard" => $keyboard,"resize_keyboard" => true, "one_time_keyboard" => false);
				$reply = json_encode($resp);
				$url = $website."/sendmessage?chat_id=".$chat_id."&parse_mode=".'HTML'."&text=".urlencode($mes)."&reply_markup=".$reply;
				file_get_contents($url);
				return;
			/////CHOOSE SPECIALIZATION
			case "/sp_choose":
				$result = mysqli_query($link,"SELECT * from classes where name = '$attr[specialization]'");
				$isTaken = mysqli_num_rows($result);
				if($isTaken == 0 and $attr[level] >= 20)
				{
					if($attr['subClass'] == "Paladin" or $attr['subClass'] == "Witcher")
					{
						if($attr['subClass'] == "Paladin")
						{
							$mes = "Выберите Ваш <b>подкласс</b>:\n\n<b>Внимание!</b>\nПодкласс <b>нельзя</b> будет изменить!\n\nЕсли Вы не уверены в своём выборе, или Вам необходима доп. информация по подклассам - воспользуйтесь командой: /sc_info";
							$keyboard = [["Титан","Каратель"],["⬅️ Класс"]];
							$resp = array("keyboard" => $keyboard,"resize_keyboard" => true, "one_time_keyboard" => false);
							$reply = json_encode($resp);
							$url = $website."/sendmessage?chat_id=".$chat_id."&parse_mode=".'HTML'."&text=".urlencode($mes)."&reply_markup=".$reply;
							file_get_contents($url);
							return;
						}
						$mes = "Выберите Ваш <b>подкласс</b>:\n\n<b>Внимание!</b>\nПодкласс <b>нельзя</b> будет изменить!\n\nЕсли Вы не уверены в своём выборе, или Вам необходима доп. информация по подклассам - воспользуйтесь командой: /sc_info";
						$keyboard = [["Деспот","Каратель"],["⬅️ Класс"]];
						$resp = array("keyboard" => $keyboard,"resize_keyboard" => true, "one_time_keyboard" => false);
						$reply = json_encode($resp);
						$url = $website."/sendmessage?chat_id=".$chat_id."&parse_mode=".'HTML'."&text=".urlencode($mes)."&reply_markup=".$reply;
						file_get_contents($url);
						return;
					}
					if($attr['subClass'] == "Magician" or $attr['subClass'] == "Exorcist")
					{
						if($attr['subClass'] == "Magician")
						{
							$mes = "Выберите Ваш <b>подкласс</b>:\n\n<b>Внимание!</b>\nПодкласс <b>нельзя</b> будет изменить!\n\nЕсли Вы не уверены в своём выборе, или Вам необходима доп. информация по подклассам - воспользуйтесь командой: /sc_info";
							$keyboard = [["Архимаг","Владыка"],["⬅️ Класс"]];
							$resp = array("keyboard" => $keyboard,"resize_keyboard" => true, "one_time_keyboard" => false);
							$reply = json_encode($resp);
							$url = $website."/sendmessage?chat_id=".$chat_id."&parse_mode=".'HTML'."&text=".urlencode($mes)."&reply_markup=".$reply;
							file_get_contents($url);
							return;
						}
						$mes = "Выберите Ваш <b>подкласс</b>:\n\n<b>Внимание!</b>\nПодкласс <b>нельзя</b> будет изменить!\n\nЕсли Вы не уверены в своём выборе, или Вам необходима доп. информация по подклассам - воспользуйтесь командой: /sc_info";
						$keyboard = [["Мистик","Владыка"],["⬅️ Класс"]];
						$resp = array("keyboard" => $keyboard,"resize_keyboard" => true, "one_time_keyboard" => false);
						$reply = json_encode($resp);
						$url = $website."/sendmessage?chat_id=".$chat_id."&parse_mode=".'HTML'."&text=".urlencode($mes)."&reply_markup=".$reply;
						file_get_contents($url);
						return;
					}
					if($attr['subClass'] == "Hunter" or $attr['subClass'] == "Ranger")
					{
						if($attr['subClass'] == "Hunter")
						{
							$mes = "Выберите Ваш <b>подкласс</b>:\n\n<b>Внимание!</b>\nПодкласс <b>нельзя</b> будет изменить!\n\nЕсли Вы не уверены в своём выборе, или Вам необходима доп. информация по подклассам - воспользуйтесь командой: /sc_info";
							$keyboard = [["Зверобой","Скаут"],["⬅️ Класс"]];
							$resp = array("keyboard" => $keyboard,"resize_keyboard" => true, "one_time_keyboard" => false);
							$reply = json_encode($resp);
							$url = $website."/sendmessage?chat_id=".$chat_id."&parse_mode=".'HTML'."&text=".urlencode($mes)."&reply_markup=".$reply;
							file_get_contents($url);
							return;
						}
						$mes = "Выберите Ваш <b>подкласс</b>:\n\n<b>Внимание!</b>\nПодкласс <b>нельзя</b> будет изменить!\n\nЕсли Вы не уверены в своём выборе, или Вам необходима доп. информация по подклассам - воспользуйтесь командой: /sc_info";
						$keyboard = [["Ассасин","Скаут"],["⬅️ Класс"]];
						$resp = array("keyboard" => $keyboard,"resize_keyboard" => true, "one_time_keyboard" => false);
						$reply = json_encode($resp);
						$url = $website."/sendmessage?chat_id=".$chat_id."&parse_mode=".'HTML'."&text=".urlencode($mes)."&reply_markup=".$reply;
						file_get_contents($url);
						return;
					}
					if($attr['subClass'] == "Necromancer" or $attr['subClass'] == "Warlock")
					{
						if($attr['subClass'] == "Necromancer")
						{
							$mes = "Выберите Ваш <b>подкласс</b>:\n\n<b>Внимание!</b>\nПодкласс <b>нельзя</b> будет изменить!\n\nЕсли Вы не уверены в своём выборе, или Вам необходима доп. информация по подклассам - воспользуйтесь командой: /sc_info";
							$keyboard = [["Аватар","Мститель"],["⬅️ Класс"]];
							$resp = array("keyboard" => $keyboard,"resize_keyboard" => true, "one_time_keyboard" => false);
							$reply = json_encode($resp);
							$url = $website."/sendmessage?chat_id=".$chat_id."&parse_mode=".'HTML'."&text=".urlencode($mes)."&reply_markup=".$reply;
							file_get_contents($url);
							return;
						}
						$mes = "Выберите Ваш <b>подкласс</b>:\n\n<b>Внимание!</b>\nПодкласс <b>нельзя</b> будет изменить!\n\nЕсли Вы не уверены в своём выборе, или Вам необходима доп. информация по подклассам - воспользуйтесь командой: /sc_info";
						$keyboard = [["Душегуб","Мститель"],["⬅️ Класс"]];
						$resp = array("keyboard" => $keyboard,"resize_keyboard" => true, "one_time_keyboard" => false);
						$reply = json_encode($resp);
						$url = $website."/sendmessage?chat_id=".$chat_id."&parse_mode=".'HTML'."&text=".urlencode($mes)."&reply_markup=".$reply;
						file_get_contents($url);
						return;
					}
					if($attr['subClass'] == "Oracle" or $attr['subClass'] == "Shaman")
					{
						if($attr['subClass'] == "Oracle")
						{
							$mes = "Выберите Ваш <b>подкласс</b>:\n\n<b>Внимание!</b>\nПодкласс <b>нельзя</b> будет изменить!\n\nЕсли Вы не уверены в своём выборе, или Вам необходима доп. информация по подклассам - воспользуйтесь командой: /sc_info";
							$keyboard = [["Проповедник","Друид"],["⬅️ Класс"]];
							$resp = array("keyboard" => $keyboard,"resize_keyboard" => true, "one_time_keyboard" => false);
							$reply = json_encode($resp);
							$url = $website."/sendmessage?chat_id=".$chat_id."&parse_mode=".'HTML'."&text=".urlencode($mes)."&reply_markup=".$reply;
							file_get_contents($url);
							return;
						}
						$mes = "Выберите Ваш <b>подкласс</b>:\n\n<b>Внимание!</b>\nПодкласс <b>нельзя</b> будет изменить!\n\nЕсли Вы не уверены в своём выборе, или Вам необходима доп. информация по подклассам - воспользуйтесь командой: /sc_info";
						$keyboard = [["Мудрец","Друид"],["⬅️ Класс"]];
						$resp = array("keyboard" => $keyboard,"resize_keyboard" => true, "one_time_keyboard" => false);
						$reply = json_encode($resp);
						$url = $website."/sendmessage?chat_id=".$chat_id."&parse_mode=".'HTML'."&text=".urlencode($mes)."&reply_markup=".$reply;
						file_get_contents($url);
						return;
					}
				}
				$mes = "Специализация уже был выбрана, либо Вы ещё не достигли 20-ого уровня.";
				$keyboard = [["👤 Информация","🎒 Инвентарь"],["🏅 Класс","📖Атрибуты","🎓Способности"],["🎽Экипировка","⚒Ремесло","🏠Гл. меню"]];
				$resp = array("keyboard" => $keyboard,"resize_keyboard" => true, "one_time_keyboard" => false);
				$reply = json_encode($resp);
				$url = $website."/sendmessage?chat_id=".$chat_id."&parse_mode=".'HTML'."&text=".urlencode($mes)."&reply_markup=".$reply;
				file_get_contents($url);
				return;
			/////SPECIALIZATION CHOOSE
			case "Титан":
				$result = mysqli_query($link,"SELECT * from classes where name = '$attr[specialization]'");
				$isTaken = mysqli_num_rows($result);
				if($isTaken == 0 and $attr[level] >= 20 and $attr[subClass] == "Paladin")
				{
					$result = mysqli_query($link,"UPDATE users SET specialization = 'Titan' where chat_id = $chat_id");
					$mes = "Вы выбрали подкласс: <b>Титан</b>.";
					$keyboard = [["👤 Информация","🎒 Инвентарь"],["🏅 Класс","📖Атрибуты","🎓Способности"],["🎽Экипировка","⚒Ремесло","🏠Гл. меню"]];
					$resp = array("keyboard" => $keyboard,"resize_keyboard" => true, "one_time_keyboard" => false);
					$reply = json_encode($resp);
					$url = $website."/sendmessage?chat_id=".$chat_id."&parse_mode=".'HTML'."&text=".urlencode($mes)."&reply_markup=".$reply;
					file_get_contents($url);
					return;
				}
				$mes = "Специализация Вам недоступна или уже была выбрана, либо Вы ещё не достигли 20-ого уровня.";
				$keyboard = [["👤 Информация","🎒 Инвентарь"],["🏅 Класс","📖Атрибуты","🎓Способности"],["🎽Экипировка","⚒Ремесло","🏠Гл. меню"]];
				$resp = array("keyboard" => $keyboard,"resize_keyboard" => true, "one_time_keyboard" => false);
				$reply = json_encode($resp);
				$url = $website."/sendmessage?chat_id=".$chat_id."&parse_mode=".'HTML'."&text=".urlencode($mes)."&reply_markup=".$reply;
				file_get_contents($url);
				return;
			case "Каратель":
				$result = mysqli_query($link,"SELECT * from classes where name = '$attr[specialization]'");
				$isTaken = mysqli_num_rows($result);
				if($isTaken == 0 and $attr[level] >= 20 and ($attr[subClass] == "Paladin" or $attr[subClass] == "Witcher"))
				{
					$result = mysqli_query($link,"UPDATE users SET specialization = 'Chastener' where chat_id = $chat_id");
					$mes = "Вы выбрали подкласс: <b>Каратель</b>.";
					$keyboard = [["👤 Информация","🎒 Инвентарь"],["🏅 Класс","📖Атрибуты","🎓Способности"],["🎽Экипировка","⚒Ремесло","🏠Гл. меню"]];
					$resp = array("keyboard" => $keyboard,"resize_keyboard" => true, "one_time_keyboard" => false);
					$reply = json_encode($resp);
					$url = $website."/sendmessage?chat_id=".$chat_id."&parse_mode=".'HTML'."&text=".urlencode($mes)."&reply_markup=".$reply;
					file_get_contents($url);
					return;
				}
				$mes = "Специализация Вам недоступна или уже была выбрана, либо Вы ещё не достигли 20-ого уровня.";
				$keyboard = [["👤 Информация","🎒 Инвентарь"],["🏅 Класс","📖Атрибуты","🎓Способности"],["🎽Экипировка","⚒Ремесло","🏠Гл. меню"]];
				$resp = array("keyboard" => $keyboard,"resize_keyboard" => true, "one_time_keyboard" => false);
				$reply = json_encode($resp);
				$url = $website."/sendmessage?chat_id=".$chat_id."&parse_mode=".'HTML'."&text=".urlencode($mes)."&reply_markup=".$reply;
				file_get_contents($url);
				return;
			case "Деспот":
				$result = mysqli_query($link,"SELECT * from classes where name = '$attr[specialization]'");
				$isTaken = mysqli_num_rows($result);
				if($isTaken == 0 and $attr[level] >= 20 and $attr[subClass] == "Witcher")
				{
					$result = mysqli_query($link,"UPDATE users SET specialization = 'Despot' where chat_id = $chat_id");
					$mes = "Вы выбрали подкласс: <b>Деспот</b>.";
					$keyboard = [["👤 Информация","🎒 Инвентарь"],["🏅 Класс","📖Атрибуты","🎓Способности"],["🎽Экипировка","⚒Ремесло","🏠Гл. меню"]];
					$resp = array("keyboard" => $keyboard,"resize_keyboard" => true, "one_time_keyboard" => false);
					$reply = json_encode($resp);
					$url = $website."/sendmessage?chat_id=".$chat_id."&parse_mode=".'HTML'."&text=".urlencode($mes)."&reply_markup=".$reply;
					file_get_contents($url);
					return;
				}
				$mes = "Специализация Вам недоступна или уже была выбрана, либо Вы ещё не достигли 20-ого уровня.";
				$keyboard = [["👤 Информация","🎒 Инвентарь"],["🏅 Класс","📖Атрибуты","🎓Способности"],["🎽Экипировка","⚒Ремесло","🏠Гл. меню"]];
				$resp = array("keyboard" => $keyboard,"resize_keyboard" => true, "one_time_keyboard" => false);
				$reply = json_encode($resp);
				$url = $website."/sendmessage?chat_id=".$chat_id."&parse_mode=".'HTML'."&text=".urlencode($mes)."&reply_markup=".$reply;
				file_get_contents($url);
				return;
			case "Архимаг":
				$result = mysqli_query($link,"SELECT * from classes where name = '$attr[specialization]'");
				$isTaken = mysqli_num_rows($result);
				if($isTaken == 0 and $attr[level] >= 20 and $attr[subClass] == "Magician")
				{
					$result = mysqli_query($link,"UPDATE users SET specialization = 'Archmage' where chat_id = $chat_id");
					$mes = "Вы выбрали подкласс: <b>Архимаг</b>.";
					$keyboard = [["👤 Информация","🎒 Инвентарь"],["🏅 Класс","📖Атрибуты","🎓Способности"],["🎽Экипировка","⚒Ремесло","🏠Гл. меню"]];
					$resp = array("keyboard" => $keyboard,"resize_keyboard" => true, "one_time_keyboard" => false);
					$reply = json_encode($resp);
					$url = $website."/sendmessage?chat_id=".$chat_id."&parse_mode=".'HTML'."&text=".urlencode($mes)."&reply_markup=".$reply;
					file_get_contents($url);
					return;
				}
				$mes = "Специализация Вам недоступна или уже была выбрана, либо Вы ещё не достигли 20-ого уровня.";
				$keyboard = [["👤 Информация","🎒 Инвентарь"],["🏅 Класс","📖Атрибуты","🎓Способности"],["🎽Экипировка","⚒Ремесло","🏠Гл. меню"]];
				$resp = array("keyboard" => $keyboard,"resize_keyboard" => true, "one_time_keyboard" => false);
				$reply = json_encode($resp);
				$url = $website."/sendmessage?chat_id=".$chat_id."&parse_mode=".'HTML'."&text=".urlencode($mes)."&reply_markup=".$reply;
				file_get_contents($url);
				return;
			case "Владыка":
				$result = mysqli_query($link,"SELECT * from classes where name = '$attr[specialization]'");
				$isTaken = mysqli_num_rows($result);
				if($isTaken == 0 and $attr[level] >= 20 and ($attr[subClass] == "Magician" or $attr[subClass] == "Exorcist"))
				{
					$result = mysqli_query($link,"UPDATE users SET specialization = 'Overlord' where chat_id = $chat_id");
					$mes = "Вы выбрали подкласс: <b>Владыка</b>.";
					$keyboard = [["👤 Информация","🎒 Инвентарь"],["🏅 Класс","📖Атрибуты","🎓Способности"],["🎽Экипировка","⚒Ремесло","🏠Гл. меню"]];
					$resp = array("keyboard" => $keyboard,"resize_keyboard" => true, "one_time_keyboard" => false);
					$reply = json_encode($resp);
					$url = $website."/sendmessage?chat_id=".$chat_id."&parse_mode=".'HTML'."&text=".urlencode($mes)."&reply_markup=".$reply;
					file_get_contents($url);
					return;
				}
				$mes = "Специализация Вам недоступна или уже была выбрана, либо Вы ещё не достигли 20-ого уровня.";
				$keyboard = [["👤 Информация","🎒 Инвентарь"],["🏅 Класс","📖Атрибуты","🎓Способности"],["🎽Экипировка","⚒Ремесло","🏠Гл. меню"]];
				$resp = array("keyboard" => $keyboard,"resize_keyboard" => true, "one_time_keyboard" => false);
				$reply = json_encode($resp);
				$url = $website."/sendmessage?chat_id=".$chat_id."&parse_mode=".'HTML'."&text=".urlencode($mes)."&reply_markup=".$reply;
				file_get_contents($url);
				return;
			case "Мистик":
				$result = mysqli_query($link,"SELECT * from classes where name = '$attr[specialization]'");
				$isTaken = mysqli_num_rows($result);
				if($isTaken == 0 and $attr[level] >= 20 and $attr[subClass] == "Exorcist")
				{
					$result = mysqli_query($link,"UPDATE users SET specialization = 'Mystic' where chat_id = $chat_id");
					$mes = "Вы выбрали подкласс: <b>Мистик</b>.";
					$keyboard = [["👤 Информация","🎒 Инвентарь"],["🏅 Класс","📖Атрибуты","🎓Способности"],["🎽Экипировка","⚒Ремесло","🏠Гл. меню"]];
					$resp = array("keyboard" => $keyboard,"resize_keyboard" => true, "one_time_keyboard" => false);
					$reply = json_encode($resp);
					$url = $website."/sendmessage?chat_id=".$chat_id."&parse_mode=".'HTML'."&text=".urlencode($mes)."&reply_markup=".$reply;
					file_get_contents($url);
					return;
				}
				$mes = "Специализация Вам недоступна или уже была выбрана, либо Вы ещё не достигли 20-ого уровня.";
				$keyboard = [["👤 Информация","🎒 Инвентарь"],["🏅 Класс","📖Атрибуты","🎓Способности"],["🎽Экипировка","⚒Ремесло","🏠Гл. меню"]];
				$resp = array("keyboard" => $keyboard,"resize_keyboard" => true, "one_time_keyboard" => false);
				$reply = json_encode($resp);
				$url = $website."/sendmessage?chat_id=".$chat_id."&parse_mode=".'HTML'."&text=".urlencode($mes)."&reply_markup=".$reply;
				file_get_contents($url);
				return;
			case "Зверобой":
				$result = mysqli_query($link,"SELECT * from classes where name = '$attr[specialization]'");
				$isTaken = mysqli_num_rows($result);
				if($isTaken == 0 and $attr[level] >= 20 and $attr[subClass] == "Hunter")
				{
					$result = mysqli_query($link,"UPDATE users SET specialization = 'Beastmaster' where chat_id = $chat_id");
					$mes = "Вы выбрали подкласс: <b>Зверобой</b>.";
					$keyboard = [["👤 Информация","🎒 Инвентарь"],["🏅 Класс","📖Атрибуты","🎓Способности"],["🎽Экипировка","⚒Ремесло","🏠Гл. меню"]];
					$resp = array("keyboard" => $keyboard,"resize_keyboard" => true, "one_time_keyboard" => false);
					$reply = json_encode($resp);
					$url = $website."/sendmessage?chat_id=".$chat_id."&parse_mode=".'HTML'."&text=".urlencode($mes)."&reply_markup=".$reply;
					file_get_contents($url);
					return;
				}
				$mes = "Специализация Вам недоступна или уже была выбрана, либо Вы ещё не достигли 20-ого уровня.";
				$keyboard = [["👤 Информация","🎒 Инвентарь"],["🏅 Класс","📖Атрибуты","🎓Способности"],["🎽Экипировка","⚒Ремесло","🏠Гл. меню"]];
				$resp = array("keyboard" => $keyboard,"resize_keyboard" => true, "one_time_keyboard" => false);
				$reply = json_encode($resp);
				$url = $website."/sendmessage?chat_id=".$chat_id."&parse_mode=".'HTML'."&text=".urlencode($mes)."&reply_markup=".$reply;
				file_get_contents($url);
				return;
			case "Скаут":
				$result = mysqli_query($link,"SELECT * from classes where name = '$attr[specialization]'");
				$isTaken = mysqli_num_rows($result);
				if($isTaken == 0 and $attr[level] >= 20 and ($attr[subClass] == "Hunter" or $attr[subClass] == "Ranger"))
				{
					$result = mysqli_query($link,"UPDATE users SET specialization = 'Scout' where chat_id = $chat_id");
					$mes = "Вы выбрали подкласс: <b>Скаут</b>.";
					$keyboard = [["👤 Информация","🎒 Инвентарь"],["🏅 Класс","📖Атрибуты","🎓Способности"],["🎽Экипировка","⚒Ремесло","🏠Гл. меню"]];
					$resp = array("keyboard" => $keyboard,"resize_keyboard" => true, "one_time_keyboard" => false);
					$reply = json_encode($resp);
					$url = $website."/sendmessage?chat_id=".$chat_id."&parse_mode=".'HTML'."&text=".urlencode($mes)."&reply_markup=".$reply;
					file_get_contents($url);
					return;
				}
				$mes = "Специализация Вам недоступна или уже была выбрана, либо Вы ещё не достигли 20-ого уровня.";
				$keyboard = [["👤 Информация","🎒 Инвентарь"],["🏅 Класс","📖Атрибуты","🎓Способности"],["🎽Экипировка","⚒Ремесло","🏠Гл. меню"]];
				$resp = array("keyboard" => $keyboard,"resize_keyboard" => true, "one_time_keyboard" => false);
				$reply = json_encode($resp);
				$url = $website."/sendmessage?chat_id=".$chat_id."&parse_mode=".'HTML'."&text=".urlencode($mes)."&reply_markup=".$reply;
				file_get_contents($url);
				return;
			case "Ассасин":
				$result = mysqli_query($link,"SELECT * from classes where name = '$attr[specialization]'");
				$isTaken = mysqli_num_rows($result);
				if($isTaken == 0 and $attr[level] >= 20 and $attr[subClass] == "Ranger")
				{
					$result = mysqli_query($link,"UPDATE users SET specialization = 'Assassin' where chat_id = $chat_id");
					$mes = "Вы выбрали подкласс: <b>Ассасин</b>.";
					$keyboard = [["👤 Информация","🎒 Инвентарь"],["🏅 Класс","📖Атрибуты","🎓Способности"],["🎽Экипировка","⚒Ремесло","🏠Гл. меню"]];
					$resp = array("keyboard" => $keyboard,"resize_keyboard" => true, "one_time_keyboard" => false);
					$reply = json_encode($resp);
					$url = $website."/sendmessage?chat_id=".$chat_id."&parse_mode=".'HTML'."&text=".urlencode($mes)."&reply_markup=".$reply;
					file_get_contents($url);
					return;
				}
				$mes = "Специализация Вам недоступна или уже была выбрана, либо Вы ещё не достигли 20-ого уровня.";
				$keyboard = [["👤 Информация","🎒 Инвентарь"],["🏅 Класс","📖Атрибуты","🎓Способности"],["🎽Экипировка","⚒Ремесло","🏠Гл. меню"]];
				$resp = array("keyboard" => $keyboard,"resize_keyboard" => true, "one_time_keyboard" => false);
				$reply = json_encode($resp);
				$url = $website."/sendmessage?chat_id=".$chat_id."&parse_mode=".'HTML'."&text=".urlencode($mes)."&reply_markup=".$reply;
				file_get_contents($url);
				return;
			case "Аватар":
				$result = mysqli_query($link,"SELECT * from classes where name = '$attr[specialization]'");
				$isTaken = mysqli_num_rows($result);
				if($isTaken == 0 and $attr[level] >= 20 and $attr[subClass] == "Necromancer")
				{
					$result = mysqli_query($link,"UPDATE users SET specialization = 'Avatar' where chat_id = $chat_id");
					$mes = "Вы выбрали подкласс: <b>Аватар</b>.";
					$keyboard = [["👤 Информация","🎒 Инвентарь"],["🏅 Класс","📖Атрибуты","🎓Способности"],["🎽Экипировка","⚒Ремесло","🏠Гл. меню"]];
					$resp = array("keyboard" => $keyboard,"resize_keyboard" => true, "one_time_keyboard" => false);
					$reply = json_encode($resp);
					$url = $website."/sendmessage?chat_id=".$chat_id."&parse_mode=".'HTML'."&text=".urlencode($mes)."&reply_markup=".$reply;
					file_get_contents($url);
					return;
				}
				$mes = "Специализация Вам недоступна или уже была выбрана, либо Вы ещё не достигли 20-ого уровня.";
				$keyboard = [["👤 Информация","🎒 Инвентарь"],["🏅 Класс","📖Атрибуты","🎓Способности"],["🎽Экипировка","⚒Ремесло","🏠Гл. меню"]];
				$resp = array("keyboard" => $keyboard,"resize_keyboard" => true, "one_time_keyboard" => false);
				$reply = json_encode($resp);
				$url = $website."/sendmessage?chat_id=".$chat_id."&parse_mode=".'HTML'."&text=".urlencode($mes)."&reply_markup=".$reply;
				file_get_contents($url);
				return;
			case "Мститель":
				$result = mysqli_query($link,"SELECT * from classes where name = '$attr[specialization]'");
				$isTaken = mysqli_num_rows($result);
				if($isTaken == 0 and $attr[level] >= 20 and ($attr[subClass] == "Necromancer" or $attr[subClass] == "Warlock"))
				{
					$result = mysqli_query($link,"UPDATE users SET specialization = 'Avenger' where chat_id = $chat_id");
					$mes = "Вы выбрали подкласс: <b>Мститель</b>.";
					$keyboard = [["👤 Информация","🎒 Инвентарь"],["🏅 Класс","📖Атрибуты","🎓Способности"],["🎽Экипировка","⚒Ремесло","🏠Гл. меню"]];
					$resp = array("keyboard" => $keyboard,"resize_keyboard" => true, "one_time_keyboard" => false);
					$reply = json_encode($resp);
					$url = $website."/sendmessage?chat_id=".$chat_id."&parse_mode=".'HTML'."&text=".urlencode($mes)."&reply_markup=".$reply;
					file_get_contents($url);
					return;
				}
				$mes = "Специализация Вам недоступна или уже была выбрана, либо Вы ещё не достигли 20-ого уровня.";
				$keyboard = [["👤 Информация","🎒 Инвентарь"],["🏅 Класс","📖Атрибуты","🎓Способности"],["🎽Экипировка","⚒Ремесло","🏠Гл. меню"]];
				$resp = array("keyboard" => $keyboard,"resize_keyboard" => true, "one_time_keyboard" => false);
				$reply = json_encode($resp);
				$url = $website."/sendmessage?chat_id=".$chat_id."&parse_mode=".'HTML'."&text=".urlencode($mes)."&reply_markup=".$reply;
				file_get_contents($url);
				return;
			case "Душегуб":
				$result = mysqli_query($link,"SELECT * from classes where name = '$attr[specialization]'");
				$isTaken = mysqli_num_rows($result);
				if($isTaken == 0 and $attr[level] >= 20 and $attr[subClass] == "Warlock")
				{
					$result = mysqli_query($link,"UPDATE users SET specialization = 'Murderer' where chat_id = $chat_id");
					$mes = "Вы выбрали подкласс: <b>Душегуб</b>.";
					$keyboard = [["👤 Информация","🎒 Инвентарь"],["🏅 Класс","📖Атрибуты","🎓Способности"],["🎽Экипировка","⚒Ремесло","🏠Гл. меню"]];
					$resp = array("keyboard" => $keyboard,"resize_keyboard" => true, "one_time_keyboard" => false);
					$reply = json_encode($resp);
					$url = $website."/sendmessage?chat_id=".$chat_id."&parse_mode=".'HTML'."&text=".urlencode($mes)."&reply_markup=".$reply;
					file_get_contents($url);
					return;
				}
				$mes = "Специализация Вам недоступна или уже была выбрана, либо Вы ещё не достигли 20-ого уровня.";
				$keyboard = [["👤 Информация","🎒 Инвентарь"],["🏅 Класс","📖Атрибуты","🎓Способности"],["🎽Экипировка","⚒Ремесло","🏠Гл. меню"]];
				$resp = array("keyboard" => $keyboard,"resize_keyboard" => true, "one_time_keyboard" => false);
				$reply = json_encode($resp);
				$url = $website."/sendmessage?chat_id=".$chat_id."&parse_mode=".'HTML'."&text=".urlencode($mes)."&reply_markup=".$reply;
				file_get_contents($url);
				return;
			case "Проповедник":
				$result = mysqli_query($link,"SELECT * from classes where name = '$attr[specialization]'");
				$isTaken = mysqli_num_rows($result);
				if($isTaken == 0 and $attr[level] >= 20 and $attr[subClass] == "Oracle")
				{
					$result = mysqli_query($link,"UPDATE users SET specialization = 'Preacher' where chat_id = $chat_id");
					$mes = "Вы выбрали подкласс: <b>Проповедник</b>.";
					$keyboard = [["👤 Информация","🎒 Инвентарь"],["🏅 Класс","📖Атрибуты","🎓Способности"],["🎽Экипировка","⚒Ремесло","🏠Гл. меню"]];
					$resp = array("keyboard" => $keyboard,"resize_keyboard" => true, "one_time_keyboard" => false);
					$reply = json_encode($resp);
					$url = $website."/sendmessage?chat_id=".$chat_id."&parse_mode=".'HTML'."&text=".urlencode($mes)."&reply_markup=".$reply;
					file_get_contents($url);
					return;
				}
				$mes = "Специализация Вам недоступна или уже была выбрана, либо Вы ещё не достигли 20-ого уровня.";
				$keyboard = [["👤 Информация","🎒 Инвентарь"],["🏅 Класс","📖Атрибуты","🎓Способности"],["🎽Экипировка","⚒Ремесло","🏠Гл. меню"]];
				$resp = array("keyboard" => $keyboard,"resize_keyboard" => true, "one_time_keyboard" => false);
				$reply = json_encode($resp);
				$url = $website."/sendmessage?chat_id=".$chat_id."&parse_mode=".'HTML'."&text=".urlencode($mes)."&reply_markup=".$reply;
				file_get_contents($url);
				return;
			case "Друид":
				$result = mysqli_query($link,"SELECT * from classes where name = '$attr[specialization]'");
				$isTaken = mysqli_num_rows($result);
				if($isTaken == 0 and $attr[level] >= 20 and ($attr[subClass] == "Oracle" or $attr[subClass] == "Shaman"))
				{
					$result = mysqli_query($link,"UPDATE users SET specialization = 'Druid' where chat_id = $chat_id");
					$mes = "Вы выбрали подкласс: <b>Друид</b>.";
					$keyboard = [["👤 Информация","🎒 Инвентарь"],["🏅 Класс","📖Атрибуты","🎓Способности"],["🎽Экипировка","⚒Ремесло","🏠Гл. меню"]];
					$resp = array("keyboard" => $keyboard,"resize_keyboard" => true, "one_time_keyboard" => false);
					$reply = json_encode($resp);
					$url = $website."/sendmessage?chat_id=".$chat_id."&parse_mode=".'HTML'."&text=".urlencode($mes)."&reply_markup=".$reply;
					file_get_contents($url);
					return;
				}
				$mes = "Специализация Вам недоступна или уже была выбрана, либо Вы ещё не достигли 20-ого уровня.";
				$keyboard = [["👤 Информация","🎒 Инвентарь"],["🏅 Класс","📖Атрибуты","🎓Способности"],["🎽Экипировка","⚒Ремесло","🏠Гл. меню"]];
				$resp = array("keyboard" => $keyboard,"resize_keyboard" => true, "one_time_keyboard" => false);
				$reply = json_encode($resp);
				$url = $website."/sendmessage?chat_id=".$chat_id."&parse_mode=".'HTML'."&text=".urlencode($mes)."&reply_markup=".$reply;
				file_get_contents($url);
				return;
			case "Мудрец":
				$result = mysqli_query($link,"SELECT * from classes where name = '$attr[specialization]'");
				$isTaken = mysqli_num_rows($result);
				if($isTaken == 0 and $attr[level] >= 20 and $attr[subClass] == "Shaman")
				{
					$result = mysqli_query($link,"UPDATE users SET specialization = 'Sage' where chat_id = $chat_id");
					$mes = "Вы выбрали подкласс: <b>Мудрец</b>.";
					$keyboard = [["👤 Информация","🎒 Инвентарь"],["🏅 Класс","📖Атрибуты","🎓Способности"],["🎽Экипировка","⚒Ремесло","🏠Гл. меню"]];
					$resp = array("keyboard" => $keyboard,"resize_keyboard" => true, "one_time_keyboard" => false);
					$reply = json_encode($resp);
					$url = $website."/sendmessage?chat_id=".$chat_id."&parse_mode=".'HTML'."&text=".urlencode($mes)."&reply_markup=".$reply;
					file_get_contents($url);
					return;
				}
				$mes = "Специализация Вам недоступна или уже была выбрана, либо Вы ещё не достигли 20-ого уровня.";
				$keyboard = [["👤 Информация","🎒 Инвентарь"],["🏅 Класс","📖Атрибуты","🎓Способности"],["🎽Экипировка","⚒Ремесло","🏠Гл. меню"]];
				$resp = array("keyboard" => $keyboard,"resize_keyboard" => true, "one_time_keyboard" => false);
				$reply = json_encode($resp);
				$url = $website."/sendmessage?chat_id=".$chat_id."&parse_mode=".'HTML'."&text=".urlencode($mes)."&reply_markup=".$reply;
				file_get_contents($url);
				return;
			/////СПОСОБНОСТИ
			case "🎓Способности":
				///1
				$result = mysqli_query($link,"SELECT * from classesSpells where name = '$attr[spell1]'");
				$type = mysqli_fetch_array($result,MYSQLI_ASSOC)["type"];
				$typeOne = mysqli_query($link,"SELECT * from text where eng = '$type'");
				$typeOneRus = " -----";

				$isTaken = mysqli_num_rows($result);
				$spell1 = $attr[spell1];
				if($isTaken > 0)
				{
					$result = mysqli_query($link,"SELECT * from classesSpells where name = '$attr[spell1]'");
					$result = mysqli_fetch_array($result,MYSQLI_ASSOC)["name_rus"];
					$spell1 = "<i>$result</i>";
					$typeOneRus = mysqli_fetch_array($typeOne,MYSQLI_ASSOC)["rus"];
				}
				///2
				$result = mysqli_query($link,"SELECT * from classesSpells where name = '$attr[spell2]'");
				$type = mysqli_fetch_array($result,MYSQLI_ASSOC)["type"];
				$typeTwo = mysqli_query($link,"SELECT * from text where eng = '$type'");
				$typeTwoRus = " -----";

				$isTaken = mysqli_num_rows($result);
				$spell2 = $attr[spell2];
				if($isTaken > 0)
				{
					$result = mysqli_query($link,"SELECT * from classesSpells where name = '$attr[spell2]'");
					$result = mysqli_fetch_array($result,MYSQLI_ASSOC)["name_rus"];
					$spell2 = "<i>$result</i>";
					$typeTwoRus = mysqli_fetch_array($typeTwo,MYSQLI_ASSOC)["rus"];
				}
				////3
				$result = mysqli_query($link,"SELECT * from classesSpells where name = '$attr[spell3]'");
				$type = mysqli_fetch_array($result,MYSQLI_ASSOC)["type"];
				$typeThree = mysqli_query($link,"SELECT * from text where eng = '$type'");
				$typeThreeRus = " -----";

				$isTaken = mysqli_num_rows($result);
				$spell3 = $attr[spell3];
				if($isTaken > 0)
				{
					$result = mysqli_query($link,"SELECT * from classesSpells where name = '$attr[spell3]'");
					$result = mysqli_fetch_array($result,MYSQLI_ASSOC)["name_rus"];
					$spell3 = "<i>$result</i>";
					$typeThreeRus = mysqli_fetch_array($typeThree,MYSQLI_ASSOC)["rus"];
				}
				////4
				$mes = "<b>Выбранные способности</b>\n\n🥇 Способность <b>класса</b>: $spell1\nУсил. атрибут: <b>$typeOneRus</b>\nИзменить: /cl_cs\n\n🥈 Способность <b>подкласса</b>: $spell2\nУсил. атрибут: <b>$typeTwoRus</b>\nИзменить: /su_cs\n\n🥉 Способность <b>специализации</b>: $spell3\nУсил. атрибут: <b>$typeThreeRus</b>\nИзменить: /sp_cs\n\nПодробнее: /spells_info";
				$keyboard = [["👤 Информация","🎒 Инвентарь"],["🏅 Класс","📖Атрибуты","🎓Способности"],["🎽Экипировка","⚒Ремесло","🏠Гл. меню"]];
				$resp = array("keyboard" => $keyboard,"resize_keyboard" => true, "one_time_keyboard" => false);
				$reply = json_encode($resp);
				$url = $website."/sendmessage?chat_id=".$chat_id."&parse_mode=".'HTML'."&text=".urlencode($mes)."&reply_markup=".$reply;
				file_get_contents($url);
				return;
			/////CLASS CHANGESPELL
			case "/cl_cs":
				$result = mysqli_query($link,"SELECT * from classes where name = '$attr[class]'");
				$class = mysqli_fetch_array($result,MYSQLI_ASSOC)["name_rus"];
				///1
				$result = mysqli_query($link,"SELECT * from classesSpells where name = '$attr[class]Spell1'");
				$array = mysqli_fetch_array($result,MYSQLI_ASSOC);
				$cfsOneName = $array["name_rus"];
				$cfsOneId = $array["id"];
				$descrOne = $array["descr_rus"];
				$type = $array["type"];
				$result = mysqli_query($link,"SELECT * from text where eng = '$type'");
				$typeOneRus = mysqli_fetch_array($result,MYSQLI_ASSOC)["rus"];
				///2
				$result = mysqli_query($link,"SELECT * from classesSpells where name = '$attr[class]Spell2'");
				$array = mysqli_fetch_array($result,MYSQLI_ASSOC);
				$cfsTwoName = $array["name_rus"];
				$cfsTwoId = $array["id"];
				$descrTwo = $array["descr_rus"];
				$type = $array["type"];
				$result = mysqli_query($link,"SELECT * from text where eng = '$type'");
				$typeTwoRus = mysqli_fetch_array($result,MYSQLI_ASSOC)["rus"];
				///3
				$result = mysqli_query($link,"SELECT * from classesSpells where name = '$attr[class]Spell3'");
				$array = mysqli_fetch_array($result,MYSQLI_ASSOC);
				$cfsThreeName = $array["name_rus"];
				$cfsThreeId = $array["id"];
				$descrThree = $array["descr_rus"];
				$type = $array["type"];
				$result = mysqli_query($link,"SELECT * from text where eng = '$type'");
				$typeThreeRus = mysqli_fetch_array($result,MYSQLI_ASSOC)["rus"];
				///
				$mes = "Доступные способности для класса: <b>$class</b>\n\n<b>$cfsOneName</b>\nОписание: <i>$descrOne</i>\nУсил. атрибут: <b>$typeOneRus</b>\nПрименить: /cl_set_$cfsOneId\n\n<b>$cfsTwoName</b>\nОписание: <i>$descrTwo</i>\nУсил. атрибут: <b>$typeTwoRus</b>\nПрименить: /cl_set_$cfsTwoId\n\n<b>$cfsThreeName</b>\nОписание: <i>$descrThree</i>\nУсил. атрибут: <b>$typeThreeRus</b>\nПрименить: /cl_set_$cfsThreeId\n\n<b>Внимание:</b> В бою можно использовать лишь одну (выбранную) способность!";
				$keyboard = [["👤 Информация","🎒 Инвентарь"],["🏅 Класс","📖Атрибуты","🎓Способности"],["🎽Экипировка","⚒Ремесло","🏠Гл. меню"]];
				$resp = array("keyboard" => $keyboard,"resize_keyboard" => true, "one_time_keyboard" => false);
				$reply = json_encode($resp);
				$url = $website."/sendmessage?chat_id=".$chat_id."&parse_mode=".'HTML'."&text=".urlencode($mes)."&reply_markup=".$reply;
				file_get_contents($url);
				return;
			/////CLASS SETSPELL
			case stripos($text,"/cl_set_"):
				$spellId = substr($text, 8);
				$result = mysqli_query($link,"SELECT * from classesSpells where id = $spellId");
				$array = mysqli_fetch_array($result,MYSQLI_ASSOC);
				$forClass = $array["forClass"];
				if($forClass == $attr['class'] and ($attr[heroStatus] != 2 or $attr[heroStatus] != 3))
				{
					$spellName = $array["name"];
					$mes = "<b>Способность выбрана</b>";
					$result = mysqli_query($link,"UPDATE users SET spell1 = '$spellName' where chat_id = $chat_id");
					$keyboard = [["👤 Информация","🎒 Инвентарь"],["🏅 Класс","📖Атрибуты","🎓Способности"],["🎽Экипировка","⚒Ремесло","🏠Гл. меню"]];
					$resp = array("keyboard" => $keyboard,"resize_keyboard" => true, "one_time_keyboard" => false);
					$reply = json_encode($resp);
					$url = $website."/sendmessage?chat_id=".$chat_id."&parse_mode=".'HTML'."&text=".urlencode($mes)."&reply_markup=".$reply;
					file_get_contents($url);
					return;
				}
				$mes = "Способность нельзя менять во время боя.\nСпособность должна быть доступна для Вас.";
				$keyboard = [["👤 Информация","🎒 Инвентарь"],["🏅 Класс","📖Атрибуты","🎓Способности"],["🎽Экипировка","⚒Ремесло","🏠Гл. меню"]];
				$resp = array("keyboard" => $keyboard,"resize_keyboard" => true, "one_time_keyboard" => false);
				$reply = json_encode($resp);
				$url = $website."/sendmessage?chat_id=".$chat_id."&parse_mode=".'HTML'."&text=".urlencode($mes)."&reply_markup=".$reply;
				file_get_contents($url);
				return;
			/////SUBCLASS CHANGESPELL
			case "/su_cs":
				$result = mysqli_query($link,"SELECT * from classes where name = '$attr[subClass]'");
				$isTaken = mysqli_num_rows($result);
				if($isTaken > 0 and $attr[level] >= 10)
				{
					$result = mysqli_query($link,"SELECT * from classes where name = '$attr[subClass]'");
					$subClass = mysqli_fetch_array($result,MYSQLI_ASSOC)["name_rus"];
					///1
					$result = mysqli_query($link,"SELECT * from classesSpells where name = '$attr[subClass]Spell1'");
					$array = mysqli_fetch_array($result,MYSQLI_ASSOC);
					$cfsOneName = $array["name_rus"];
					$cfsOneId = $array["id"];
					$descrOne = $array["descr_rus"];
					$type = $array["type"];
					$result = mysqli_query($link,"SELECT * from text where eng = '$type'");
					$typeOneRus = mysqli_fetch_array($result,MYSQLI_ASSOC)["rus"];
					///2
					$result = mysqli_query($link,"SELECT * from classesSpells where name = '$attr[subClass]Spell2'");
					$array = mysqli_fetch_array($result,MYSQLI_ASSOC);
					$cfsTwoName = $array["name_rus"];
					$cfsTwoId = $array["id"];
					$descrTwo = $array["descr_rus"];
					$type = $array["type"];
					$result = mysqli_query($link,"SELECT * from text where eng = '$type'");
					$typeTwoRus = mysqli_fetch_array($result,MYSQLI_ASSOC)["rus"];
					///3
					$result = mysqli_query($link,"SELECT * from classesSpells where name = '$attr[subClass]Spell3'");
					$array = mysqli_fetch_array($result,MYSQLI_ASSOC);
					$cfsThreeName = $array["name_rus"];
					$cfsThreeId = $array["id"];
					$descrThree = $array["descr_rus"];
					$type = $array["type"];
					$result = mysqli_query($link,"SELECT * from text where eng = '$type'");
					$typeThreeRus = mysqli_fetch_array($result,MYSQLI_ASSOC)["rus"];
					///
					$mes = "Доступные способности для подкласса: <b>$subClass</b>\n\n<b>$cfsOneName</b>\nОписание: <i>$descrOne</i>\nУсил. атрибут: <b>$typeOneRus</b>\nПрименить: /su_set_$cfsOneId\n\n<b>$cfsTwoName</b>\nОписание: <i>$descrTwo</i>\nУсил. атрибут: <b>$typeTwoRus</b>\nПрименить: /su_set_$cfsTwoId\n\n<b>$cfsThreeName</b>\nОписание: <i>$descrThree</i>\nУсил. атрибут: <b>$typeThreeRus</b>\nПрименить: /su_set_$cfsThreeId\n\n<b>Внимание:</b> В бою можно использовать лишь одну (выбранную) способность!";
					$keyboard = [["👤 Информация","🎒 Инвентарь"],["🏅 Класс","📖Атрибуты","🎓Способности"],["🎽Экипировка","⚒Ремесло","🏠Гл. меню"]];
					$resp = array("keyboard" => $keyboard,"resize_keyboard" => true, "one_time_keyboard" => false);
					$reply = json_encode($resp);
					$url = $website."/sendmessage?chat_id=".$chat_id."&parse_mode=".'HTML'."&text=".urlencode($mes)."&reply_markup=".$reply;
					file_get_contents($url);
					return;
				}
				$mes = "Сначала необходимо выбрать подкласс, нажав на кнопку <b>Класс</b>. Подкласс доступен с <b>10</b> уровня.";
				$keyboard = [["👤 Информация","🎒 Инвентарь"],["🏅 Класс","📖Атрибуты","🎓Способности"],["🎽Экипировка","⚒Ремесло","🏠Гл. меню"]];
				$resp = array("keyboard" => $keyboard,"resize_keyboard" => true, "one_time_keyboard" => false);
				$reply = json_encode($resp);
				$url = $website."/sendmessage?chat_id=".$chat_id."&parse_mode=".'HTML'."&text=".urlencode($mes)."&reply_markup=".$reply;
				file_get_contents($url);
				return;
			/////SUBCLASS SETSPELL
			case stripos($text,"/su_set_"):
				$spellId = substr($text, 8);
				$result = mysqli_query($link,"SELECT * from classesSpells where id = $spellId");
				$array = mysqli_fetch_array($result,MYSQLI_ASSOC);
				$forClass = $array["forClass"];
				if($forClass == $attr['subClass'] and ($attr[heroStatus] != 2 or $attr[heroStatus] != 3))
				{
					$spellName = $array["name"];
					$mes = "<b>Способность выбрана</b>";
					$result = mysqli_query($link,"UPDATE users SET spell2 = '$spellName' where chat_id = $chat_id");
					$keyboard = [["👤 Информация","🎒 Инвентарь"],["🏅 Класс","📖Атрибуты","🎓Способности"],["🎽Экипировка","⚒Ремесло","🏠Гл. меню"]];
					$resp = array("keyboard" => $keyboard,"resize_keyboard" => true, "one_time_keyboard" => false);
					$reply = json_encode($resp);
					$url = $website."/sendmessage?chat_id=".$chat_id."&parse_mode=".'HTML'."&text=".urlencode($mes)."&reply_markup=".$reply;
					file_get_contents($url);
					return;
				}

				$mes = "Способность нельзя менять во время боя.\nСпособность должна быть доступна для Вас.";
				$keyboard = [["👤 Информация","🎒 Инвентарь"],["🏅 Класс","📖Атрибуты","🎓Способности"],["🎽Экипировка","⚒Ремесло","🏠Гл. меню"]];
				$resp = array("keyboard" => $keyboard,"resize_keyboard" => true, "one_time_keyboard" => false);
				$reply = json_encode($resp);
				$url = $website."/sendmessage?chat_id=".$chat_id."&parse_mode=".'HTML'."&text=".urlencode($mes)."&reply_markup=".$reply;
				file_get_contents($url);
				return;
			/////SPECIALIZATION CHANGESPELL
			case "/sp_cs":
				$result = mysqli_query($link,"SELECT * from classes where name = '$attr[specialization]'");
				$isTaken = mysqli_num_rows($result);
				if($isTaken > 0 and $attr[level] >= 20)
				{
					$result = mysqli_query($link,"SELECT * from classes where name = '$attr[specialization]'");
					$specialization = mysqli_fetch_array($result,MYSQLI_ASSOC)["name_rus"];
					///1
					$result = mysqli_query($link,"SELECT * from classesSpells where name = '$attr[specialization]Spell1'");
					$array = mysqli_fetch_array($result,MYSQLI_ASSOC);
					$cfsOneName = $array["name_rus"];
					$cfsOneId = $array["id"];
					$descrOne = $array["descr_rus"];
					$type = $array["type"];
					$result = mysqli_query($link,"SELECT * from text where eng = '$type'");
					$typeOneRus = mysqli_fetch_array($result,MYSQLI_ASSOC)["rus"];
					///2
					$result = mysqli_query($link,"SELECT * from classesSpells where name = '$attr[specialization]Spell2'");
					$array = mysqli_fetch_array($result,MYSQLI_ASSOC);
					$cfsTwoName = $array["name_rus"];
					$cfsTwoId = $array["id"];
					$descrTwo = $array["descr_rus"];
					$type = $array["type"];
					$result = mysqli_query($link,"SELECT * from text where eng = '$type'");
					$typeTwoRus = mysqli_fetch_array($result,MYSQLI_ASSOC)["rus"];
					///3
					$result = mysqli_query($link,"SELECT * from classesSpells where name = '$attr[specialization]Spell3'");
					$array = mysqli_fetch_array($result,MYSQLI_ASSOC);
					$cfsThreeName = $array["name_rus"];
					$cfsThreeId = $array["id"];
					$descrThree = $array["descr_rus"];
					$type = $array["type"];
					$result = mysqli_query($link,"SELECT * from text where eng = '$type'");
					$typeThreeRus = mysqli_fetch_array($result,MYSQLI_ASSOC)["rus"];
					///
					$mes = "Доступные способности для специализации: <b>$specialization</b>\n\n<b>$cfsOneName</b>\nОписание: <i>$descrOne</i>\nУсил. атрибут: <b>$typeOneRus</b>\nПрименить: /sp_set_$cfsOneId\n\n<b>$cfsTwoName</b>\nОписание: <i>$descrTwo</i>\nУсил. атрибут: <b>$typeTwoRus</b>\nПрименить: /sp_set_$cfsTwoId\n\n<b>$cfsThreeName</b>\nОписание: <i>$descrThree</i>\nУсил. атрибут: <b>$typeThreeRus</b>\nПрименить: /sp_set_$cfsThreeId\n\n<b>Внимание:</b> В бою можно использовать лишь одну (выбранную) способность!";
					$keyboard = [["👤 Информация","🎒 Инвентарь"],["🏅 Класс","📖Атрибуты","🎓Способности"],["🎽Экипировка","⚒Ремесло","🏠Гл. меню"]];
					$resp = array("keyboard" => $keyboard,"resize_keyboard" => true, "one_time_keyboard" => false);
					$reply = json_encode($resp);
					$url = $website."/sendmessage?chat_id=".$chat_id."&parse_mode=".'HTML'."&text=".urlencode($mes)."&reply_markup=".$reply;
					file_get_contents($url);
					return;
				}
				$mes = "Сначала необходимо выбрать специализацию, нажав на кнопку <b>Класс</b>. Специализация доступна с <b>20</b> уровня.";
				$keyboard = [["👤 Информация","🎒 Инвентарь"],["🏅 Класс","📖Атрибуты","🎓Способности"],["🎽Экипировка","⚒Ремесло","🏠Гл. меню"]];
				$resp = array("keyboard" => $keyboard,"resize_keyboard" => true, "one_time_keyboard" => false);
				$reply = json_encode($resp);
				$url = $website."/sendmessage?chat_id=".$chat_id."&parse_mode=".'HTML'."&text=".urlencode($mes)."&reply_markup=".$reply;
				file_get_contents($url);
				return;
			/////SPECIALIZATION SETSPELL
			case stripos($text,"/sp_set_"):
				$spellId = substr($text, 8);
				$result = mysqli_query($link,"SELECT * from classesSpells where id = $spellId");
				$array = mysqli_fetch_array($result,MYSQLI_ASSOC);
				$forClass = $array["forClass"];
				if($forClass == $attr['specialization'] and ($attr[heroStatus] != 2 or $attr[heroStatus] != 3))
				{
					$spellName = $array["name"];
					$mes = "<b>Способность выбрана</b>";
					$result = mysqli_query($link,"UPDATE users SET spell3 = '$spellName' where chat_id = $chat_id");
					$keyboard = [["👤 Информация","🎒 Инвентарь"],["🏅 Класс","📖Атрибуты","🎓Способности"],["🎽Экипировка","⚒Ремесло","🏠Гл. меню"]];
					$resp = array("keyboard" => $keyboard,"resize_keyboard" => true, "one_time_keyboard" => false);
					$reply = json_encode($resp);
					$url = $website."/sendmessage?chat_id=".$chat_id."&parse_mode=".'HTML'."&text=".urlencode($mes)."&reply_markup=".$reply;
					file_get_contents($url);
					return;
				}

				$mes = "Способность нельзя менять во время боя.\nСпособность должна быть доступна для Вас.";
				$keyboard = [["👤 Информация","🎒 Инвентарь"],["🏅 Класс","📖Атрибуты","🎓Способности"],["🎽Экипировка","⚒Ремесло","🏠Гл. меню"]];
				$resp = array("keyboard" => $keyboard,"resize_keyboard" => true, "one_time_keyboard" => false);
				$reply = json_encode($resp);
				$url = $website."/sendmessage?chat_id=".$chat_id."&parse_mode=".'HTML'."&text=".urlencode($mes)."&reply_markup=".$reply;
				file_get_contents($url);

				return;
			/////REFERAL
			case "/referal":
				$referal = $attr[referal];
				$mes = "<b>Пригласить друга</b>\nПригласи друга в игру, и получи невероятные бонусы:\n\n+1 к макс. кол-ву энергии;\n10% от добытого другом золота (навсегда);\n\n<b>Твой друг получит:</b>\n\n+50 золотых монет;\nНедельное увеличение опыта и добычи на 35%;\n\nСсылка, по которой необходимо перейти другу: https://telegram.me/mmo_test_1_bot?start=$referal";
				$keyboard = [["👤 Информация","🎒 Инвентарь"],["🏅 Класс","📖Атрибуты","🎓Способности"],["🎽Экипировка","⚒Ремесло","🏠Гл. меню"]];
				$resp = array("keyboard" => $keyboard,"resize_keyboard" => true, "one_time_keyboard" => false);
				$reply = json_encode($resp);
				$url = $website."/sendmessage?chat_id=".$chat_id."&parse_mode=".'HTML'."&text=".urlencode($mes)."&reply_markup=".$reply;
				file_get_contents($url);
				return;

			case "Удалить профиль..❌":
				$result = mysqli_query($link,"UPDATE users SET heroStatus = NULL, regStatus = 0, lang = NULL, nick = NULL, energy = NULL, maxEnergy = NULL, level = NULL, subClass = NULL, specialization = NULL, spell1 = NULL, spell2 = NULL, spell3 = NULL, freePoints = NULL, guild = NULL, xp = NULL, gold = NULL, sGold = NULL, fromReferal = NULL, referal = NULL where chat_id = $chat_id");

				$mes = "Choose your language:";

				$keyboard = [["Русский","English"]];

				$resp = array("keyboard" => $keyboard,"resize_keyboard" => true, "one_time_keyboard" => false,"remove_keyboard" => true);
				$reply = json_encode($resp);

				$url = $website."/sendmessage?chat_id=".$chat_id."&text=".urlencode($mes)."&reply_markup=".$reply;
				file_get_contents($url);

				return;
		}
		$result = mysqli_query($link,"SELECT * from text where id = 4");
		$mes = mysqli_fetch_array($result,MYSQLI_ASSOC)["rus"];
		$keyboard = [["Местность","В город","Карта"],["Группа","Гильдия","Альянс"],["🥋 Герой","Инвентарь","Помощь"],["Удалить профиль..❌"]];
		$resp = array("keyboard" => $keyboard,"resize_keyboard" => true, "one_time_keyboard" => false);
		$reply = json_encode($resp);
		$url = $website."/sendmessage?chat_id=".$chat_id."&parse_mode=".'HTML'."&text=".urlencode($mes)."&reply_markup=".$reply;
		file_get_contents($url);
		return;
	}
	//////////////////////////////////////////////////////////////////////////////////////
	////////////////////////////////////////ДЛЯ АНГЛИЧАН//////////////////////////////////
	//////////////////////////////////////////////////////////////////////////////////////
	if($attr["lang"] == 'English')
	{
		switch($text)
		{
			case "Delete profile..❌":
				$result = mysqli_query($link,"UPDATE users SET heroStatus = NULL, regStatus = 0, lang = NULL, nick = NULL, energy = NULL, maxEnergy = NULL, level = NULL, subClass = NULL, specialization = NULL, spell1 = NULL, spell2 = NULL, spell3 = NULL, freePoints = NULL, guild = NULL, xp = NULL, gold = NULL, sGold = NULL, fromReferal = NULL, referal = NULL where chat_id = $chat_id");

				$mes = "Choose your language:";

				$keyboard = [["Русский","English"]];

				$resp = array("keyboard" => $keyboard,"resize_keyboard" => true, "one_time_keyboard" => false,"remove_keyboard" => true);
				$reply = json_encode($resp);

				$url = $website."/sendmessage?chat_id=".$chat_id."&text=".urlencode($mes)."&reply_markup=".$reply;

				file_get_contents($url);

				return;
		}
		$result = mysqli_query($link,"SELECT * from text where id = 4");
		$mes = mysqli_fetch_array($result,MYSQLI_ASSOC)["eng"];
		$keyboard = [["Terrain","In Town","Map"],["Group","Guild","Alliance"],["Hero","Inventory","Help"],["Delete profile..❌"]];
		$resp = array("keyboard" => $keyboard,"resize_keyboard" => true, "one_time_keyboard" => false);
		$reply = json_encode($resp);
		$url = $website."/sendmessage?chat_id=".$chat_id."&parse_mode=".'HTML'."&text=".urlencode($mes)."&reply_markup=".$reply;
		file_get_contents($url);
		return;
	}
}