# Circular Transparent Favicon Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Replace the square opaque root favicon with a circular transparent icon and wire WordPress Site Icon so browser tabs no longer show a white plate.

**Architecture:** One-shot Python (Pillow) script crops the high-res round logo with a circular alpha mask and padding, writes multi-size `favicon.ico` plus a 512×512 `site-icon.png` in the theme. Import that PNG into Media Library and set `site_icon`. Update deploy docs. No theme PHP/CSS changes for favicon output.

**Tech Stack:** Python 3 + Pillow, WordPress Site Icon (`site_icon` option), `make wp` (wp-cli via Docker), root `favicon.ico`.

**Spec:** `docs/superpowers/specs/2026-08-06-favicon-circular-transparent-design.md`

## Global Constraints

- Keep the existing portrait + wreath artwork; no redraw, no simplified 16×16 mark, no SVG favicon.
- Circular hard mask; outside the circle fully transparent (alpha 0); ~4–6% padding inset from canvas edge.
- Source for generation: `docs/jana_logo_official-scaled.jpg` (2560×2560) — same artwork as brand logo. Do **not** upscale the compressed theme `logo-round.jpg` (192×192).
- Do not change on-page logo display, brand bar, or theme favicon hooks/templates.
- Comments and commit messages in English; `docs/THEME-DEPLOY.md` may stay Polish.

---

## File map

| File | Role |
|------|------|
| `docs/jana_logo_official-scaled.jpg` | High-res source for generation (read-only) |
| `scripts/generate-favicon.py` | One-shot generator: circular mask → ICO + PNG |
| `/favicon.ico` (repo root = WP root) | Multi-size ICO 16/32/48 with alpha |
| `wp-content/themes/rozgadana-jana/assets/images/site-icon.png` | 512×512 circular PNG for Site Icon re-import |
| `docs/THEME-DEPLOY.md` | Favicon deploy checklist row |
| WP DB `site_icon` option | Live `<link rel="icon">` via core `wp_site_icon()` |

---

### Task 1: Generate circular assets and replace favicon.ico

**Files:**
- Create: `scripts/generate-favicon.py`
- Create: `wp-content/themes/rozgadana-jana/assets/images/site-icon.png`
- Modify: `favicon.ico` (overwrite)

**Interfaces:**
- Consumes: `docs/jana_logo_official-scaled.jpg`
- Produces: root `favicon.ico` with sizes (16, 16), (32, 32), (48, 48); theme `site-icon.png` at exactly 512×512 RGBA with transparent corners

- [ ] **Step 1: Write the generator script**

Create `scripts/generate-favicon.py` with this content:

```python
#!/usr/bin/env python3
"""Generate circular transparent favicon.ico and site-icon.png from the official logo."""

from __future__ import annotations

from pathlib import Path

from PIL import Image, ImageDraw

ROOT = Path(__file__).resolve().parents[1]
SOURCE = ROOT / "docs" / "jana_logo_official-scaled.jpg"
OUT_ICO = ROOT / "favicon.ico"
OUT_PNG = (
    ROOT
    / "wp-content"
    / "themes"
    / "rozgadana-jana"
    / "assets"
    / "images"
    / "site-icon.png"
)
PADDING = 0.05  # 5% inset from canvas edge
ICO_SIZES = [(16, 16), (32, 32), (48, 48)]
SITE_ICON_SIZE = 512


def circular_icon(source: Image.Image, size: int, padding: float) -> Image.Image:
    """Resize source to square, apply circular alpha mask with padding."""
    img = source.convert("RGBA")
    # Force square by center-crop if needed (official logo is already square).
    w, h = img.size
    side = min(w, h)
    left = (w - side) // 2
    top = (h - side) // 2
    img = img.crop((left, top, left + side, top + side))
    img = img.resize((size, size), Image.Resampling.LANCZOS)

    mask = Image.new("L", (size, size), 0)
    draw = ImageDraw.Draw(mask)
    inset = int(round(size * padding))
    draw.ellipse((inset, inset, size - inset - 1, size - inset - 1), fill=255)

    out = Image.new("RGBA", (size, size), (0, 0, 0, 0))
    out.paste(img, (0, 0), mask)
    return out


def main() -> None:
    if not SOURCE.is_file():
        raise SystemExit(f"Missing source: {SOURCE}")

    src = Image.open(SOURCE)
    site = circular_icon(src, SITE_ICON_SIZE, PADDING)
    OUT_PNG.parent.mkdir(parents=True, exist_ok=True)
    site.save(OUT_PNG, format="PNG", optimize=True)

    ico_frames = [circular_icon(src, s[0], PADDING) for s in ICO_SIZES]
    # Pillow writes multi-size ICO when given a list via append_images.
    ico_frames[0].save(
        OUT_ICO,
        format="ICO",
        sizes=ICO_SIZES,
        append_images=ico_frames[1:],
    )
    print(f"Wrote {OUT_PNG} ({SITE_ICON_SIZE}x{SITE_ICON_SIZE})")
    print(f"Wrote {OUT_ICO} sizes={ICO_SIZES}")


if __name__ == "__main__":
    main()
```

- [ ] **Step 2: Run the generator**

```bash
python3 scripts/generate-favicon.py
```

Expected: prints paths for `site-icon.png` and `favicon.ico`; exits 0.

- [ ] **Step 3: Verify PNG transparency and ICO sizes**

```bash
python3 <<'PY'
from PIL import Image
from PIL.IcoImagePlugin import IcoFile

png = Image.open('wp-content/themes/rozgadana-jana/assets/images/site-icon.png')
assert png.size == (512, 512) and png.mode == 'RGBA'
# Corner pixel must be fully transparent
assert png.getpixel((0, 0))[3] == 0
# Near-center should be opaque
cx, cy = 256, 256
assert png.getpixel((cx, cy))[3] > 200
print('PNG OK', png.size, png.mode)

with open('favicon.ico', 'rb') as f:
    ico = IcoFile(f)
    sizes = set(ico.sizes())
assert {(16, 16), (32, 32), (48, 48)} <= sizes
for s in [(16, 16), (32, 32), (48, 48)]:
    frame = ico.getimage(s)
    assert frame.mode in ('RGBA', 'RGB', 'P')
    # Sample corner after convert
    rgba = frame.convert('RGBA')
    assert rgba.getpixel((0, 0))[3] == 0, f'ICO {s} corner not transparent'
print('ICO OK', sizes)
PY
```

Expected: `PNG OK …` and `ICO OK …`.

- [ ] **Step 4: Commit**

```bash
git add scripts/generate-favicon.py favicon.ico \
  wp-content/themes/rozgadana-jana/assets/images/site-icon.png
git commit -m "$(cat <<'EOF'
feat: circular transparent favicon and site-icon.png

EOF
)"
```

---

### Task 2: Set WordPress Site Icon locally

**Files:**
- Modify: WordPress DB option `site_icon` (via wp-cli; not a git file)
- Test: HTTP/`wp option get` against local stack

**Interfaces:**
- Consumes: `wp-content/themes/rozgadana-jana/assets/images/site-icon.png` from Task 1
- Produces: `site_icon` option set to a Media Library attachment ID; homepage `<head>` contains `rel="icon"`

- [ ] **Step 1: Ensure local WordPress is up**

```bash
make up
```

Expected: containers running; site reachable at `http://localhost:8080` (or the project’s documented local URL).

- [ ] **Step 2: Import site-icon.png and set site_icon**

```bash
ID=$(make wp ARGS="media import wp-content/themes/rozgadana-jana/assets/images/site-icon.png --title='Site Icon' --porcelain" | tr -d '\r')
echo "attachment=$ID"
make wp ARGS="option update site_icon $ID"
make wp ARGS="option get site_icon"
```

Expected: `attachment=` prints a positive integer; `option get site_icon` prints the same ID.

- [ ] **Step 3: Verify head tags and root favicon**

```bash
curl -sS http://localhost:8080/ | grep -E 'rel="icon"|rel="apple-touch-icon"' | head -20
file favicon.ico
python3 -c "from PIL.IcoImagePlugin import IcoFile; f=open('favicon.ico','rb'); print(set(IcoFile(f).sizes()))"
```

Expected: at least one `rel="icon"` line; ICO reports sizes including 16/32/48.

- [ ] **Step 4: Manual visual check**

Open `http://localhost:8080/` in a browser (hard-refresh if cached). Confirm the tab icon reads as a **circle** without a white square plate on both light and dark tab strips if available.

- [ ] **Step 5: No code commit required for DB-only change**

If Step 2/3 failed, fix and re-run; do not invent theme PHP to print icons.

---

### Task 3: Update deploy docs

**Files:**
- Modify: `docs/THEME-DEPLOY.md` (Favicon row in §1.3 table, currently ~line 61)

**Interfaces:**
- Consumes: Task 1 path `wp-content/themes/rozgadana-jana/assets/images/site-icon.png`
- Produces: deploy checklist that requires circular Site Icon from that file

- [ ] **Step 1: Replace the Favicon table row**

In `docs/THEME-DEPLOY.md`, change the Favicon row from:

```markdown
| **Favicon** | Wygląd → Dostosuj → Tożsamość witryny → ikona z okrągłego logo (opcjonalnie) |
```

to:

```markdown
| **Favicon / Site Icon** | Wygląd → Dostosuj → Tożsamość witryny → **Ikona witryny**: wgraj `wp-content/themes/rozgadana-jana/assets/images/site-icon.png` (okrągłe PNG z przezroczystym tłem). Na serwerze wgraj też rootowy `favicon.ico` z repo. |
```

- [ ] **Step 2: Confirm no leftover “optional JPEG favicon” wording for this step**

```bash
rg -n "Favicon|Site Icon|favicon|site-icon" docs/THEME-DEPLOY.md
```

Expected: the new row references `site-icon.png` and root `favicon.ico`; no “opcjonalnie” for this step.

- [ ] **Step 3: Commit**

```bash
git add docs/THEME-DEPLOY.md
git commit -m "$(cat <<'EOF'
docs: require circular Site Icon in theme deploy checklist

EOF
)"
```

---

## Self-review (author)

1. **Spec coverage:** Visual mask + padding → Task 1; assets ICO + site-icon.png → Task 1; WP Site Icon → Task 2; THEME-DEPLOY → Task 3; verification → Tasks 1–2. Out of scope (SVG, simplified mark, theme hooks) not scheduled.
2. **Placeholders:** None; script and commands are complete.
3. **Consistency:** Paths and sizes match the spec; generation source clarified as high-res `docs/jana_logo_official-scaled.jpg` to avoid upscaling the 192×192 theme JPEG.
