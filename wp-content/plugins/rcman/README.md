# RC Manager

WordPress-plugin voor RC-modelvliegclubs: vluchtregistratie, ledenbeheer, rapportages,
statistieken en nieuwsbrieven. Dit is de WordPress-versie van de Laravel-applicatie **Kiyomi**.

## Installatie

1. Installeer de PDF-bibliotheek (dompdf) in deze map:
   ```bash
   composer install --no-dev
   ```
   Zonder `vendor/` werkt alles behalve het genereren van PDF's (vluchtrapporten en leden-exports).
2. Activeer **RC Manager** in WordPress onder *Plugins*.
3. Stel de plugin in via *RC Manager → Instellingen*.
4. Maak pagina's aan met de shortcodes:
   - `[rcman_flight_form]` – vlucht aanmelden (optioneel: `title="..."`, `image="https://..."`)
   - `[rcman_member_signup]` – aanmelden als nieuw lid (optioneel: `title="..."`)

## Functies (Kiyomi → WordPress)

| Kiyomi                                     | RC Manager                                                     |
|--------------------------------------------|----------------------------------------------------------------|
| Vlucht aanmelden (`/flights/create`)       | Shortcode `[rcman_flight_form]`                                |
| Lid aanmelden (`/member/create`)           | Shortcode `[rcman_member_signup]` + welkomstmail + melding     |
| Panel / dashboard                          | *RC Manager → Overzicht*                                       |
| Vluchten bekijken, zoeken, bewerken        | *RC Manager → Vluchten* (zoeken op naam, filter op jaar)       |
| Vluchtrapporten (PDF)                      | *RC Manager → Vluchtrapporten*                                 |
| Statistieken (vluchten + leden)            | *RC Manager → Statistieken* (Chart.js)                         |
| Leden beheren, zoeken, filteren            | *RC Manager → Leden* (rol- en instructeurfilter)               |
| Leden export (PDF)                         | *RC Manager → Leden export*                                    |
| Leden mailen / nieuwsbrief + test e-mail   | *RC Manager → Nieuwsbrief* (wachtrij via WP-Cron)              |
| Instellingen                               | *RC Manager → Instellingen*                                    |
| Logging (laravel / user_error / access)    | *RC Manager → Logs* (systeem / gebruikersfouten / toegang)     |
| Login, wachtwoord reset                    | Standaard WordPress-login                                      |
| Spatie permissies                          | WordPress-capabilities + rol **Clubbestuur**                   |

Kiyomi's *Financials*-module was nog niet uitgewerkt en is daarom niet overgenomen.

## Rechten

Bij activeren krijgen beheerders alle `rcman_*`-capabilities en wordt de WordPress-rol
**Clubbestuur** (`rcman_management`) aangemaakt met dezelfde rechten. Geef bestuursleden die rol
om ze toegang te geven zonder volledige beheerdersrechten.

Lidmaatschapstypes (Bestuur, Junior lid, Aspirant lid, Lid, Donateur, Nieuw/niet betaald) zijn
eigenschappen van een clublid en staan los van WordPress-gebruikers: niet elk lid hoeft in te loggen.

## Structuur

```
rcman.php                 Plugin header + bootstrap
uninstall.php             Opruimen bij verwijderen (alleen als ingesteld)
includes/
  Plugin.php              Koppelt alle onderdelen aan WordPress-hooks
  Installer.php           Activeren / deactiveren
  Capabilities.php        Rechten + rol Clubbestuur
  Settings.php            Instellingen (één optie)
  Database/Schema.php     Tabellen (dbDelta) + licenties seeden
  Enums/                  ModelType, PowerClass, ClubRole, InstructorType, Licence
  Repositories/           Database-queries per onderwerp
  Services/               PDF, rapportages, statistieken, e-mail, mailwachtrij, opslag
  Admin/                  Menu, meldingen, pagina's en WP_List_Tables
  Frontend/               Shortcodes en formulierafhandeling
  Support/                View, Request, Logger, Format helpers
templates/                admin-, frontend-, pdf- en e-mailtemplates
assets/                   CSS en JavaScript
```

## Opslag

Gegenereerde PDF's staan in `wp-content/uploads/rcman/` en zijn afgeschermd met een `.htaccess`
(Apache). Gebruik je **nginx**, blokkeer de map dan zelf:

```nginx
location ~* /wp-content/uploads/rcman/ { deny all; }
```

Downloads lopen altijd via WordPress met rechten- en nonce-controle.

## Data uit Kiyomi overzetten

De tabellen zijn vereenvoudigd: `flights` + `flight_user` + `flight_model` + `flight_submitted_model`
is samengevoegd tot `{prefix}rcman_flights` (één piloot en één model per vlucht), `users` heet
`{prefix}rcman_members` en de Spatie-rollen staan in `{prefix}rcman_member_roles`.
