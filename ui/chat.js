/* чат */
var cid = 0; // latest chat id

function chatUpdateRequest() {
	return {a: 'getchat', chatid: cid};
}
function chatUpdateListener(data) {
return;
/*
function chat_update() {
	if (cid == null) cid = 0; //если не определена, значит впервые видим чат, надо получить всё с первого сообщения
	//FIXIT а нужно ли? получить всё с самого начала... последние 20 сообщений может нужно, а остальное лишнее?
	$.getJSON("game.php", {a: 'getchat', id: cid}, // сообщаем последнее известное нам сообщение
		function(data) {
*/		
			//if(data != null) { //если переменная data не определна, значит новых сообщений нет
				for (var v in data.chat)
					$("#screen").append(data.chat[v].name+": "+data.chat[v].msg+" "+cid+"\n"); 
					//потом можно сделать имя-ссылка, что бы можно щелкнуть, а оно добавилось в строку сообщения
					//увеличиваем наш id
					cid = data.id;
			//}//if
/*			
		}); 
	setTimeout('chat_update()', 3000); // раз в секунду, чтобы заипать сервак)))
*/
}


function send_message() {
	message = $('#message').val();
	$.getJSON("game.php", {a: 'sendmessage', msg: message}, 
		function(data) {
			$("#screen").append(data.responseText+"\n");
			$('#message').val() = "";
			//cid = data.chatid; //FIXIT незнаю.. косяк тут какой-то, с ней может добавиться до 6 сообщений сразу под одним id
		});
	//chat_update(); //обновим окно чата, надеюсь таймер будет один а не два?
	// два будет, отправил и хрен с ним, своё сообщение отобразится при следующем обновлении
}
//FIXIT есть иногда момент времени, когда при отправке сообщения сразу добавляется 2 сообщения, а не одно
// можно думаю просто убрать chat_update() из send_message(), но тогда появится некашерная задрежка
// в чате, что бывает очень раздражительно для человека отправившего сообщение
