# Anleitung

## Allgemeines

Die Erweiterung verwendet für die Synchronisation derzeit ausschließlich den Dateityp **Spielplan**. Folgende Daten werden abgeglichen:

### Team

* Name
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

Der FTP-Versand muss so eingerichtet werden, daß alle Dateien einer Saison im selben Verzeichnis erstellt werden. Ein empfohlender Dateipfad würde so aussehen:

```
some/folder/${season}/staffel_${divisionIdentifier}
```

Der **divisionIdentifier** ist die Staffelkennung. Diese ID ist NICHT eineindeutig. Sie wird also über mehrere Jahre für die gleiche Staffel verwendet. Im XML des Spielplans entspricht die **divisionIdentifier** dem Attribut **kennung** im Tag `kopfdaten->staffel`.

### Vorarbeiten in TYPO3

#### Wettbewerbe erstellen

Eine Staffel beim DFB entspricht in **T3sports** einem Wettbewerb. Somit muss die Staffel-Kennung als externe ID im Datensatz des Wettbewerbs verwendet werden. Man legt zu Beginn einer neuen Saison pro Staffel einen Wettbewerb an und trägt die Staffelkennung als externe ID ein. Es müssen auch alle weiteren Felder im Wettbewerb korrekt ausgefüllt werden. Mit Ausnahme der Team-Zuordnung wird dieser Datensatz während der Synchronisation nicht verändert.

Es können mehrere Wettbewerbe angelegt und synchronisiert werden.

#### Vereine anpassen

Während der Synchronisation werden die Teams automatisch angelegt. Die Spielplandatei des DFB enthält für jede Mannschaft auch eine ID für den Verein. Wenn diese ID in einen vorhandenen Vereinsdatensatz von T3sports als externe ID eingetragen wird, dann wird die neue Mannschaft automatisch diesem Verein zugeordnet.

**Diese Zuordnung erfolgt auschließlich bei der Neuanlage von Teams! Team-Datensätze werden grundsätzlich nicht aktualisiert.**


#### Scheduler einrichten

Für die Synchronisation muss nun ein Scheduler-Task vom Typ **[DFB Sync] Spielplan aktualisieren** angelegt werden.
Folgende Angaben müssen gemacht werden:

**Saison**
Auswahl der aktuellen Saison. Der Sync wird ausschließlich Wettbewerbe dieser Saison bearbeiten. Müssen gleichzeitig Wettbewerbe aus mehreren Spielzeiten synchronisiert werden, dann wird für jede Saison ein eigener Scheduler-Task benötigt.

**Pfad zum Spielplan**
Hier muss der korrekte Pfad zur Spielplan-Datei eingetragen werden. Der Name der eigentlichen Datei wird dabei dynamisch über einen Platzhalter gebildet. Es wird ein ähnliches Format wie beim DFB verwendet. Allerdings steht nur der Platzhalter `${divisionIdentifier}` zur Verfügung. Analog zum Beispielpfad in den Einstellungen beim Sportmedia könnte der Pfad in TYPO3 so aussehen: 

```
some/folder/2021/staffel_${divisionIdentifier}.xml
```

Der Scheduler sucht dann automatisch Wettbewerbe in der angegebenen Saison mit einer externen ID. Wenn für die ID (die Staffel-Kennung des DFB) eine Spielplan-Datei gefunden wird, dann werden die Daten synchronisiert. Die Angabe des Pfad kann entweder absolut oder relativ zum Installationsverzeichnis (PATH_site) von TYPO3 erfolgen.

#### Ausführung per Command

Alternativ zum Scheduler-Task kann die Synchronisation auch per Command ausgeführt werden. Es sind die gleichen Parameter notwendig.

```
$ ./vendor/bin/typo3 dfbsync:sync -s 2 -p ../spielplan_\${divisionIdentifier}.xml
```

In diesem Beispiel erfolgt die Synchronisation für Wettbewerbe der Saison mit der UID "2".
