// extract.js

import os from 'os';

process.env.TEMP = process.env.TEMP || os.tmpdir();
process.env.TMPDIR = process.env.TMPDIR || os.tmpdir();
process.env.TMP = process.env.TMP || os.tmpdir();


import puppeteer from "puppeteer";

const rawUrl = process.argv[2] || '';
const url = rawUrl.trim().replace(/\r?\n|\r/g, ''); // bersihkan newline

if (!url.startsWith('http')) {
    console.error("❌ Invalid or missing URL.");
    process.exit(1);
}

try {
     const browser = await puppeteer.launch({
        executablePath: puppeteer.executablePath(), // gunakan Chromium bawaan Puppeteer
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox'],
        timeout: 0
    });

    const page = await browser.newPage();
    await page.goto(url, { waitUntil: "networkidle2", timeout: 60000 });

    // Ambil hanya teks bersih (tanpa HTML)
    const content = await page.evaluate(() => document.body.innerText);

    console.log(content);

    await browser.close();
    process.exit(0);
} catch (error) {
    console.error("🚨 Error extracting content:", error);
    process.exit(1);
}
