diggit (en reddit-klon i PHP), Viktor Jackson, december 2011
------------------------------------------------------------

Funktionalitet:
 * Registrering av användare. Registrering krävs för att kunna posta och rösta på länkar eller kommentera/rösta på kommentarer.
 * Salting/hashing av lösenord för ökad säkerhet. Regler för (någorlunda) starka lösenord implementeras med regex.
 * Posta länkar. Länkens URL måste vara giltig och unik, annars genereras ett felmeddelande.
 * Kategorier, jämförbart med subreddits. En länk utan kategori hamnar i kategorin "main".
 * Redigera och radera egna länkar. (kräver att javascript är aktiverat)
 * Kommentera på länkar samt svara på tidigare kommentarer. (trädstruktur)
 * Redigera och radera egna kommentarer. (kräver att javascript är aktiverat)
 * Användarsida med lista över länkar/kommentarer postade av en användare.
 * Sortera länkar efter: flest röster de senaste 48 timmarna (standard) eller tiden länken postades (senast först).
 * Rösta på länkar och kommentarer (upp eller ner). Man kan även ta bort sin röst, precis som på reddit.
 * Den senaste länken man har besökt markeras med en border, som på reddit. (kräver att javascript är aktivt)
 * Lista över mest aktiva användare på förstasidan.

Styrkor:
 * Presentation (HTML/CSS) och funktion (PHP) är väl åtskiljda mha mallar för länklistan, kommentarsträdet och alla formulär
   Data matas in i mallarna av olika funktioner, med hjälp av str_replace. Tittar man på en mall så ser man att datan (och i
   vissa fall andra mallar, som när vi vill ha ett svarsformulär under en kommentar) representeras av platshållare som 
   ser ut så här: {PLACEHOLDER}
 * CSS och Javascript ligger såklart i egna filer och inte inline
 * Alla funktioner som hämtar data från eller modifierar databasen ligger i en fil (db.php). Alla funktioner för validering,
   felhantering och formattering av data ligger uppdelat på flera filer beroende på vilken aspekt av sidan de tillhör
   (dvs. länkar, kommentarer, användare osv). Vidare finns en fil med funktioner som används på flera ställen, common.php
 * All indata som används för databasfrågor säkras med hjälpfunktioner baserade på mysqli_real_escape_string() för att förhindra
   injektionsattacker.
 * Sessions-id:t kollas mot användartabellen varje gång vi gör tillägg/ändringar i databasen (postar länkar, kommenterar, redigerar,
   raderar). Om det inte stämmer loggas användaren ut och ett nytt sessions-id skapas. Detta ska försvåra kapning av konton genom
   ändring av sessionsvariabler på klientsidan.

Brister:
 - Mycket av sidans funktionalitet kräver att javascript är aktiverat. Det enda man i dagsläget kan göra utan javascript är att
   registrera/logga in och posta länkar. Man kan alltså inte skriva kommentarer, rösta på något eller redigera något. I min iver
   att fördjupa mig i jQuery hann jag inte fixa alternativ funktionalitet utan javascript.
 - Eftersom vi bygger kommentarsträdet med en rekursiv funktion så kan vi inte paginera med en enkel LIMIT i SELECT-frågan som för
   länkar, då detta skulle leda till kommentarer som försvinner eller blir "föräldralösa" på sin sida. Eftersom vi hämtar alla
   kommentarer med en SELECT kan detta leda till tung belastning för servern om vi har väldigt många kommentarer att loopa över,
   filtrera och formattera. Pagination funkar dock i kommentarslistan på användarsidan, då det bara är en rak lista och inget träd.
 - Det går inte att ändra lösenord.
 - Mailadressen används inte till något.
 - Det går inte att redigera eller radera kategorier, bara lägga till nya.
 - Ingen integration med sociala media. Jag kunde ha fixat detta, men fick prioritera annat pga tidsbrist. 
 - Ingen funktion för medlemsbilder. Ännu en sak som fick prioriteras bort.
 - CSS:en är extremt rörig och troligtvis onödigt lång. Sidans stil är inkonsekvent på flera ställen.
 - Sidan är enbart testad med WebKit och Firefox. Inte hunnit testa för IE-kompatibilitet.

Databasdesign
-------------

Vi har fem tabeller:
 * links:
    Innehåller länkarnas data. Kolumnen 'user' är en foreign key som refererar till 'id' av tabellen users.
    Raderas en användare så ändras 'user' till NULL. 'category' är även det en foreign key, 'name' i categories. Raderas en 
    kategori så ändras 'category' till NULL. Om 'category' är NULL ändrar PHP detta till "main" när länken skrivs ut.

 * comments:
   Innehåller kommentarernas data. 'linkid' är en foreign key för 'id' i links. Raderas en länk så ryker även kommentarerna.
   'userid' är en foreign key för 'id' i users. Om en användare tas bort så ändras 'userid' till NULL. Antal kommentarer för
   varje länk räknas av en view, commentcounts.

 * users:
   Innehåller användardata. Detta är bara en vanlig lookup table utan constraints. 
 
 * votes
   Innehåller användar-id, ämnes-id, typ, röstvärde och tid för alla röster. Om en användare raderas ändras användar-id till NULL
   eftersom vi har en foreign key constraint där. Rösterna räknas upp per länk av två views - recentvotes för röster lagda för 
   mindre än 48 timmar sedan, totalvotes för alla röster.
 
 * categories
   Innehåller namn på kategorier. Innehåller även ägare och beskrivning, men dessa används inte än. Kategorins namn är en unik
   key (istället för ett id-fält, eftersom kategorins fulla namn sparas i tabellen links).

... och tre views:
 * commentcounts (Antalet kommentarer för varje länk)
 * recentvotes (Summan av röster för varje länk och kommentar under de senaste 48 timmarna)
 * totalvotes (Summan av alla röster för varje kommentar/länk)
 

Genomgång av de olika sidorna
-----------------------------

Längst upp på varje sida har vi vårt sidhuvud, med länkar för att ändra sorteringen av länkarna på förstasidan till vänster,
länkar till de populäraste kategorierna, och länk för att logga in eller registrera sig längst till höger. När vi är 
inloggade kommer vi ha en länk för att skicka in en ny länk, och inloggningslänken ersätts av en utloggningslänk och vårt 
användarnamn med vår "karma" inom parentes (som reddit, fast här räknas även röster på dina kommentarer). Poängen hämtas 
av funktionen getMyPoints() i functions/db.php. Klickar vi på användarnamnet kommer vi till användarsidan, users.php, där 
våra länkar och kommentarer listas.

Är vi inne i en kategori (antingen genom att ha klickat på kategorinamnet under en länk eller genom att vara i comments.php)
så visas kategorins namn som en länk till kategorins förstasida, och sorteringslänkarna ändras till att avse våran kategori.
Har vi klickat på ett domännamn bredvid en länk ser vi detta istället för kategorin. Funktionaliteten är jämförbar med en 
subreddit. Om vi klickar på "diggit" längst till vänster så återgår vi till huvudsidan, där länkarna från alla kategorier listas.

login.php
---------

Här har vi formulär för inloggning och registrering, som genereras från mallarna login.html och register.html, i mappen forms/,
av funktionerna loginform() och regform() i functions/forms.php. Dessa funktioner tar två arrayer som argument, först en med
felmeddelanden från eventuella tidigare registrerings-/inloggningsförsök (genereras av funktionerna loginErrors() och 
registrationErrors()), sedan en array med indata från det förra försöket. Om dessa argument inte anges får vi ett blankt formulär.

För att registrera anger man användarnamn, lösenord (två gånger), och epostadress. Krav:
 * Användarnamnet får bestå av 3-16 tecken, endast siffror, bokstäver, bindestreck och understreck. Det måste vara unikt.
 * Lösenordet måste minst vara 8 tecken långt och bestå av minst en siffra, minst en stor bokstav och minst en liten bokstav.
 * Lösenordet får endast bestå av siffror, bokstäver och följande tecken: !@#$%*()_
 * Epostadressen är valfri, men om den anges måste den vara giltig och unik.

När formuläret är skickat går vi genom indatan med funktionen registrationErrors() i functions/user.php. Om allt stämmer returneras
false, och vi kan gå vidare med registreringen. Annars returneras en array med felmeddelanden som matas in i formulärsmallen,
tillsammans med användarens data. Om allt gick bra så genererar vi nu ett sk. salt - ett 128 tecken långt hexadecimalt värde 
(funktionen generateSalt() i functions/user.php) som vi konkatenerar med lösenordet: ($salt . $password). Strängen vi får av detta 
hashar vi med sha512, sedan klistrar vi ihop saltet och hashen: ($salt . $hash) (hashPassword() i functions/user.php). Denna sträng
blir det som skickas till databasen.

(Vi vill såklart generera nytt salt för varje användare så en cracker som får reda på saltet inte kan generera en rainbow table för
att hitta alla lösenorden)

Vår hash-saltkombo skickas nu till databasen tillsammas med resten av datan. Funktionen registerUser() i functions/db.php returnerar
sedan vårt användar-id som vi sparar i $_SESSION[id]. Sedan sparar funktionen login() vårt sessions-id i användartabellen. 
Voilá! Registrerad och inloggad. Nu skickas vi tillbaka till index.php och kan se vårt användarnamn längst upp till höger, tillsammans 
med en länk för att logga ut.

Inloggningen funkar som vilken som helst (ange giltigt användarnamn och rätt lösenord, annars får du felmeddelanden). loginErrors() i
functions/user.php kollar att användarnamnet finns i databasen, sen hämtar den saltet/hashen från användartabellen i databasen. 
validatePassword() i users.php skiljer på hashen och saltet (128 tecken var), sen saltar och hashar den det inmatade lösenordet, precis
som vid registreringen, och jämför resultatet med den lagrade hashen. Skiljer de sig returnerar loginErrors() ett felmeddelande (matas in
i formulärsmallen precis som vid registrering), annars godkänns inloggningen. 

Vid godkänd inloggning hämtas användarens id (getUserid($username) i functions/db.php) och sparas i sessionsvariabeln tillsammans med
användarnamnet, precis som förut. Funktionen login() sparar sessions-id i databasen. Inloggning klar och vi skickas tillbaka till förstasidan.

submit.php
----------

När vi är inloggade och klickar på "submit link" i sidhuvudet skickas vi hit. Vi får ett formulär som genereras på samma sätt som inloggnings-
och registreringsformulären, dvs en mall som kommer från filen forms/link.html och skapas av funktionen linkform() i functions/forms.php.

För att posta en länk anger vi titel, URL, kategori och om länken är NSFW eller ej. Krav:
 * Titel och URL måste anges
 * URL:en måste vara giltig och innehålla en toppdomän. Eftersom FILTER_VALIDATE_URL tillåter URL:er utan toppdomän måste vi kolla efter en 
   toppdomän med regex.
 * Kategori måste inte anges (lämnas den blank så får den värdet 'main'). Kategorin får enbart bestå av bokstäver, inga mellanslag, siffror eller
   specialtecken. 

Anger vi en kategori som inte finns så skapas den samtidigt som resten av datan skrivs in i databasen. Har man javascript på och klickar på en 
kategori i listan under fältet så matas den kategorin man klickar på in.

cleanLink() i functions/links.php filtrerar bort skadliga specialtecken och ersätter eventuella måsvingar i indatan ({ }) med HTML entities, 
eftersom våra mallar använder måsvingar för att markera en {PLACEHOLDER} och vi vill undvika konstigheter när länken ska visas på sidan.

Den filtrerade indatan kollas sedan efter fel av funktionen linkErrors() i functions/links.php. Upptäcks några fel returneras en array med
felmeddelanden som linkform() matar in på rätt plats i mallen på det sedvanliga sättet, annars returneras false, och vi kan skicka vidare indatan. 

sendLink() i functions/db.php tar nu indatan och skickar den till databasen. Om kategorin vi angav inte finns så skapas denna. Dessutom tar vi ut
domännamnet från URL:en med hjälp av parse_url(). Det läggs sedan i ett eget fält i links-tabellen. En röst upp för länken registreras automatiskt,
som på reddit.
 
Om allt går bra så returnerar sendLink() ett id för länken, som vi använder för att med header() skicka användaren vidare till 
comments.php?linkid=$id. Men låt oss först kika på...

index.php
---------

En lista med länkar, som kan sorteras på två olika sätt: "what's hot", "what's new" och "most popular". Ungefär som reddit. "Hot" är det som fått mest röster de
senaste två dygnen, medan "new" helt enkelt är det som är nyast. "Popular" är de länkar som fått flest röster genom tiderna.

Om vi klickar på en länk och har javascript aktiverat (varesig vi är inloggade eller ej) så sparas länkens id i en sessionsvariabel via ett anrop
till ajax/lastvisited.php. På så vis kan vi, med hjälp av en CSS-klass, markera den senast besökta länken med en border, precis som på reddit.

Återigen använder vi en mall med {PLACEHOLDERS}, den här gången är det från templates/link.html. Funktionen getLinks() i functions/db.php hämtar
länkarna från databasen och returnerar en array - som innehåller en array med varje länks data, plus annan användbar information som användarnamn, 
antalet röster, inloggad användares röst på den länken (1,-1,0), och antalet kommentarer - plus vilken sida vi är på och det totala antalet sidor, 
för paginationens skull. En foreach-loop och funktionen printLink() matar sedan in vår data i mallen och skriver ut den. 

getLinks() har några argument, som dock är valfria. Först har vi sidnummer (som hämtas från querystring, med 1 som default), sedan mängden rader 
per "sida" (default 25, ställs in med konstanten LINKS_PER_PAGE i config.php (för närvarande står den på 10)) och sist kategori (default NULL). 
Med dessa argument kan vi, med en query string ?page=(int) hämta en viss del av inläggen. Om vi angav page=2 så skulle vi få tillbaka länkar 26-50, 
eftersom det är 25 per sida, och vi bad om sida 2. Voilá, pagination.

Om vi är inloggade skapar funktionen voteArrows() två pilar där rätt pil är markerad utifrån den inloggade användarens röst 
(upp för 1, ner för -1 och ingen alls för 0). Pilarna matas sedan in i mallen där {ARROWS} finns.

Med jQuerys hjälp kan man nu rösta genom att klicka på pilarna, precis som på reddit. När man röstar skickas ens röst via en AJAX-förfrågan till
funktionen vote() i functions/db.php. Har man redan röstat på denna länk så uppdateras rösten, om inte så skapas en ny rad. Om man ångrar sin röst
(t.ex. genom att klicka på pil upp när den redan är markerad) så tas rösten bort från databasen. Eftersom rösten uppdateras varje gång så kan man
rösta hur många gånger som helst, det är ändå bara en röst som registreras. Pilarna och den totala poängen uppdateras direkt på sidan, utan att
sidan måste laddas om.

Om det var man själv som skickade in länken ($_SESSION[id] == $link[user]) så visas länkar för att redigera och radera länken, samt markera den
som NSFW. 

Väljer vi att radera länken får vi bekräfta detta genom att klicka på "yes", sedan tar funktionen deleteLink() i functions/db.php bort länken, 
och för att slippa ladda om sidan tas länken bort från sidan med jQuery. Eftersom vi har en constraint i kommentarstabellen så tas även alla 
kommentarer på länken bort om länken raderas. (ON DELETE CASCADE)

Om vi väljer att redigera länken så får vi med funktionen linkform() upp samma formulär som vi använde för inmatningen av länkar i submit.php,
och om länken var vår egen så skrevs detta formulär in i mallen med style=display:none (dvs dold). Med jQuerys hjälp visar vi formuläret (och
ändrar opaciteten på de andra länkarna, för tydlighetens skull). När formuläret sänds så skickas det till submit.php precis som när vi skickar
in en ny länk, och samma felbehandling och filtrering görs, innan den modifierade länken skrivs in i databasen. Vid fel i inmatningen får vi upp
felmeddelanden som förut, dock sänds vi inte tillbaka till index.php, utan stannar på submit.php, och vid lyckad inmatning skickas vi till
comments.php?linkid=$id, som ovan.

Tillbaka på index.php:
Om vi klickar på "nsfw?" så ändras nsfw-statusen på länken med funktionen nsfw() i functions/db.php, och jQuery ändrar texten på nsfw-länken till
"sfw?" och visar ::NSFW::-markören på länken (som är en tidigare dold <span>). Om vi klickar på en "sfw?" där länken redan är NSFW så händer det
motsatta.

Tidsangivelsen på länken kommer från funktionen timeSince() i functions/common.php, som räknar ut skillnaden i tidsenheter (t.ex 50 minutes ago) 
mellan tiden då sidan laddades och tiden som anges som argument, en timestamp i det format MySQL använder, som vi hämtar från resultatet av 
getLinks() ovan. Är tidsskillnaden 0 returnerar vi istället strängen "Just now".

Längst ner på sidan visas en lista över de fem mest aktiva användarna. Denna hämtas av funktionen getUser() utan argument, och inkluderar mängder länkar
och kommentarer användaren skrivit, och hur många poäng de har.

comments.php
------------

Här visar vi först länken som linkid har som värde i querysträngen (t.ex. ?linkid=3) i samma format som på förstasidan, med funktionerna getLink()
(functions/db.php) och printLink() (functions/links.php). Vi kan göra allt här som vi kunde på förstasidan, dvs rösta, redigera, radera och NSFW:a.

Under länken visas ett kommentarsfält där man kan skriva en kommentar (hämtas av commentform() i functions/forms.php från mallen forms/comment.html).
Submit-knappen är skyddad, så att man inte kan klicka på den förrän man "aktiverat" textfältet genom att klicka på det.

Jag har implementerat lite enklare textformattering här, med samma syntax som reddit, dvs:
 **fet text**
 *kursiv text*
 [länk](http://url.com)

När kommentaren är skriven skickas den till databasen av sendComment(), som returnerar kommentarens id som vi använder för att gå till kommentaren med
header("Location: comments.php?linkid=1#$commentid"). Ens egna kommentarer röstas automatiskt upp, som på reddit.

Under kommentarsfältet har vi vårt kommentarsträd. Först hämtas alla kommentarer till en array av funktionen getComments() i functions/db.php. Denna
array behandlas sedan av den rekursiva funktionen commentTree() i functions/comments.php. Den loopar först över alla kommentarer som inte har någon
förälderkommentar, dvs de kommentarer som är direkt till länken. För varje träff så skriver den ut den relevanta kommentaren (printComment() 
i functions/comments.php som använder mallen templates/comment.html), sedan loopar den över arrayen igen, denna gång på jakt efter kommentarer som 
har den förra som förälder. Sen skriver den ut dessa kommentarer var för sig och loopar vidare på nästa nivå. Detta fortsätter tills alla kommentarer 
är utskrivna. 

Den kommentarstext vi hämtar från databasen är rå, dvs att vi inte sparar några taggar eller dylikt för formattering. Istället läggs dessa på i efterhand
med funktionen parseComment() i functions/comments.php, som filtrerar specialtecken med FILTER_SANITIZE_SPECIAL_CHARS, och använder regex för att söka 
efter och ersätta formatterad text innan vi matar in strängen i mallen och skriver ut den på sidan.

Tidsangivelserna på de utskrivna kommentarerna funkar på samma sätt som på länkarna, dvs med funktionen timeSince().

För varje kommentar får inloggade användare en svarslänk. Klickar man på den tar jQuery fram ett tidigare dolt svarsformulär som fungerar som det ovan.
För egna länkar kan vi även redigera och radera kommentarer. Om vi väljer att redigera så tar jQuery fram samma formulär som för ett svar, men ändrar
ett dolt fält för att berätta för PHP-funktionen sendComment() att den ska redigera den befintliga kommentaren istället för att skapa en ny. Eftersom
texten som returnerades av printComment() redan var formatterad så måste vi hämta texten i dess råa form. Detta gör vi via AJAX, med en förfrågan till
ajax/rawcomment.php?id=$commentid som ger oss resultatet från funktionen rawComment($id) i functions/db.php, som returnerar texten för kommentaren
med det angivna id:t. Denna sträng matar vi in i kommentarsfältet med jQuery, sen är det bara att redigera.

Väljer vi att radera en kommentar så får vi bekräfta detta genom att klicka på "yes". Via ett AJAX-anrop till ajax/delete.php ändras sedan fältet "deleted"
i tabellraden för kommentaren till 1 av funktionen deleteComment().  Vi gör detta istället för att radera hela kommentaren för att bibehålla trädstrukturen. 
En kommentar tas bara bort från databasen helt om länken som den hör till raderas, eftersom vi har en foreign key constraint som refererar till länktabellens
id, med ON DELETE CASCADE.

Om en kommentar har 1 i deleted så ersätts kommentarstexten av "deleted", och opaciteten på kommentaren till en tredjedel. Vi tar även bort pilarna för 
röstning och svara/redigera/radera-länkarna. När vi raderar en länk görs detta live av jQuery efter att kommentaren tagits bort från databasen, så att vi
slipper ladda om sidan.

Att rösta på kommentarer funkar på precis samma sätt som att rösta på länkar.

user.php
--------

Användarsidan kan vi komma till genom att klicka på vårt egna användarnamn längst upp till höger, eller på ett användarnamn under en länk eller på en kommentar.
Funktionen getUser() med användarens ID (från query string) som argument hämtar användarnamn, registreringsdatum och antal länkar, kommentarer och poäng som
användaren har från databasen. Denna information skrivs ut av funktionen printUser() med mallen templates/userinfo.html. Kommentarer och länkar skrivs ut som
länkar vi kan klicka på för att se användarens kommentarer/länkar, som hämtas med getLinks()/getComments(), som, när ett användar-id är angivet inkluderar en 
WHERE-klausul i SELECT-frågan. Kommentarslistan är en rak lista över kommentarer användaren har gjort. Man kan inte svara på eller redigera kommentarer härifrån,
detta för att undvika att man missar poängen med en kommentar när den ses utanför sitt sammanhang. Istället för användarnamnet visas länkens titel som en länk
till kommentarssidan, plus en länk till kategorin länken ligger i.

Länkar kan redigeras, raderas och NSFW:as precis som vanligt.
