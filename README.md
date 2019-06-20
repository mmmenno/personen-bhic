# Persoonsobservaties in Schepenakten

Het script vindt personen - met reguliere expressies - in transcripties en beschrijvingen van akten en markeert deze tussen {{dubbele accolades}}. Het script is toegespitst op de schepenakten van het [BHIC](https://www.bhic.nl/het-geheugen-van-brabant) en maakt gebruik van het voorbeeldige hoofdlettergebruik voor namen daarin en daarnaast van een [lijst (Brabantse) voornamen](firstnames.csv). 

Verder bevat het script functies die de gevonden voornamen splitsen in naamdelen zoals gedefinieerd in de [Person Name Vocabulary (PNV)](https://w3id.org/pnv), relaties (ouder/kind, echtgenoten) zoeken tussen verschillende personen en de observatie wegschrijven als RDF - gebruikmakend van de [roar](https://leonvanwissen.nl/vocab/roar/docs/) vocabulary.

#### input - csv akte 12258333

```
12258333,"Ursula Hartongh weduwe van Cornelis Thooft oud-president van 's-Hertogenbosch verkoopt aan  de mombers van de kinderen van Elias van Valentijn een  erfelijke (schuld)brief ter waarde van 1000 gulden. Dit is een schuld ten laste van de stad Dordrecht vermeld in de Statenbrieven gepasseerd te ’s-Gravenhage op 12-01-1640 getekend door Herbert van Beaumonts. Deze brief is de transportante in bezit gekomen bij het overlijden van kapitein-luitenant Paulus Frederick van de Poll.",02-09-1700
```

#### gevonden namen worden {{tussen accolades}} gemarkeerd

```
{{Ursula Hartongh}} weduwe van {{Cornelis Thooft}} oud-president van 's-Hertogenbosch verkoopt aan de mombers van de kinderen van {{Elias van Valentijn}} een erfelijke (schuld)brief ter waarde van 1000 gulden. Dit is een schuld ten laste van de stad Dordrecht vermeld in de Statenbrieven gepasseerd te ’s-Gravenhage op 12-01-1640 getekend door {{Herbert van Beaumonts}}. Deze brief is de transportante in bezit gekomen bij het overlijden van kapitein-luitenant {{Paulus Frederick van de Poll}}.
```

#### namen splitsen en relaties zoeken, persoonsobservaties weergeven in RDF

Het script concludeert uit een string als `{{Persoon 1}} weduwe van {{Persoon 2}}` dat Persoon 1 en Persoon 2 echtgenoten zijn, maar ook dat Persoon 1 vrouw is en Persoon 2 man en overleden voor de datum van de akte. Hieronder de personen in akte 12258333 als turtle.

```
personobs:12258333-1
	roar:documentedIn akte:12258333 ;
	pnv:hasName [
		pnv:literalName "Paulus Frederick van de Poll" ;
		pnv:givenName "Paulus" ;
		pnv:patronym "Frederick" ;
		pnv:surName "van de Poll" ;
		pnv:baseSurname "Poll" ;
		pnv:surnamePrefix "van de" ;
	] ;
	a roar:PersonObservation .

personobs:12258333-2
	roar:documentedIn akte:12258333 ;
	pnv:hasName [
		pnv:literalName "Elias van Valentijn" ;
		pnv:givenName "Elias" ;
		pnv:surName "van Valentijn" ;
		pnv:baseSurname "Valentijn" ;
		pnv:surnamePrefix "van" ;
	] ;
	a roar:PersonObservation .

personobs:12258333-3
	roar:documentedIn akte:12258333 ;
	pnv:hasName [
		pnv:literalName "Herbert van Beaumonts" ;
		pnv:givenName "Herbert" ;
		pnv:surName "van Beaumonts" ;
		pnv:baseSurname "Beaumonts" ;
		pnv:surnamePrefix "van" ;
	] ;
	a roar:PersonObservation .

personobs:12258333-4
	roar:documentedIn akte:12258333 ;
	pnv:hasName [
		pnv:literalName "Ursula Hartongh" ;
		pnv:givenName "Ursula" ;
		pnv:surName "Hartongh" ;
	] ;
	schema:spouse personobs:12258333-5 ;
	schema:gender schema:Female ;
	a roar:PersonObservation .

personobs:12258333-5
	roar:documentedIn akte:12258333 ;
	pnv:hasName [
		pnv:literalName "Cornelis Thooft" ;
		pnv:givenName "Cornelis" ;
		pnv:surName "Thooft" ;
	]
	schema:spouse personobs:12258333-4 ;
	schema:gender schema:Male ;
	bio:death [
		a bio:Death ;
		bio:principal personobs:12258333-5 ;
		sem:hasLatestTimeStamp "1700-09-02"^^xsd:date ;
	] ;
	a roar:PersonObservation .
```

## Resultaten

- In enkele steekproeven zijn rond de 3% vals positieve persoonsnamen gevonden. Dit waren bijvoorbeeld veldnamen die als persoonsnaam herkend werden.
- Het aantal persoonsnamen dat niet of slechts gedeeltelijk herkend werd lag rond de 10%.
- Het splitsen van de naam in delen is lastig, omdat vaak niet te zeggen is wanneer iets een voornaam, patroniem of achternaam is. Hier is de pragmatische aanpak gekozen die het BHIC al hanteert.
- Aan het zoeken naar relaties is maar beperkt tijd besteed, maar heeft toch bijna 238.000 echtgenoten en 72.000 ouder-kindparen opgeleverd. Aan de hand van de huwelijksfrasering ('weduwe van') kon van 151.535 mannen en 152.927 vrouwen het geslacht worden vastgesteld.
- De relatie 'komen voor in dezelfde akte' heb je natuurlijk al.

## Toekomstig werk

- De gebruikte voornamenlijst zou verder geschoond kunnen worden. Nu staan er nog voornamen als 'Er', 'Aen', 'Agt' en 'Holland'.
- Nu blijkt er een 'Holland van der Horst' geleefd te hebben. Als in de namenlijst opgenomen zou worden hoe vaak een naam is voorgekomen, dan zou je bij zeldzame namen de lat wat hoger kunnen leggen: als een naam uit één woord bestaat, als 'Holland', zou je 'm op basis van die zeldzaamheid terzijde kunnen schuiven.
- In zo'n namenlijst zou ook het geslacht opgenomen kunnen worden
- Voor de negentiende-eeuwse data zou de namenlijst van de Historische Steekproef Nederland (HSN) gebruikt kunnen worden.
- Er zijn akten waarin de namen helemaal IN KAPITALEN worden gesteld. Die worden door het script niet herkend. We kunnen, met het risico acroniemen en afkortingen ook binnen bereik te brengen, alle 2e en volgende letters van woorden lowercase maken.
- De vals positieven blijken met enige regelmaat veldnamen. We zouden kunnen proberen uit de context op te maken dat het om land gaat en ze dan uitsluiten.
- De gebruikte scripts zijn allen gebaseerd op reguliere expressies en data. Een meer semantische, tekstanalytische benadering zou betere resultaten kunnen geven. Hetzelfde geldt voor machine learning.

