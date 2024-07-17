# Nette Palette
Nette palette je rozšíření pro Nette Framework které umožňuje jednoduchuché 
i pokročilé úpravy obrazových souborů včetně inteligentního generování miniatur a náhledů.

Palette u obrázků například umožňuje: změny rozměrů, vkládání vodoznaků, pokročilé transformace, 
nastavení průhlednosti a množství dalších efektních filtrů a funkcí.

## Instalace a integrace do Nette

1. Nejdříve Palette naistalujeme do projektu nejlépe pomocí [composeru](https://getcomposer.org/).
```bash
php composer.phar require pavlista/nette-palette
```

2. Po té v Nette do config.neon zaregistrujeme rozšíření.
```neon
extensions:
    palette: NettePalette\PaletteExtension
```

3. Do config.neon také přidáme sekci s nastavením rozšíření a správně ji vyplníme.
```neon
palette:
    path: 'files/thumbs/'
    url: 'http://website.com/files/thumbs/'
    signingKey: '%uniqueSigningKey%'
    basepath: '/var/www/website.com/www/files/' 
```

- **path:** Je relativní nebo absolutní cesta ke složce do které se mají vygenerované miniatury a obrázky ukládat. Tato složka musí existovat a musí být do ní možné zapisovat!
- **url:** Absolutní url adresa s lomítkem na konci na které je složka s miniatury veřejně dostupná.
- **signingKey:** Náhodný řetězec, kterým se podepisují (http) požadavky na generování miniatur.
- **basepath:** *(nepovinný)* Absolutní cesta k document rootu webu.
- **fallbackImage:** *(nepovinný)* Absolutní cesta k obrázku, který se použije v případě, že požadovaný obrázek neexistuje (užitečné hlavně pro DEV).
- **fallbackImages** *(nepovinný)* Definice pojmenovaných výchozích obrázků ve tvaru `název obrázku` => `absolutní cesta k obrázku`, které je možné v Palette query použít pomocí `FallbackImg`.
- **template:** *(nepovinný)* Pole šablon ve tvaru `název šablony` => `paletteQuery`. Šablony je možné používat v palette query přes `.` např.: `.template`.
- **websiteUrl:** *(nepovinný)* Adresa aplikace s lomítkem na konci pro generování absolutních url adres k obrázkům v cli (např.: `https://localhost/`).
- **pictureLoader:** *(nepovinný)* Služba implementující interface `IPictureLoader`, přes kterou je možné upravit logiku načítání a generování obrázků přes Palette.
- **handleException:** *(nepovinný)* Jak se má pracovat s výjímkami při generování obrázků? true *(default)* - výjímky se logují přes tracy, false - výjímky se vyhazují, string - výjímky se logují do souboru přes tracy.

## Použití v nette
V Nette je služba palette dostupná pod názvem **@palette.service**.

### Latte
V Latte je možné palette používat následujícími způsoby:
_________________

#### Filtr palette
V Latte lze generovat miniatury a různé verze obrázků pomocí filtru palette 
na jehož vstupu musí být vždy cesta k souboru obrázku (ne url adresa) a palette query.

**Příklad:**
```latte
<img src="{$image|palette:'Resize;100;150&Border;2;2;black'}" />
```
Tento kód vygeneruje z obrázku miniaturu obrázku o rozměrech 100 x 150px s 2px černým rámečkem okolo.
_________________
Seznam všech možných filtrů a effektů včetně používání samotného Palette naleznete na [Githubu Palette](https://github.com/MichaelPavlista/palette)

## Důležité odkazy
- [Github Palette](https://github.com/MichaelPavlista/palette)
- [Github Nette Palette](https://github.com/MichaelPavlista/nette-palette)
- [Dokumentace Palette a jejích filtrů](http://palette.pavlista.cz/)
