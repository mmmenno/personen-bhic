# Persoonsobservaties in Schepenakten

Het script vindt personen - met reguliere expressies - in transcripties en beschrijvingen van akten en markeert deze tussen {{dubbele accolades}}. Het script is toegespitst op de schepenakten van het [BHIC](https://www.bhic.nl/het-geheugen-van-brabant) en maakt gebruik van het voorbeeldige hoofdlettergebruik voor namen daarin en daarnaast van een [lijst (Brabantse) voornamen](names/firstnames.csv). 

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

## Gebruik scripts

Volg de volgende stappen om de scripts te gebruiken:

- git clone of download deze repository naar een directory naar keuze
- zorg dat php op je systeem draait
- ga naar de command line en run een scripts naar keuze

```
> php persons2csv.php 
```
Dit script extraheert namen en zet ze in een csv-bestand.


```
> php persons2rdf.php 
```
Dit script extraheert namen en relaties en toont die gegevens als ttl (turtle).

```
> php persons2rdf.php > personobservations.ttl
```
Dit script extraheert namen en relaties en slaat die gegevens in het bestand `personobservatoins.ttl`

Om namen in aktebeschrijvingen / transcripties te vinden en die te exporteren als csv-bestand

```
> php findpersons.php 
```
Dit script is te gebruiken als debugscript. Door verschillende variabelen te uncommenten kan je zien wat er gebeurt.

```
> php create-sample-lines.php 
```
Om een kleiner testbestand te maken van een groot bestand met aktebeschrijvingen. Het pakt elke zoveelste (geef zelf een getal op) regel uit de csv en plaatst die in een nieuwe csv.

## Door scripts gebruikte data

In de map [names](names/) is data te vinden op basis waarvan beslissingen worden genomen. 

- Het bestand [firstnames.csv](names/firstnames.csv) bevat voornamen. Het script checkt of het eerste deel van de naam in deze lijst voorkomt.
- Het bestand [notnames.csv](names/notnames.csv) bevat frases die, indien ze beginnen met een hoofdletter, voor namen aangezien kunnen worden. Het script maakt ze geheel lowercase voordat er naar namen gezocht wordt. Aangezien nu in alle gevallen gecheckt wordt of een voornaam in de voornamenlijst voorkomt zijn vooral frases waarvan het eerste deel een voornaam lijkt van belang ('Philippus gulden').
- Het bestand [placenames.csv](names/placenames.csv) bevat plaatsnamen die door het script nooit meegenomen worden.


## Resultaten

- In de eerste steekproeven zijn rond de 3% vals positieve persoonsnamen gevonden. Dit waren bijvoorbeeld veldnamen die als persoonsnaam herkend werden.
- Het aantal persoonsnamen dat niet of slechts gedeeltelijk herkend werd lag rond de 10%.
- Het splitsen van de naam in delen is lastig, omdat vaak niet te zeggen is wanneer iets een voornaam, patroniem of achternaam is. Hier is de pragmatische aanpak gekozen die het BHIC al hanteert.
- Aan het zoeken naar relaties is maar beperkt tijd besteed, maar heeft toch bijna 238.000 echtgenoten en 72.000 ouder-kindparen opgeleverd. Aan de hand van de huwelijksfrasering ('weduwe van') kon van 151.535 mannen en 152.927 vrouwen het geslacht worden vastgesteld.
- De relatie 'komen voor in dezelfde akte' heb je natuurlijk al.

## Aanpassingen na eerste steekproeven

- De gebruikte voornamenlijst is verder geschoond.
- Het aantal keer dat een voornaam voorkomt is toegevoegd aan de voornamenlijst. Zeldzame namen uitsluiten gaf echter niet de gewenste resultaten (o.a. omdat er meer dan 16.000 namen maar 1 x voorkomen - dat geeft dus veel vals negatieven). Het bleek handiger om de voornamenlijst verder te schonen en enkele voornamen die wel degelijk als voornaam voorkwamen ('Holland van der Horst') toch uit de namenlijst te verwijderen.
- Er zijn akten waarin de namen helemaal IN KAPITALEN worden gesteld. Het script maakt nu alle 2e en volgende letters van dergelijke woorden lowercase. Tussenvoegsels worden helemaal lowercase gemaakt.
- De vals positieven blijken met enige regelmaat veldnamen. Het script is aangepast en schuift nu alle namen die vooraf worden gegaan door 'de', 'het' en 'te' terzijde.
- Soms blijken Initialen voor te komen zonder punt (A van der Zande). Die worden nu ook als initiaal herkend.

## Mogelijke toekomstige verbeteringen
- De namenlijst kan altijd beter.
- In de namenlijst zou ook het geslacht opgenomen kunnen worden. Nu we het er toch over hebben, eigenlijk zou er een landelijke voornamenbank ingericht moeten worden, waarin behalve geslacht ook periode (eeuw?) en regio worden vermeld.
- Voor de negentiende-eeuwse data zou de namenlijst van de Historische Steekproef Nederland (HSN) gebruikt kunnen worden. Deze is echter niet als open data beschikbaar.
- De gebruikte scripts zijn allen gebaseerd op reguliere expressies en data. Een meer semantische, tekstanalytische benadering zou betere resultaten kunnen geven. Hetzelfde geldt voor machine learning.
