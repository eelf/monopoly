/* ��� */
var cid = 0; // latest chat id

function chatUpdateRequest() {
	return {a: 'getchat', chatid: cid};
}
function chatUpdateListener(data) {
return;
/*
function chat_update() {
	if (cid == null) cid = 0; //���� �� ����������, ������ ������� ����� ���, ���� �������� �� � ������� ���������
	//FIXIT � ����� ��? �������� �� � ������ ������... ��������� 20 ��������� ����� �����, � ��������� ������?
	$.getJSON("game.php", {a: 'getchat', id: cid}, // �������� ��������� ��������� ��� ���������
		function(data) {
*/		
			//if(data != null) { //���� ���������� data �� ���������, ������ ����� ��������� ���
				for (var v in data.chat)
					$("#screen").append(data.chat[v].name+": "+data.chat[v].msg+" "+cid+"\n"); 
					//����� ����� ������� ���-������, ��� �� ����� ��������, � ��� ���������� � ������ ���������
					//����������� ��� id
					cid = data.id;
			//}//if
/*			
		}); 
	setTimeout('chat_update()', 3000); // ��� � �������, ����� ������� ������)))
*/
}


function send_message() {
	message = $('#message').val();
	$.getJSON("game.php", {a: 'sendmessage', msg: message}, 
		function(data) {
			$("#screen").append(data.responseText+"\n");
			$('#message').val() = "";
			//cid = data.chatid; //FIXIT ������.. ����� ��� �����-��, � ��� ����� ���������� �� 6 ��������� ����� ��� ����� id
		});
	//chat_update(); //������� ���� ����, ������� ������ ����� ���� � �� ���?
	// ��� �����, �������� � ���� � ���, ��� ��������� ����������� ��� ��������� ����������
}
//FIXIT ���� ������ ������ �������, ����� ��� �������� ��������� ����� ����������� 2 ���������, � �� ����
// ����� ����� ������ ������ chat_update() �� send_message(), �� ����� �������� ���������� ��������
// � ����, ��� ������ ����� �������������� ��� �������� ������������ ���������
