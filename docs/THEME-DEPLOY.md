# Wdrożenie motywu Rozgadana Jana — staging i produkcja

Workflow z repo: **`development` → `staging` → `main`**, upload plików przez **FTP/SFTP** (ogólny opis w [`DEPLOY.md`](./DEPLOY.md)).

---

## Faza 0 — Przygotowanie lokalne (przed stagingiem)

1. **Upewnij się, że wszystko jest zacommitowane** na `development`:

   ```bash
   git checkout development
   git status   # musi być czysto
   ```

2. **Lokalny smoke test** (http://localhost:8080):

   - strona główna (brand bar, featured stage, filtr kategorii, typographic rows, cover shelf/grid)
   - pojedynczy wpis, archiwum kategorii
   - `/ksiazki/`, pojedyncza recenzja
   - `/o-mnie/` (about strip), 404, wyszukiwarka
   - `php wp-content/themes/rozgadana-jana/tests/test-reading-time.php` → OK

3. **Spisz, co wgrywasz** (tylko to, co się zmieniło):

   - `wp-content/themes/rozgadana-jana/` — **cały folder**
   - `wp-content/mu-plugins/rj-reviews.php`
   - `wp-content/mu-plugins/rj-reviews/`

   **Nie wgrywaj:** `wp-config.php`, `.env`, `wp-content/uploads/`, core WordPressa.

---

## Faza 1 — Wdrożenie na staging

### 1.1 Git

```bash
git checkout staging
git merge development
git diff main -- wp-content/    # przejrzyj zmiany
git push origin staging
```

### 1.2 FTP/SFTP na serwer staging

Wgraj foldery z punktu 3 powyżej. Nadpisz istniejący motyw lub wgraj obok i aktywuj nowy.

### 1.3 Konfiguracja WordPressa (wp-admin staging)

| Krok | Co zrobić |
|------|-----------|
| **Aktywuj motyw** | Wygląd → Motywy → **Rozgadana Jana** |
| **Mu-plugin** | Po wgraniu `mu-plugins/` w menu powinno pojawić się **Recenzje** (osobny typ treści) |
| **Permalinki** | Ustawienia → Bezpośrednie odnośniki → **Nazwa wpisu** → Zapisz (flush rewrite rules) |
| **Strony: Start + Blog** | Strony → Dodaj nową: utwórz stronę **Start** (np. slug `start`) oraz stronę **Blog** (slug `blog`) |
| **Czytanie (ważne)** | Ustawienia → Czytanie → **Strona statyczna**: Strona główna = **Start**, Strona wpisów = **Blog** *(wtedy `/` używa `front-page.php`, a `/blog/` pokazuje wszystkie wpisy)* |
| **Menu główne** | Wygląd → Menu → przypisz do lokalizacji **Menu główne** (`primary`): Start, kategorie, Książki, O mnie |
| **Menu stopki** | Przypisz do **Menu w stopce** (`footer`) |
| **Strona O mnie** | Utwórz/edytuj stronę `o-mnie`, szablon **page-o-mnie.php** (lub meta `_wp_page_template`) |
| **Favicon / Site Icon** | Wygląd → Dostosuj → Tożsamość witryny → **Ikona witryny**: wgraj `wp-content/themes/rozgadana-jana/assets/images/site-icon.png` (okrągłe PNG z przezroczystym tłem). Na serwerze wgraj też rootowy `favicon.ico` z repo. |

### 1.4 Kategorie — sprawdź slugi

Motyw oczekuje **dokładnie** tych slugów (na produkcji już tak jest):

| Kategoria | Slug (adres) |
|-----------|----------------|
| Codzienność z Bogiem | `codziennosc-z-bogiem` |
| Macierzyństwo i rodzina | `macierzynstwo-i-rodzina` |

Wpis → Kategorie → edytuj kategorię → pole **Uproszczona nazwa** (slug). Jeśli slug się nie zgadza, filtr na stronie głównej i kolory kart nie zadziałają poprawnie.

### 1.5 Migracja starych recenzji (ważne)

Na żywej stronie recenzje (np. „Historia Miłości!”) są dziś **zwykłymi wpisami** w kategorii. Nowy motyw oczekuje recenzji jako typ **`recenzja`** (osobny CPT, archiwum `/ksiazki/`).

Szczegółowa instrukcja konwersji: sekcja [Konwersja wpisu na recenzję](#konwersja-wpisu-na-recenzję) poniżej.

**Na stagingu:** najpierw przekonwertuj **jedną** recenzję testowo, sprawdź URL i wygląd, dopiero potem resztę.

### 1.6 Checklist QA na stagingu

- [ ] Strona główna: brand bar, featured stage (najnowszy wpis), filtr JS (klik bez przeładowania), sekcje Przemyślenia (typographic rows) + Recenzje (cover shelf/grid)
- [ ] `/category/codziennosc-z-bogiem/` i `/category/macierzynstwo-i-rodzina/` — typographic rows grouped by year, pagination
- [ ] Pojedynczy wpis: data, czas czytania, poprzedni/następny
- [ ] `/ksiazki/` + pojedyncza recenzja (okładka, autor książki)
- [ ] `/o-mnie/` — about strip z logo i opisem
- [ ] 404, wyszukiwarka (search results show full date on desktop)
- [ ] Mobile: menu hamburger, responsive layout
- [ ] Brak błędów PHP w logach hostingu

---

## Faza 2 — Wdrożenie na produkcję (live)

**Dopiero po akceptacji stagingu.**

```bash
git checkout main
git merge staging
git diff HEAD~1 -- wp-content/    # ostatnia kontrola
git push origin main
```

1. **Kopia zapasowa** — baza (phpMyAdmin / panel) + `wp-content/themes/` i `wp-content/mu-plugins/` z produkcji
2. **FTP** — te same foldery co na staging
3. **wp-admin** — powtórz kroki z tabeli w sekcji 1.3 (aktywacja motywu, permalinki, menu, strona O mnie)
4. **Migracja recenzji** — według planu przetestowanego na stagingu
5. **Szybki test live** — homepage, 1 wpis, 1 kategoria, 1 recenzja, O mnie
6. **Cache** — wyczyść cache hostingu/CDN jeśli jest

### Rollback (gdy coś pójdzie nie tak)

1. Przywróć poprzedni motyw w Wygląd → Motywy
2. Przywróć foldery z backupu FTP
3. W razie problemów z permalinkami: Ustawienia → Bezpośrednie odnośniki → Zapisz

---

## Konwersja wpisu na recenzję

WordPress **nie ma** wbudowanego przycisku „zmień typ treści” w edytorze. Są trzy sensowne sposoby.

### Co się zmienia po konwersji

| Element | Wpis (stary) | Recenzja (nowy) |
|---------|--------------|-----------------|
| Panel | Wpisy | Recenzje |
| URL | `/2026/03/12/historia-milosci/` | `/ksiazki/historia-milosci/` |
| Strona główna | sekcja Przemyślenia | sekcja Recenzje książek |
| Kategorie | używane | **nieużywane** (można usunąć) |
| Nowe pole | — | **Autor książki** (`rj_book_author`) |

**Obraz wyróżniający, treść, data i slug** zostają przy tym samym ID wpisu (metoda WP-CLI) lub trzeba je skopiować ręcznie.

---

### Sposób A — WP-CLI (zalecany, szybki)

Działa lokalnie (`make wp`) i na serwerze (jeśli hosting ma WP-CLI).

**1. Zidentyfikuj wpisy-recenzje** — lista wszystkich wpisów:

```bash
make wp ARGS="post list --post_type=post --fields=ID,post_title,post_date,url --format=table"
```

Zanotuj ID każdej recenzji (np. tytuły książek: „Historia Miłości!” itd.).

**2. Zapisz stary URL** (do przekierowania):

```bash
make wp ARGS="post url <ID>"
# np. https://rozgadanajana.pl/2026/03/12/historia-milosci/
```

**3. Konwertuj typ treści** (to samo ID, ta sama treść i okładka):

```bash
make wp ARGS="post update <ID> --post_type=recenzja"
```

**4. Ustaw autora książki** (ręcznie — stare wpisy tego nie mają):

```bash
make wp ARGS="post meta update <ID> rj_book_author 'Alicja Lenczewska'"
```

**5. Usuń kategorie** (opcjonalnie, porządek):

```bash
make wp ARGS="post term remove <ID> category --all"
```

**6. Odśwież permalinki:**

```bash
make wp ARGS="rewrite flush"
```

**7. Sprawdź nowy URL:**

```bash
make wp ARGS="post url <ID>"
# np. https://rozgadanajana.pl/ksiazki/historia-milosci/
```

**Konwersja wielu wpisów** — powtórz kroki 3–4 dla każdego ID albo pętla (podstaw prawdziwe ID i autorów):

```bash
# Przykład: ID 42 → recenzja, autor Lenczewska
make wp ARGS="post update 42 --post_type=recenzja"
make wp ARGS="post meta update 42 rj_book_author 'Alicja Lenczewska'"
make wp ARGS="post term remove 42 category --all"
```

---

### Sposób B — Ręcznie w panelu (bez WP-CLI)

Gdy na hostingu nie ma WP-CLI:

1. Otwórz stary **Wpis** → skopiuj tytuł, treść, zajawkę
2. Pobierz / zapamiętaj **obraz wyróżniający**
3. **Recenzje → Dodaj recenzję** → wklej treść, ustaw okładkę i **Autor książki**
4. Opublikuj recenzję
5. Stary wpis → **Szkic** lub **Kosz** (żeby nie było duplikatu w Google)
6. Ustaw **przekierowanie 301** ze starego URL na nowy (plugin typu Redirection, lub reguła w panelu hostingu)

Wada: nowe ID, trzeba ręcznie przekierować stary link.

---

### Sposób C — Plugin (jednorazowo)

Zainstaluj tymczasowo **Post Type Switcher** (oficjalny plugin w repozytorium WP):

1. Wpisy → edytuj recenzję
2. W panelu bocznym: **Typ treści** → zmień na **Recenzja** → Aktualizuj
3. Uzupełnij **Autor książki** w polu bocznym
4. Usuń kategorie wpisu (jeśli widoczne)
5. Odinstaluj plugin po migracji wszystkich recenzji

---

### Sposób D — Bezpośrednio w bazie danych (SQL)

To samo co WP-CLI (`post update --post_type=recenzja`), tylko ręcznie w **phpMyAdmin** lub konsoli MySQL. Przydatne na hostingu bez WP-CLI.

**Zanim cokolwiek zrobisz:** pełny backup bazy (Eksport w phpMyAdmin).

**Prefiks tabel** — domyślnie `wp_`, ale na serwerze może być inny (sprawdź w `wp-config.php` → `$table_prefix`).

#### 1. Podgląd — które wpisy konwertować

```sql
SELECT ID, post_title, post_type, post_name, post_date
FROM wp_posts
WHERE post_type = 'post'
  AND post_status = 'publish'
ORDER BY post_date DESC;
```

Zanotuj `ID` recenzji (np. tytuły książek).

#### 2. Konwersja typu treści

```sql
-- Jedna recenzja (podstaw ID):
UPDATE wp_posts
SET post_type = 'recenzja'
WHERE ID = 123
  AND post_type = 'post';

-- Wiele naraz:
UPDATE wp_posts
SET post_type = 'recenzja'
WHERE ID IN (123, 456, 789)
  AND post_type = 'post';
```

Treść, data, slug (`post_name`) i obraz wyróżniający **zostają** — są powiązane z tym samym `ID`.

#### 3. Autor książki (meta)

Stare wpisy nie mają tego pola — trzeba dodać:

```sql
INSERT INTO wp_postmeta (post_id, meta_key, meta_value)
VALUES (123, 'rj_book_author', 'Alicja Lenczewska');
```

Dla wielu recenzji — osobny `INSERT` na każde ID (z właściwym autorem).

#### 4. Usunięcie kategorii (opcjonalnie)

Recenzje ich nie używają; stare powiązania można wyczyścić:

```sql
DELETE tr
FROM wp_term_relationships tr
INNER JOIN wp_term_taxonomy tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
WHERE tr.object_id IN (123, 456, 789)
  AND tt.taxonomy = 'category';
```

#### 5. Po SQL — obowiązkowo w WordPressie

Sam SQL **nie odświeża** reguł adresów URL. Wejdź w panelu:

**Ustawienia → Bezpośrednie odnośniki → Zapisz** (bez zmian — wystarczy kliknięcie „Zapisz zmiany”).

Bez tego `/ksiazki/...` może zwracać 404.

#### Co NIE trzeba ruszać

| Tabela / pole | Dlaczego |
|---------------|----------|
| `post_content`, `post_title`, `post_date` | zostają bez zmian |
| `wp_postmeta` z `_thumbnail_id` | okładka zostaje |
| `guid` | WordPress zwykle nie wymaga aktualizacji |

#### Ryzyko SQL vs WP-CLI

| | SQL | WP-CLI |
|---|-----|--------|
| Szybkość masowej konwersji | tak | tak |
| Hooki WordPressa | nie odpala | odpala |
| Flush permalinków | ręcznie | `rewrite flush` |
| Bezpieczeństwo | łatwo o literówkę | bezpieczniejsze |

Dla kilku–kilkunastu recenzji SQL jest w porządku, jeśli robisz backup i sprawdzasz `ID` w podglądzie przed `UPDATE`.

---

### Przekierowania (stary URL → nowy)

Po konwersji stary link (z datą w ścieżce) przestanie działać. Dla SEO ustaw **301**:

| Stary URL | Nowy URL |
|-----------|----------|
| `/2026/03/12/historia-milosci/` | `/ksiazki/historia-milosci/` |

**Opcje:**

- Plugin **Redirection** (wp-admin → Narzędzia → Redirection)
- Reguła w panelu hostingu (cPanel / DirectAdmin)
- WP-CLI z pluginem Redirection (jeśli zainstalowany)

Przy **Sposobie A (WP-CLI)** ID się nie zmienia — wystarczy jedno przekierowanie na wpis.

---

### Checklist po każdej konwersji

- [ ] Recenzja widoczna w **Recenzje** (nie w Wpisy)
- [ ] `/ksiazki/` — karta się pojawia
- [ ] Strona główna — sekcja „Recenzje książek”
- [ ] Okładka (obraz wyróżniający) wyświetla się
- [ ] Pole **Autor książki** wypełnione
- [ ] Wpis **nie** wisi już w sekcji Przemyślenia
- [ ] Przekierowanie 301 ze starego URL (produkcja)

---

## Instrukcja dla autorki: jak oznaczać treści

Motyw rozróżnia **dwa rodzaje treści**. To nie są dwie kategorie — to dwa **osobne typy** w panelu WordPressa.

### Przemyślenia (wpisy blogowe)

**Gdzie:** Wpisy → Dodaj wpis  
**Kiedy:** osobiste refleksje, codzienność z Bogiem, macierzyństwo, rodzina

**Kategoria — wybierz dokładnie jedną:**

| Kategoria | Kiedy używać |
|-----------|----------------|
| **Codzienność z Bogiem** | wiara, modlitwa, duchowość, relacja z Bogiem, refleksje duchowe |
| **Macierzyństwo i rodzina** | dzieci, małżeństwo, dom, codzienność rodzinna |

**Zasady:**

- Każdy wpis powinien mieć **jedną** kategorię (główną) — od tego zależy filtr/chipy na stronie głównej i przypisanie wpisu do właściwej sekcji w wierszach typograficznych
- Wypełnij **zajawkę** (excerpt) — wyświetla się na kartach; jeśli pusta, WordPress obetnie treść automatycznie
- **Nie** oznaczaj przemyśleń kategorią „recenzja” — recenzje mają osobny typ treści

**Gdzie widać wpis:**

- Strona główna → sekcja „Przemyślenia” (filtr: Wszystko / Codzienność z Bogiem / Macierzyństwo i rodzina)
- Archiwum kategorii, np. `/category/codziennosc-z-bogiem/`

---

### Recenzje książek

**Gdzie:** Recenzje → Dodaj recenzję *(nie „Wpisy”)*  
**Kiedy:** recenzja książki, świadectwo literackie

**Pola do wypełnienia:**

| Pole | Co wpisać |
|------|-----------|
| **Tytuł** | tytuł książki (np. „Historia Miłości!”) |
| **Treść** | pełna recenzja |
| **Zajawka** | krótki teaser na kartę (opcjonalnie) |
| **Obraz wyróżniający** | okładka książki |
| **Autor książki** (panel boczny) | np. „Alicja Lenczewska” — **nie** mylić z autorką bloga |

**Zasady:**

- **Nie** przypisuj recenzji kategorii wpisu — recenzje nie używają kategorii
- **Nie** publikuj recenzji jako zwykły Wpis — wtedy nie trafią do sekcji „Recenzje książek” ani pod `/ksiazki/`

**Gdzie widać recenzję:**

- Strona główna → sekcja „Recenzje książek”
- Archiwum `/ksiazki/`
- Osobna strona recenzji z okładką i autorem książki

---

### Szybka ściągawka

| Treść | Typ w panelu | Kategoria | Dodatkowe pola |
|-------|--------------|-----------|----------------|
| Przemyślenie o wierze | **Wpis** | Codzienność z Bogiem | — |
| Przemyślenie o rodzinie | **Wpis** | Macierzyństwo i rodzina | — |
| Recenzja książki | **Recenzja** | *(brak)* | okładka + autor książki |
| Strona statyczna (O mnie) | **Strona** | *(brak)* | szablon „O mnie” |

---

## Kolejność rekomendowana

1. Merge `development` → `staging` + FTP + konfiguracja wp-admin
2. Testy na stagingu + migracja 1–2 starych recenzji jako proof of concept
3. Akceptacja wizualna i treściowa
4. Merge `staging` → `main` + FTP na produkcję
5. Migracja pozostałych recenzji + ukrycie starych duplikatów
