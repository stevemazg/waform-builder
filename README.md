# WaForm Builder

A self-contained PHP WhatsApp Form Builder — no database, no framework, no dependencies.  
Inspired by [form.wa.link](https://form.wa.link): each question shown one at a time on mobile, and on completion the answers are compiled into a WhatsApp message and sent via `wa.me`.

---

## Features

- **Admin dashboard** — create, edit, copy, and delete forms
- **Drag-friendly question builder** — add unlimited questions, reorder with ↑/↓
- **6 question types** — Short Text, Amount (£), Number, Long Text, Yes/No, Multiple Choice
- **Public form** — mobile-first, WhatsApp-green design, one question at a time
- **Welcome screen** — optional intro text before the first question
- **Progress bar** — fixed at the bottom, fills as the user progresses
- **Completion screen** — summary of all answers + big "Send via WhatsApp" button
- **WhatsApp message** — formatted with the form name and all answers, opens `wa.me`
- **JSON storage** — each form is a single `.json` file in `data/`, no database required
- **Password-protected admin** — single shared password, PHP sessions

---

## Requirements

- PHP 7.4 or higher
- Write permission on the `data/` directory
- No Composer, no npm, no database

---

## Installation

1. Upload the entire `waform/` folder to your web server (e.g. via FTP or cPanel file manager)
2. Make sure the `data/` directory is writable by the web server:
   ```bash
   chmod 755 waform/data
   ```
3. Open `config.php` and change the admin password:
   ```php
   define('ADMIN_PASSWORD', 'YourStrongPassword123!');
   ```
4. Navigate to `https://yoursite.com/waform/` — you'll be redirected to the login page

---

## Default admin password

```
CPDS2026!
```

**Change this before going live.**

---

## Usage

### Admin
- **Login:** `/waform/login.php`
- **Dashboard:** `/waform/index.php`
- **Create form:** `/waform/edit.php`
- **Edit form:** `/waform/edit.php?slug=your-form-slug`

### Public form
```
https://yoursite.com/waform/form.php?f=your-form-slug
```

Share this URL with clients. No login required.

---

## Form JSON structure

Forms are stored in `data/{slug}.json`:

```json
{
  "name": "Essential Living Expenditure",
  "slug": "essential-living",
  "whatsapp": "447700000000",
  "logo_url": "https://example.com/logo.png",
  "privacy_url": "https://example.com/privacy",
  "terms_url": "https://example.com/terms",
  "intro": "Please fill in this form as accurately as possible.",
  "created": "2026-08-09",
  "modified": "2026-08-09",
  "questions": [
    {
      "id": "q1",
      "title": "Rent or Mortgage",
      "hint": "How much do you pay each month?",
      "type": "amount",
      "required": true,
      "placeholder": "0.00"
    }
  ]
}
```

**Question types:** `text`, `amount`, `number`, `textarea`, `yesno`, `choice`

---

## WhatsApp message format

When a user completes the form, the following message is opened in WhatsApp:

```
*Form Name*

Question 1 Title: Answer
Question 2 Title: £125.00
Question 3 Title: Yes
```

---

## File structure

```
waform/
├── index.php           Admin dashboard
├── login.php           Admin login
├── logout.php          Clear session
├── edit.php            Create/edit a form
├── save.php            POST handler for save
├── copy.php            Duplicate a form
├── delete.php          Delete a form
├── form.php            Public form (?f=slug)
├── config.php          App config & password
├── question_template.php  PHP helper for edit.php
├── data/               JSON form files (auto-created)
│   └── .gitkeep
├── uploads/            Reserved for future logo uploads
│   └── .gitkeep
├── assets/
│   ├── style.css       Admin styles
│   └── form.css        Public form styles
└── README.md
```

---

## Security notes

- Change `ADMIN_PASSWORD` in `config.php` before deploying
- Add a `.htaccess` rule to deny direct access to `data/*.json` if on Apache:
  ```apache
  <FilesMatch "\.json$">
      Deny from all
  </FilesMatch>
  ```
- The `data/` directory should not be publicly browsable; add `Options -Indexes` in `.htaccess`

---

## Licence

MIT — free to use and modify.
