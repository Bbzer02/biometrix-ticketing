To make login-illustration.png transparent (remove light blue background):

Option 1 - Python (requires Pillow):
  pip install Pillow
  python make_transparent.py public/image/login-illustration.png public/image/login-illustration.png

Option 2 - PHP (requires ext-gd):
  php make_transparent.php

Option 3 - Use an online tool: upload the image and remove the background, then save as PNG and replace public/image/login-illustration.png.
