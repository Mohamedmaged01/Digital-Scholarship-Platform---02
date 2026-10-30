"""Builds public/images/hero-kasp-banner.jpg — the home-page hero image.

Program logo on top, the Human Capability Development Program and Vision 2030
logos below (as on the back cover of the 2026-2027 guideline), on a 4:3 white
canvas that fills the hero frame. Run: python resources/brand/hero-banner.py
"""
from pathlib import Path

from PIL import Image, ImageDraw

ROOT = Path(__file__).resolve().parents[2]
W, H = 1600, 1200  # same 4:3 ratio as the hero frame

canvas = Image.new('RGB', (W, H), (255, 255, 255))

kasp = Image.open(ROOT / 'resources/brand/kasp-logo-source.png').convert('RGBA')
kasp = kasp.resize((1080, round(kasp.height * 1080 / kasp.width)), Image.LANCZOS)
canvas.paste(kasp, ((W - kasp.width) // 2, 230), kasp)

# فاصل رفيع بين الشعارين
draw = ImageDraw.Draw(canvas)
y = 230 + kasp.height + 70
draw.line([(W // 2 - 260, y), (W // 2 + 260, y)], fill=(226, 230, 228), width=3)

strip = Image.open(ROOT / 'resources/brand/hcdp-vision-source.png').convert('RGB')
strip = strip.resize((760, round(strip.height * 760 / strip.width)), Image.LANCZOS)
canvas.paste(strip, ((W - strip.width) // 2, y + 60))

out = ROOT / 'public/images/hero-kasp-banner.jpg'
canvas.save(out, quality=90, optimize=True, progressive=True)
print(out, canvas.size)
