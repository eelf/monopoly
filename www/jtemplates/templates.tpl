{#template MAIN}
	{#if $T.page == 'login'}
		{#include LOGIN}
	{#elseif $T.page == 'games'}
		{#include GAMES}
	{#elseif $T.page == 'loginnew'}
		{#include LOGINNEW}
	{#/if}
    <!--div id="x"></div-->

	<!--div id="container">
		<div id="gamelist">
			game list:
		</div>
		<div id="chat_container">
			<div id="login_bl">
				login informaton:
			</div>
			<div id="info_bl">
				<div id="chat">
					<textarea id="screen" cols="50" rows="20"  readonly="readonly"></textarea><br />
					<input id="message" size="40"><input type="button" value="send" onclick="send_message();">
				</div>	
			</div>
		</div>
	</div-->
	<div id="log"></div>
{#/template MAIN}

{#template GAMELIST}
{$T.game} <input type="button" value="join" name="{CREATOR}" onclick="joinGame(this.name);">
<input type="button" value="spectate" name="{CREATOR}" onclick="spectateGame(this.name);"><br>
{#/template GAMELIST}

{#template GAMES}
<div id="newgame">NEW GAME:<br/>
name: <input type="text" id="gamename" value="lilit"><br>
maxplayers: <input type="text" id="maxplayers" value="2"><br>
<input type="button" value="new game" onclick="newgame();">
<input type="button" value="logout" onclick="logout();">
</div>
<div id="gamelist"></div>
{#/template GAMES}

{#template LOGIN}
email: <input type="text" id="email"><br>
password: <input type="password" id="password" onkeypress="if(event.keyCode == 13)$('#login').click()"><br>
<input type="button" onclick="login();" value="login" id="login">
<input type="button" onclick="shownewplayer();" value="new player">
{#/template LOGIN}

{#template LOGINNEW}
email: <input type="text" id="email"><br>
password: <input type="password" id="password"><br>
name: <input type="text" id="name"><br>
<input type="button" onclick="newplayer();" value="register">
{#/template LOGINNEW}

