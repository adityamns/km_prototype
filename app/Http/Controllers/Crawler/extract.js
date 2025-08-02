import os from "os";
process.env.TEMP = process.env.TEMP || os.tmpdir();
process.env.TMPDIR = process.env.TMPDIR || os.tmpdir();
process.env.TMP = process.env.TMP || os.tmpdir();

import puppeteer from "puppeteer-extra";
import StealthPlugin from "puppeteer-extra-plugin-stealth";

// Aktifkan stealth plugin
puppeteer.use(StealthPlugin());

const rawUrl = process.argv[2] || "";
const url = rawUrl.trim().replace(/\r?\n|\r/g, ""); // bersihkan newline

if (!url.startsWith("http")) {
    console.error("❌ Invalid or missing URL.");
    process.exit(1);
}

try {
    const browser = await puppeteer.launch({
        headless: true,
        args: [
            "--no-sandbox",
            "--disable-setuid-sandbox",
            "--disable-blink-features=AutomationControlled",
            "--disable-features=site-per-process",
        ],
        timeout: 0,
    });

    const page = await browser.newPage();

    // Set User-Agent agar terlihat seperti browser biasa
    await page.setUserAgent(
        "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36"
    );

    // Set viewport
    await page.setViewport({ width: 1366, height: 768 });

    // Tambah sedikit delay acak agar terlihat natural
    const randomDelay = () =>
        new Promise((resolve) =>
            setTimeout(resolve, 500 + Math.random() * 1000)
        );

    await page.goto(url, { waitUntil: "domcontentloaded", timeout: 120000 });

    // Simulasi scroll ke bawah agar Cloudflare mendeteksi ada interaksi
    for (let i = 0; i < 3; i++) {
        await page.evaluate(() => window.scrollBy(0, window.innerHeight));
        await randomDelay();
    }

    // Tunggu semua network idle setelah interaksi
    await page.waitForNetworkIdle({ idleTime: 1000, timeout: 120000 });

    // Ambil teks bersih
    const content = await page.evaluate(() => document.body.innerText);

    console.log(content);

    await browser.close();
    process.exit(0);
} catch (error) {
    console.error("🚨 Error extracting content:", error);
    process.exit(1);
}
