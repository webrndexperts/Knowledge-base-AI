# PDF Processing Setup Guide

This guide will help you set up the required dependencies for PDF processing in this application.

## Overview

The application uses the following tools to process PDF files:

1. **Ghostscript** - Required by ImageMagick to convert PDF pages to images
2. **Poppler (pdftotext)** - Extracts native text from PDFs
3. **Tesseract OCR** - Performs optical character recognition on images
4. **ImageMagick (PHP extension)** - Handles image manipulation and PDF processing

## Common Error: Ghostscript Not Found

If you see an error like:
```
FailedToExecuteCommand `"gswin64c.exe" ...` (127)
```

This means Ghostscript is not installed or not configured properly.

## Installation Instructions

### Windows

#### 1. Install Ghostscript

1. Download Ghostscript from: https://ghostscript.com/releases/gsdnld.html
2. Choose the appropriate version:
   - For 64-bit Windows: `Ghostscript 10.x.x for Windows (64 bit)`
   - For 32-bit Windows: `Ghostscript 10.x.x for Windows (32 bit)`
3. Run the installer (default installation path is recommended)
4. After installation, find the Ghostscript executable:
   - Default 64-bit path: `C:\Program Files\gs\gs10.04.0\bin\gswin64c.exe`
   - Default 32-bit path: `C:\Program Files (x86)\gs\gs10.03.1\bin\gswin32c.exe`

5. Add to your `.env` file:
   ```env
   GHOSTSCRIPT_PATH="C:/Program Files/gs/gs10.04.0/bin/gswin64c.exe"
   ```
   (Note: Use forward slashes `/` or escaped backslashes `\\`)

#### 2. Install Poppler (pdftotext)

1. Download Poppler for Windows from: https://github.com/oschwartz10612/poppler-windows/releases
2. Download the latest release (e.g., `Release-24.08.0-0.zip`)
3. Extract to a location like `C:\tools\poppler`
4. Add to your `.env` file:
   ```env
   PDFTOTEXT_PATH="C:/tools/poppler/Library/bin/pdftotext.exe"
   ```

#### 3. Install Tesseract OCR

1. Download from: https://github.com/UB-Mannheim/tesseract/wiki
2. Run the installer
3. Add Tesseract to your system PATH, or note the installation directory
4. Default path: `C:\Program Files\Tesseract-OCR\tesseract.exe`

#### 4. Verify ImageMagick PHP Extension

1. Check if ImageMagick extension is installed:
   ```bash
   php -m | findstr imagick
   ```

2. If not installed, you need to:
   - Download the appropriate ImageMagick DLL for your PHP version from: https://windows.php.net/downloads/pecl/releases/imagick/
   - Copy `php_imagick.dll` to your PHP extensions directory
   - Add `extension=imagick` to your `php.ini`
   - Restart your web server

### Linux (Ubuntu/Debian)

```bash
# Install Ghostscript
sudo apt-get update
sudo apt-get install ghostscript

# Install Poppler
sudo apt-get install poppler-utils

# Install Tesseract
sudo apt-get install tesseract-ocr

# Install ImageMagick and PHP extension
sudo apt-get install imagemagick
sudo apt-get install php-imagick

# Restart PHP-FPM (adjust version as needed)
sudo systemctl restart php8.2-fpm
```

Your `.env` file can use the default values:
```env
PDFTOTEXT_PATH=pdftotext
# GHOSTSCRIPT_PATH is not needed on Linux (automatically detected)
```

### macOS

```bash
# Install Homebrew if not already installed
/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"

# Install dependencies
brew install ghostscript
brew install poppler
brew install tesseract
brew install imagemagick

# Install PHP ImageMagick extension
pecl install imagick
```

Add to your `php.ini`:
```ini
extension=imagick.so
```

Your `.env` file can use the default values:
```env
PDFTOTEXT_PATH=pdftotext
# GHOSTSCRIPT_PATH is not needed on macOS (automatically detected)
```

## Verification

After installation, verify everything is working:

1. **Check Ghostscript:**
   ```bash
   # Windows
   "C:\Program Files\gs\gs10.04.0\bin\gswin64c.exe" --version
   
   # Linux/macOS
   gs --version
   ```

2. **Check Poppler:**
   ```bash
   pdftotext -v
   ```

3. **Check Tesseract:**
   ```bash
   tesseract --version
   ```

4. **Check ImageMagick PHP extension:**
   ```bash
   php -m | grep imagick
   ```

## Troubleshooting

### Error: "Ghostscript is not available"

**Solution:** Install Ghostscript and configure `GHOSTSCRIPT_PATH` in your `.env` file.

### Error: "FailedToExecuteCommand gswin64c.exe (127)"

**Cause:** Ghostscript executable not found.

**Solutions:**
1. Verify Ghostscript is installed
2. Check the path in your `.env` file is correct
3. Make sure you're using forward slashes in the path
4. Restart your web server after changing `.env`

### Error: "Unable to read the file"

**Cause:** File permissions or path issues.

**Solutions:**
1. Check that the uploaded file exists in the storage directory
2. Verify file permissions (should be readable by the web server user)
3. Check `storage/logs/laravel.log` for detailed error messages

### PDF Processing Falls Back to Text-Only

If you see a warning like "Ghostscript is not available. PDF processing with ImageMagick requires Ghostscript", the application will:
- Extract text using pdftotext (if available)
- Skip OCR and image extraction
- Continue processing without errors

To enable full PDF processing with OCR, install Ghostscript as described above.

## Configuration Summary

Your `.env` file should include:

```env
# Windows example
PDFTOTEXT_PATH="C:/tools/poppler/Library/bin/pdftotext.exe"
GHOSTSCRIPT_PATH="C:/Program Files/gs/gs10.04.0/bin/gswin64c.exe"

# Linux/macOS example
PDFTOTEXT_PATH=pdftotext
# GHOSTSCRIPT_PATH not needed (auto-detected)
```

## Need Help?

If you continue to experience issues:

1. Check the Laravel logs: `storage/logs/laravel.log`
2. Verify all paths in your `.env` file
3. Ensure all services are restarted after configuration changes
4. Check that file permissions are correct

For more information, see the main [README.md](README.md) file.
