"""
Remove solid light blue background from an image and save as PNG with transparency.
Usage: python make_transparent.py <input.png> <output.png>
"""
import sys
from pathlib import Path

try:
    from PIL import Image
except ImportError:
    print("Installing Pillow...")
    import subprocess
    subprocess.check_call([sys.executable, "-m", "pip", "install", "Pillow", "-q"])
    from PIL import Image


def is_light_blue(r, g, b, tolerance=45):
    """Light blue: high B, high G, moderate R (e.g. #ADD8E6, #B0E0E6, #87CEEB)."""
    # Typical light blue: R 160-220, G 200-240, B 230-255
    return (
        140 <= r <= 235 and
        190 <= g <= 255 and
        220 <= b <= 255 and
        b >= g and g >= r - 30  # blue-ish
    )


def make_bg_transparent(input_path: str, output_path: str):
    img = Image.open(input_path).convert("RGBA")
    data = img.getdata()
    new_data = []
    for item in data:
        r, g, b, a = item
        if is_light_blue(r, g, b):
            new_data.append((255, 255, 255, 0))
        else:
            new_data.append(item)
    img.putdata(new_data)
    img.save(output_path, "PNG")
    print(f"Saved transparent image to {output_path}")


if __name__ == "__main__":
    if len(sys.argv) < 3:
        print("Usage: python make_transparent.py <input.png> <output.png>")
        sys.exit(1)
    make_bg_transparent(sys.argv[1], sys.argv[2])
