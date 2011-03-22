<form method="get">
{+GAMES}
<div>
game_{game} {players}{_started} started{__started}{_!started} <a href="?action=join&game={game}">JOIN</a>{__!started} <a href="?action=spectate&game={game}">SPECTATE</a>
</div>
{++GAMES}
<a href="?action=create">CREATE</a>
</form>

