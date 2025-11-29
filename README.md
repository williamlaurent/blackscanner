# Website Backlink Scanner (Judol / Slot Keyword Detection)

This is a lightweight PHP-based tool that scans a list of URLs to detect gambling-related keywords (such as "slot", "gacor", "mpo", and "judi") and hidden backlinks that are often injected by hackers.  
The scanner uses a simple heuristic scoring system to classify each site as **Safe**, **Suspicious**, or **Infected**.

This project is intended to run locally using PHP’s built-in development server.

---

## Features

- **Keyword Detection**  
  Detects gambling/SEO spam keywords inside the page HTML.

- **Hidden Backlink Detection**  
  Finds suspicious CSS patterns like `display:none`, `opacity:0`, `visibility:hidden`, and `left:-9999px`.

- **Outbound Link Counting**  
  Counts all outbound `<a href>` links to detect abnormal SEO spam.

- **Heuristic Risk Scoring**  
  A 3-factor scoring model determines the site’s final status.

- **Simple Web Interface**  
  Paste a list of URLs and scan them instantly.

---

## Risk Scoring Algorithm

Each URL gets a score based on three detection factors:

| Detection Type | Score |
|----------------|-------|
| Gambling keyword found | +50 |
| Hidden backlink found | +30 |
| Outbound links > 20 | +10 |

### Final Classification

| Score Range | Status |
|-------------|---------|
| 50 or more | Infected |
| 30–49 | Suspicious |
| Below 30 | Safe |

---

## Requirements

- PHP 7.4 or newer  
- Internet access  
- A `keywords.txt` file in the same directory  
  - One keyword per line  

Example `keywords.txt`:

```
slot
slot gacor
gacor
mpo
judi
```

---

## Running the Scanner Locally

Use PHP’s built-in development server:

```bash
php -S localhost:4444
```

Then open this link in your browser:

```
http://localhost:4444
```

Enter URLs and start scanning.

---

## Project Structure

```
/project-folder
 ├── index.php        # main scanner script
 ├── keywords.txt     # list of keywords for detection
 └── README.md
```

---

## How It Works

1. Load list of URLs from the form.
2. Fetch each site’s HTML using `file_get_contents()`.
3. Normalize the content to lowercase.
4. Perform three checks:
   - keyword detection  
   - hidden backlink detection  
   - outbound link counting  
5. Assign a heuristic score.
6. Display results using color-coded rows:
   - red for infected  
   - orange for suspicious  
   - green for safe  

---

## Example Input List

```
https://example.com/
https://sampledomain.org/
https://mysite.co.id/
```

---

## Notes

- Some sites may block access to HTML fetching.
- The scanner does not execute JavaScript; it only reads static HTML.
- Suspicious cases (hidden backlinks only) should be checked manually.
- This tool only scans the main page of each URL; it does not crawl subdirectories.

---

## License

You may use, modify, or integrate this tool freely for personal or internal use.
