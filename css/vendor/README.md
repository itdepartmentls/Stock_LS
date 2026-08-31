# css/vendor/ - self-hosted icon sets

These were downloaded so the app renders icons with **no internet access**
(the pages used to pull them from jsdelivr / cloudflare / flaticon CDNs).
Nothing here is edited except local font paths.

| folder            | set                       | version | source |
|-------------------|---------------------------|---------|--------|
| `fontawesome/`    | Font Awesome **Free**     | 6.7.2   | cdnjs  |
| `bootstrap-icons/`| Bootstrap Icons           | 1.10.5  | jsdelivr |
| `uicons/`         | Flaticon UICONS (3 fams)  | latest  | cdn-uicons.flaticon.com |

## Font Awesome Pro icons

The pages use Pro-only styles (`fa-duotone`, `fa-light`, `fa-thin`) ~280 times.
This is the **Free** build, so `fontawesome/all.min.css` ends with a shim that
renders those with the Free *solid* face - they show, but lose the duotone look.

To restore real Pro rendering: drop your licensed FA Pro webfonts into
`fontawesome/webfonts/`, replace `fontawesome/all.min.css` with your Pro
`all.min.css` (keep the `url(webfonts/...)` paths), and delete the shim block.
