# Anleitung

## Allgemeines

Die Erweiterung verwendet für die Synchronisation die Dateitypen **Spielplan** und **Ergebnisse**. Folgende Daten werden abgeglichen:

### Team

* Name
* Spielfrei
* Verein (optional)

### Spiel

* Heim- und Gastmannschaft
* Spielnummer
* Spieldatum
* Spieltag
* Spielstatus
* Stadion (Name im Textfeld)
* Endergebnis

## Einrichtung der Synchronisation

### Vorarbeiten bei Sportmedia

Der FTP-Versand muss so eingerichtet werden, daß alle Dateien eines Typs in einer Saison im selben Verzeichnis erstellt werden. Die empfohlenen Dateipfade würden so aussehen:

```
some/folder/${season}/staffel_${divisionIdentifier}_spielplan
some/folder/${season}/staffel_${divisionIdentifier}_ergebnisse
```

Der **divisionIdentifier** ist die Staffelkennung. Diese ID ist NICHT eineindeutig. Sie wird also über mehrere Jahre für die gleiche Staffel verwendet. Im XML des Spielplans entspricht die **divisionIdentifier** dem Attribut **kennung** im Tag `kopfdaten->staffel`.

### Vorarbeiten in TYPO3

#### Wettbewerbe erstellen

Eine Staffel beim DFB entspricht in **T3sports** einem Wettbewerb. Somit muss die Staffel-Kennung als externe ID im Datensatz des Wettbewerbs verwendet werden. Man legt zu Beginn einer neuen Saison pro Staffel einen Wettbewerb an und trägt die Staffelkennung als externe ID ein. Es müssen auch alle weiteren Felder im Wettbewerb korrekt ausgefüllt werden. Mit Ausnahme der Team-Zuordnung wird dieser Datensatz während der Synchronisation nicht verändert.

Es können mehrere Wettbewerbe angelegt und synchronisiert werden.

#### Vereine anpassen

Während der Synchronisation werden die Teams automatisch angelegt. Die Spielplandatei des DFB enthält für jede Mannschaft auch eine ID für den Verein. Wenn diese ID in einen vorhandenen Vereinsdatensatz von **T3sports** als externe ID eingetragen wird, dann wird die neue Mannschaft automatisch diesem Verein zugeordnet.

**Diese Zuordnung erfolgt auschließlich bei der Neuanlage von Teams! Team-Datensätze werden grundsätzlich nicht aktualisiert.**


#### Scheduler einrichten

Für die Synchronisation muss nun ein Scheduler-Task vom Typ **[DFB Sync] Spielplan aktualisieren** angelegt werden.
Folgende Angaben müssen gemacht werden:

**Saison**

Auswahl der aktuellen Saison. Der Sync wird ausschließlich Wettbewerbe dieser Saison bearbeiten. Müssen gleichzeitig Wettbewerbe aus mehreren Spielzeiten synchronisiert werden, dann wird für jede Saison ein eigener Scheduler-Task benötigt.

**Pfad der Spielplan-Datei**

Hier muss der korrekte Pfad zur Spielplan-Datei eingetragen werden. Der Name der eigentlichen Datei wird dabei dynamisch über einen Platzhalter gebildet. Es wird ein ähnliches Format wie beim DFB verwendet. Allerdings steht nur der Platzhalter `${divisionIdentifier}` zur Verfügung. Die Angabe des Pfads kann entweder absolut oder relativ zum Installationsverzeichnis (PATH_site) von TYPO3 erfolgen.

Analog zum Beispielpfad in den Einstellungen beim Sportmedia könnte der Pfad in TYPO3 so aussehen: 

```
some/folder/2021/staffel_${divisionIdentifier}_spielplan.xml
```

**Pfad der Ergebnis-Datei**

Es gelten die selben Regeln wie bei der Spielplan-Datei.

```
some/folder/2021/staffel_${divisionIdentifier}_ergebnisse.xml
```

Der Scheduler sucht dann automatisch Wettbewerbe in der angegebenen Saison mit einer externen ID. Wenn für die ID (die Staffel-Kennung des DFB) eine Spielplan-Datei gefunden wird, dann werden die Daten synchronisiert. Für jede Paarung wird automatisch nach einem Eintrag in der Ergebnis-Datei gesucht. Gibt es einen Treffer, dann werden die Spieldaten aus der Ergebnis-Datei verwendet.

#### Ausführung per Command

Alternativ zum Scheduler-Task kann die Synchronisation auch per Command ausgeführt werden. Es sind die gleichen Parameter notwendig.

```
$ ./bin/typo3 dfbsync:sync -s 2 -p ../spielplan_\${divisionIdentifier}_spielplan.xml -r ../spielplan_\${divisionIdentifier}_ergebnisse.xml
```

In diesem Beispiel erfolgt die Synchronisation für Wettbewerbe der Saison mit der UID "2".

Per Command hat man zusätzlich mit der Option `-c` die Möglichkeit einen bestimmten Wettbewerb zu synchronisieren. Dafür einfach die UID des Wettbewerbs angeben.
