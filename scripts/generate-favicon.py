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
    img = img.resize((size, size), Image.LANCZOS)

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
    # Primary frame must be the largest; Pillow embeds the sizes= list from it.
    primary = ico_frames[-1]
    primary.save(
        OUT_ICO,
        format="ICO",
        sizes=ICO_SIZES,
        append_images=ico_frames[:-1],
    )
    print(f"Wrote {OUT_PNG} ({SITE_ICON_SIZE}x{SITE_ICON_SIZE})")
    print(f"Wrote {OUT_ICO} sizes={ICO_SIZES}")


if __name__ == "__main__":
    main()
